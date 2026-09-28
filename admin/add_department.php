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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = $_POST['department'] ?? '';
    $hod_name = $_POST['hod_name'] ?? '';
    $hod_email = $_POST['hod_email'] ?? '';
    $hod_mobile = $_POST['hod_mobile'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $approved = isset($_POST['approved']) ? 1 : 0;
    
    // Validate input
    if (empty($department) || empty($hod_name) || empty($hod_email) || empty($hod_mobile) || empty($_POST['password'])) {
        $error_message = "All fields are required.";
    } else {
        try {
            // Check if department already exists
            $stmt = $conn->prepare("SELECT department_id FROM departments WHERE department = ?");
            $stmt->bind_param("s", $department);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "A department with this name already exists.";
            } else {
                // Insert new department
                $stmt = $conn->prepare("INSERT INTO departments (department, hod_name, hod_email, hod_mobile, password, approved) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $department, $hod_name, $hod_email, $hod_mobile, $password, $approved);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Department added successfully!";
                    header('Location: manage_departments.php');
                    exit();
                } else {
                    $error_message = "Error adding department. Please try again.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error adding department: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .add-department-form {
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

        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group input {
            margin-right: 10px;
        }

        .checkbox-group label {
            color: var(--text-primary);
            font-weight: 500;
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
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Welcome Admin</p>
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
                    <a href="manage_departments.php" class="nav-link active">
                        <i class="fas fa-building"></i> Manage Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_candidates.php" class="nav-link">
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
                <h1 class="page-title">Add New Department</h1>
                <a href="manage_departments.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Departments
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

            <div class="add-department-form">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="department">Department Name</label>
                        <input type="text" id="department" name="department" required>
                    </div>

                    <div class="form-group">
                        <label for="hod_name">HOD Name</label>
                        <input type="text" id="hod_name" name="hod_name" required>
                    </div>

                    <div class="form-group">
                        <label for="hod_email">HOD Email</label>
                        <input type="email" id="hod_email" name="hod_email" required>
                    </div>

                    <div class="form-group">
                        <label for="hod_mobile">HOD Mobile</label>
                        <input type="tel" id="hod_mobile" name="hod_mobile" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="approved" name="approved">
                        <label for="approved">Approve Department Immediately</label>
                    </div>

                    <button type="submit" class="submit-btn">Add Department</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 