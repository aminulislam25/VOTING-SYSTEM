<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$success_message = '';
$error_message = '';
$candidate = null;

// Get candidate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

// Get candidate data
try {
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
    
    if (!$candidate) {
        header('Location: manage_candidates.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching candidate: " . $e->getMessage());
    header('Location: manage_candidates.php');
    exit();
}

// Get all positions for the dropdown
$positions = [];
try {
    $result = $conn->query("SELECT * FROM positions ORDER BY position_name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching positions: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position_id = $_POST['position_id'] ?? '';
    $department = $_POST['department'] ?? '';
    $last_sem_percentage = $_POST['last_sem_percentage'] ?? '';
    $manifesto = $_POST['manifesto'] ?? '';
    
    // Handle photo upload
    $photo = $candidate['photo']; // Keep existing photo by default
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/candidates/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Delete old photo
            $old_photo_path = $upload_dir . $candidate['photo'];
            if (file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
            
            $photo = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $photo;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                // Check if GD library is available
                if (extension_loaded('gd')) {
                    // Compress the image using GD
                    $image = imagecreatefromstring(file_get_contents($target_path));
                    if ($image) {
                        imagejpeg($image, $target_path, 80); // 80% quality
                        imagedestroy($image);
                    }
                } else {
                    // If GD is not available, just use the uploaded file as is
                    error_log("GD library not available. Image compression skipped.");
                }
            } else {
                $error_message = "Error uploading photo. Please check directory permissions.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (empty($error_message)) {
        try {
            $stmt = $conn->prepare("UPDATE candidates SET name = ?, position_id = ?, department = ?, photo = ?, manifesto = ?, last_sem_percentage = ? WHERE candidate_id = ?");
            $stmt->bind_param("sisssdi", $name, $position_id, $department, $photo, $manifesto, $last_sem_percentage, $candidate_id);
            
            if ($stmt->execute()) {
                $success_message = "Candidate updated successfully!";
                // Refresh candidate data
                $stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = ?");
                $stmt->bind_param("i", $candidate_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $candidate = $result->fetch_assoc();
            } else {
                $error_message = "Error updating candidate. Please try again.";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error updating candidate: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --text-primary: #2b2d42;
            --text-secondary: #8d99ae;
            --bg-light: #f8f9fa;
            --border-radius: 12px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: var(--text-primary);
            font-size: 1.8rem;
        }

        .back-btn {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--text-secondary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: #7b8794;
        }

        .back-btn i {
            margin-right: 8px;
        }

        /* Form Styles */
        .edit-candidate-form {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .current-photo {
            margin-top: 10px;
            width: 100px;
            height: 100px;
            border-radius: var(--border-radius);
            object-fit: cover;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
        }

        .submit-btn:hover {
            background: var(--secondary-color);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }

        .photo-upload-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            gap: 8px;
            font-weight: 500;
        }

        .upload-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .photo-preview {
            width: 200px;
            height: 200px;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 2px solid #e9ecef;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .default-preview {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #adb5bd;
        }

        .default-preview i {
            font-size: 4rem;
            margin-bottom: 10px;
        }

        .default-preview span {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>PRECIDING OFFICER<br>(Karimganj college)</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_voters.php" class="nav-link">
                        <i class="fas fa-users"></i> Manage Voters
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_departments.php" class="nav-link">
                        <i class="fas fa-building"></i> Manage Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_candidates.php" class="nav-link active">
                        <i class="fas fa-user-tie"></i> Manage Candidates
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_results.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> View Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Edit Candidate</h1>
                <a href="manage_candidates.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Candidates
                </a>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="edit-candidate-form">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Candidate Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($candidate['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="position_id">Position</label>
                            <select id="position_id" name="position_id" required>
                                <option value="">Select Position</option>
                                <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position['position_id']; ?>" <?php echo ($candidate['position_id'] == $position['position_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($candidate['department']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="last_sem_percentage">Last Semester Percentage</label>
                            <input type="number" id="last_sem_percentage" name="last_sem_percentage" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($candidate['last_sem_percentage']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <div class="photo-upload-container">
                            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png" style="display: none;">
                            <button type="button" class="upload-btn" id="uploadBtn">
                                <i class="fas fa-upload"></i> Change Photo
                            </button>
                            <div class="photo-preview" id="photoPreview">
                                <?php
                                $photo_path = '../uploads/candidates/' . (isset($candidate['photo']) && !empty($candidate['photo']) ? htmlspecialchars($candidate['photo']) : '');
                                if (!empty($candidate['photo']) && file_exists($photo_path)) {
                                    echo '<img src="' . $photo_path . '" alt="' . (isset($candidate['name']) ? htmlspecialchars($candidate['name']) : 'Candidate') . '">';
                                } else {
                                    echo '<div class="default-preview"><i class="fas fa-user-circle"></i><span>No Photo</span></div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="manifesto">Manifesto</label>
                        <textarea id="manifesto" name="manifesto" required><?php echo htmlspecialchars($candidate['manifesto']); ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Update Candidate</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cropping Modal -->
    <div class="cropping-modal" id="croppingModal">
        <div class="cropping-content">
            <button class="close-modal" onclick="closeCroppingModal()">&times;</button>
            <div class="cropper-container">
                <img id="cropperImage" src="" alt="Image to crop">
            </div>
            <div class="cropping-controls">
                <button class="cropping-btn cancel-crop" onclick="closeCroppingModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="cropping-btn apply-crop" onclick="applyCrop()">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
        </div>
    </div>

    <script>
    let cropper = null;
    const modal = document.getElementById('croppingModal');
    const cropperImage = document.getElementById('cropperImage');
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const uploadBtn = document.getElementById('uploadBtn');

    uploadBtn.addEventListener('click', function() {
        photoInput.click();
    });

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, or GIF)');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                cropperImage.src = imageUrl;
                openCroppingModal();
            }
            reader.readAsDataURL(file);
        }
    });

    function openCroppingModal() {
        modal.style.display = 'block';
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: true
        });
    }

    function closeCroppingModal() {
        modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function applyCrop() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas();
            const croppedImage = canvas.toDataURL('image/jpeg', 0.8);
            photoPreview.innerHTML = `<img src="${croppedImage}" alt="Cropped Preview">`;
            
            // Convert base64 to blob for upload
            fetch(croppedImage)
                .then(res => res.blob())
                .then(blob => {
                    const file = new File([blob], "cropped_image.jpg", { type: "image/jpeg" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    photoInput.files = dataTransfer.files;
                });
            
            closeCroppingModal();
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            closeCroppingModal();
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (modal.style.display === 'block') {
            if (e.key === 'Escape') {
                closeCroppingModal();
            }
            if (e.key === 'Enter') {
                applyCrop();
            }
        }
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
