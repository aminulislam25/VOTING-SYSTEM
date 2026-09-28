<?php
session_start();
require_once '../config.php';

// Check if department is logged in
if (!isset($_SESSION['department_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get department details
$department_id = $_SESSION['department_id'];
$stmt = $conn->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$department = $stmt->get_result()->fetch_assoc();

// Check if candidate ID is provided
if (!isset($_GET['id'])) {
    header('Location: manage_candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

// Verify the candidate belongs to this department
$stmt = $conn->prepare("SELECT c.*, p.position_name 
                       FROM candidates c 
                       LEFT JOIN positions p ON c.position_id = p.position_id 
                       WHERE c.candidate_id = ? AND c.department = ?");
$stmt->bind_param("is", $candidate_id, $department['department']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Candidate not found or you don't have permission to edit this candidate.";
    header('Location: manage_candidates.php');
    exit();
}

$candidate = $result->fetch_assoc();

// Get all positions for the edit form
$result = $conn->query("SELECT * FROM positions ORDER BY position_name");
$positions = [];
while ($row = $result->fetch_assoc()) {
    $positions[] = $row;
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position_id = $_POST['position_id'] ?? '';
    $bio = $_POST['bio'] ?? '';
    
    // Validate input
    if (empty($name) || empty($position_id)) {
        $error_message = "Name and position are required.";
    } else {
        // Handle file upload
        $photo = $candidate['photo']; // Keep existing photo by default
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_dir = '../uploads/candidates/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    // Delete old photo if exists
                    if (!empty($candidate['photo']) && file_exists($candidate['photo'])) {
                        unlink($candidate['photo']);
                    }
                    $photo = $upload_path;
                } else {
                    $error_message = "Error uploading file.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
            }
        }
        
        if (empty($error_message)) {
            // Update candidate
            $stmt = $conn->prepare("UPDATE candidates SET name = ?, position_id = ?, photo = ?, bio = ? WHERE candidate_id = ?");
            $stmt->bind_param("sissi", $name, $position_id, $photo, $bio, $candidate_id);
            
            if ($stmt->execute()) {
                $success_message = "Candidate updated successfully.";
                
                // Refresh candidate data
                $stmt = $conn->prepare("SELECT c.*, p.position_name 
                                       FROM candidates c 
                                       LEFT JOIN positions p ON c.position_id = p.position_id 
                                       WHERE c.candidate_id = ?");
                $stmt->bind_param("i", $candidate_id);
                $stmt->execute();
                $candidate = $stmt->get_result()->fetch_assoc();
            } else {
                $error_message = "Error updating candidate: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate - Department Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --danger-color: #f44336;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--light-gray);
            color: var(--text-color);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: white;
            padding: 2rem;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .department-info {
            text-align: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .department-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
        }

        .department-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .department-details {
            font-size: 0.9rem;
            color: #666;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-link i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .back-btn {
            background: var(--secondary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: #1976d2;
        }

        .back-btn i {
            margin-right: 0.5rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        /* Edit Form */
        .edit-candidate-form {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .current-photo {
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .current-photo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
        }

        .current-photo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .cancel-btn {
            background: var(--light-gray);
            color: var(--text-color);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .cancel-btn:hover {
            background: #e0e0e0;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .submit-btn:hover {
            background: #3d8b40;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="department-info">
                <div class="department-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="department-name"><?php echo htmlspecialchars($department['department']); ?></div>
                <div class="department-details">
                    <?php echo htmlspecialchars($department['hod_mobile']); ?>
                </div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_voters.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        View Voters
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_results.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-edit"></i>
                        Update Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Edit Candidate</h1>
                <a href="manage_candidates.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Candidates
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

            <!-- Edit Candidate Form -->
            <div class="edit-candidate-form">
                <h2 class="form-title">Edit Candidate Details</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Candidate Name</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($candidate['name']); ?>">
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
                    <div class="form-group">
                        <label for="photo">Photo (Optional)</label>
                        <input type="file" id="photo" name="photo">
                        <div class="current-photo">
                            <?php if (!empty($candidate['photo']) && file_exists($candidate['photo'])): ?>
                            <img src="<?php echo $candidate['photo']; ?>" alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                            <span>Current photo</span>
                            <?php else: ?>
                            <div class="current-photo-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                            <span>No photo uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio (Optional)</label>
                        <textarea id="bio" name="bio"><?php echo htmlspecialchars($candidate['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="manage_candidates.php" class="cancel-btn">Cancel</a>
                        <button type="submit" class="submit-btn">Update Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 