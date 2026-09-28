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

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hod_name = $_POST['hod_name'] ?? '';
    $hod_email = $_POST['hod_email'] ?? '';
    $hod_mobile = $_POST['hod_mobile'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($hod_name) || empty($hod_email) || empty($hod_mobile)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        try {
            // Check if email already exists (excluding current department)
            $stmt = $conn->prepare("SELECT department_id FROM departments WHERE hod_email = ? AND department_id != ?");
            $stmt->bind_param("si", $hod_email, $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "This email is already registered with another department.";
            } else {
                // If changing password, verify current password
                if (!empty($new_password)) {
                    if (!password_verify($current_password, $department['password'])) {
                        $error_message = "Current password is incorrect.";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE departments SET hod_name = ?, hod_email = ?, hod_mobile = ?, password = ? WHERE department_id = ?");
                        $stmt->bind_param("ssssi", $hod_name, $hod_email, $hod_mobile, $hashed_password, $department_id);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE departments SET hod_name = ?, hod_email = ?, hod_mobile = ? WHERE department_id = ?");
                    $stmt->bind_param("sssi", $hod_name, $hod_email, $hod_mobile, $department_id);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Profile updated successfully!";
                    // Refresh department details
                    $stmt = $conn->prepare("SELECT * FROM departments WHERE department_id = ?");
                    $stmt->bind_param("i", $department_id);
                    $stmt->execute();
                    $department = $stmt->get_result()->fetch_assoc();
                } else {
                    $error_message = "Error updating profile. Please try again.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error updating department profile: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($department['department']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Include the same root and basic styles as dashboard.php */
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
            display: flex;
        }

        /* Include sidebar styles from dashboard.php */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: white;
            padding: 20px;
            position: fixed;
            box-shadow: var(--card-shadow);
        }

        .department-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .department-info h2 {
            color: var(--text-primary);
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .department-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-links i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .profile-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .profile-header {
            background: var(--primary-color);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .profile-header h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .profile-header p {
            opacity: 0.9;
        }

        .profile-body {
            padding: 30px;
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

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .password-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .password-section h3 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
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

        .logout-btn {
            margin-top: auto;
            width: 100%;
            padding: 12px;
            background: var(--warning-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="department-info">
            <h2><?php echo htmlspecialchars($department['department']); ?></h2>
            <p>HOD: <?php echo htmlspecialchars($department['hod_name']); ?></p>
        </div>
        
        <ul class="nav-links">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
            <li>
                <a href="view_students.php">
                    <i class="fas fa-user-graduate"></i> View Students
                </a>
            </li>
            <li>
                <a href="approve_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'approve_students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-check"></i> Approve Students
                    </a>
                </li>
            <li>
                <a href="profile.php" class="active">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
        </ul>

        <form action="../logout.php" method="POST" style="margin-top: auto;">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>

    <div class="main-content">
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

        <div class="profile-card">
            <div class="profile-header">
                <h2><?php echo htmlspecialchars($department['department']); ?></h2>
                <p>Update your department profile information</p>
            </div>

            <div class="profile-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="hod_name">HOD Name</label>
                        <input type="text" id="hod_name" name="hod_name" value="<?php echo htmlspecialchars($department['hod_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hod_email">HOD Email</label>
                        <input type="email" id="hod_email" name="hod_email" value="<?php echo htmlspecialchars($department['hod_email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hod_mobile">HOD Mobile</label>
                        <input type="tel" id="hod_mobile" name="hod_mobile" value="<?php echo htmlspecialchars($department['hod_mobile']); ?>" required>
                    </div>

                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 20px;">Leave password fields empty if you don't want to change it</p>

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 