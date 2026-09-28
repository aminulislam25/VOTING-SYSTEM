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

// Get all departments for the dropdown
$departments = [];
try {
    $result = $conn->query("SELECT * FROM departments WHERE approved = 1 ORDER BY department");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching departments: " . $e->getMessage());
}

// Get active election
$active_election = null;
try {
    $result = $conn->query("SELECT election_id FROM elections WHERE status = 'active' OR status = 'upcoming' ORDER BY created_at DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $active_election = $result->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Error fetching active election: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $position_id = $_POST['position_id'] ?? '';
    $department = $_POST['department'] ?? '';
    $last_sem_percentage = $_POST['last_sem_percentage'] ?? '';
    $manifesto = $_POST['manifesto'] ?? '';
    
    // Handle photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__DIR__) . '/uploads/candidates/';
        
        // Create directory if it doesn't exist with proper permissions
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $error_message = "Failed to create upload directory. Please check server permissions.";
            }
        }
        
        // Verify directory is writable
        if (!is_writable($upload_dir)) {
            $error_message = "Upload directory is not writable. Please check directory permissions.";
        }
        
        if (empty($error_message)) {
            $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                // Generate a unique filename
                $photo = uniqid('candidate_') . '.' . $file_extension;
                $target_path = $upload_dir . $photo;
                
                // Verify uploaded file is actually an image
                $check = getimagesize($_FILES['photo']['tmp_name']);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
                        // Set proper permissions for the uploaded file
                        chmod($target_path, 0644);
                        
                        // Check if GD library is available
                        if (extension_loaded('gd')) {
                            // Get image type and create image resource accordingly
                            $image_info = getimagesize($target_path);
                            if ($image_info !== false) {
                                switch ($image_info[2]) {
                                    case IMAGETYPE_JPEG:
                                        $image = imagecreatefromjpeg($target_path);
                                        break;
                                    case IMAGETYPE_PNG:
                                        $image = imagecreatefrompng($target_path);
                                        break;
                                    case IMAGETYPE_GIF:
                                        $image = imagecreatefromgif($target_path);
                                        break;
                                    default:
                                        $image = false;
                                }
                                
                                if ($image) {
                                    // Create a new file with proper permissions
                                    imagejpeg($image, $target_path, 80); // 80% quality
                                    chmod($target_path, 0644);
                                    imagedestroy($image);
                                }
                            }
                        } else {
                            error_log("GD library not available. Image compression skipped.");
                        }
                    } else {
                        $error_message = "Error moving uploaded file. Please check directory permissions.";
                    }
                } else {
                    $error_message = "Uploaded file is not a valid image.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        }
    }

    if (empty($error_message)) {
        try {
            if (!$active_election) {
                throw new Exception("No active or upcoming election found. Please create an election first.");
            }
            
            $stmt = $conn->prepare("INSERT INTO candidates (name, position_id, department, photo, manifesto, last_sem_percentage, election_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sisssdi", $name, $position_id, $department, $photo, $manifesto, $last_sem_percentage, $active_election['election_id']);
            
            if ($stmt->execute()) {
                $success_message = "Candidate added successfully!";
                // Clear form data
                $_POST = array();
            } else {
                $error_message = "Error adding candidate. Please try again.";
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error adding candidate: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Candidate - Admin Dashboard</title>
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
            --bg-light: #ffffff;
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
            color: var(--text-primary);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #e9ecef;
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
            border: none;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border: none;
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
            text-shadow: none;
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
            box-shadow: none;
        }

        .back-btn i {
            margin-right: 8px;
        }

        /* Form Styles */
        .add-candidate-form {
            background: white;
            border: 1px solid #e9ecef;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            animation: none;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-weight: 600;
            text-shadow: none;
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
            background: white;
            color: var(--text-primary);
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

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: flex-start;
        }

        .submit-btn, .reset-btn {
            padding: 12px 24px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
        }

        .submit-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .reset-btn {
            background: #f8f9fa;
            color: #495057;
            border: 2px solid #e9ecef;
        }

        .reset-btn:hover {
            background: #e9ecef;
            color: #212529;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .reset-btn i {
            font-size: 1.1rem;
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
            box-shadow: none;
        }

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
            box-shadow: none;
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
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #e9ecef;
            border-radius: var(--border-radius);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            overflow: hidden;
            position: relative;
            background: white;
        }

        .photo-preview:hover {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .photo-preview i {
            font-size: 2rem;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .photo-preview span {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-align: center;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius);
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
            font-size: 1rem;
            transition: var(--transition);
            gap: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .upload-btn:hover {
            background: var(--secondary-color);
            box-shadow: none;
        }

        .upload-btn i {
            font-size: 1.1rem;
        }

        /* Cropping Modal Styles */
        .cropping-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            padding: 20px;
        }

        .cropping-content {
            background: white;
            width: 90%;
            max-width: 800px;
            height: 80vh;
            margin: 40px auto;
            padding: 20px;
            border-radius: var(--border-radius);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .cropper-container {
            flex: 1;
            min-height: 400px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .cropping-controls {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            position: relative;
            z-index: 2;
        }

        .cropping-btn {
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            min-width: 100px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            position: relative;
            z-index: 3;
        }

        .apply-crop {
            background: var(--primary-color);
            color: white;
        }

        .cancel-crop {
            background: var(--text-secondary);
            color: white;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            z-index: 3;
        }

        .close-modal:hover {
            background: var(--primary-color);
            color: white;
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
                <h1 class="page-title">Add New Candidate</h1>
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

            <div class="add-candidate-form">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Candidate Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="position_id">Position</label>
                            <select id="position_id" name="position_id" required>
                                <option value="">Select Position</option>
                                <option value="1" <?php echo (isset($_POST['position_id']) && $_POST['position_id'] == '1') ? 'selected' : ''; ?>>Good</option>
                                <option value="2" <?php echo (isset($_POST['position_id']) && $_POST['position_id'] == '2') ? 'selected' : ''; ?>>Excellent</option>
                                <option value="3" <?php echo (isset($_POST['position_id']) && $_POST['position_id'] == '3') ? 'selected' : ''; ?>>Outstanding</option>
                                <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position['position_id']; ?>" <?php echo (isset($_POST['position_id']) && $_POST['position_id'] == $position['position_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['department']); ?>" <?php echo (isset($_POST['department']) && $_POST['department'] == $dept['department']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="last_sem_percentage">Last Semester Percentage</label>
                            <input type="number" id="last_sem_percentage" name="last_sem_percentage" min="0" max="100" step="0.01" value="<?php echo isset($_POST['last_sem_percentage']) ? htmlspecialchars($_POST['last_sem_percentage']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <div class="photo-upload-container">
                            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png" style="display: none;">
                            <button type="button" class="upload-btn" id="uploadBtn">
                                <i class="fas fa-upload"></i> Choose Photo
                            </button>
                            <div class="photo-preview" id="photoPreview">
                                <?php
                                $default_photo = '../assets/images/default-candidate.jpg';
                                if (isset($_POST['photo']) && !empty($_POST['photo'])) {
                                    $photo_path = '../uploads/candidates/' . htmlspecialchars($_POST['photo']);
                                    if (file_exists($photo_path)) {
                                        echo '<img src="' . $photo_path . '" alt="Preview">';
                                    } else {
                                        echo '<div class="default-preview"><i class="fas fa-user-circle"></i><span>No Photo</span></div>';
                                    }
                                } else {
                                    echo '<div class="default-preview"><i class="fas fa-user-circle"></i><span>No Photo</span></div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="manifesto">Manifesto</label>
                        <textarea id="manifesto" name="manifesto" required><?php echo isset($_POST['manifesto']) ? htmlspecialchars($_POST['manifesto']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-plus-circle"></i> Add Candidate
                        </button>
                        <button type="reset" class="reset-btn">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
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
