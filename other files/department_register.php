<?php
session_start();
require_once 'config.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department = $_POST['department'] ?? '';
    $hod_name = $_POST['hod_name'] ?? '';
    $hod_email = $_POST['hod_email'] ?? '';
    $hod_mobile = $_POST['hod_mobile'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($department) || empty($hod_name) || empty($hod_email) || empty($hod_mobile) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
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
                // Check if email already exists
                $stmt = $conn->prepare("SELECT department_id FROM departments WHERE hod_email = ?");
                $stmt->bind_param("s", $hod_email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "This email is already registered.";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new department with approved = 0 (pending approval)
                    $stmt = $conn->prepare("INSERT INTO departments (department, hod_name, hod_email, hod_mobile, password, approved) VALUES (?, ?, ?, ?, ?, 0)");
                    $stmt->bind_param("sssss", $department, $hod_name, $hod_email, $hod_mobile, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success_message = "Department registered successfully! Your account is pending approval by the administrator.";
                    } else {
                        $error_message = "Error registering department. Please try again.";
                    }
                }
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error registering department: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Registration - Voting System</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--text-primary);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .registration-form {
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%234361ee' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 45px;
        }

        .form-group select {
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-group select option {
            padding: 12px;
            font-size: 1rem;
            background: white;
            color: var(--text-primary);
        }

        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-group select:hover {
            border-color: var(--primary-color);
        }

        /* Style for the first option (placeholder) */
        .form-group select option[value=""] {
            color: var(--text-secondary);
            font-style: italic;
        }

        /* Custom scrollbar for the dropdown */
        .form-group select::-webkit-scrollbar {
            width: 8px;
        }

        .form-group select::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .form-group select::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .form-group select::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Remove default input styles since we're using the same style for both */
        .form-group input {
            background-image: none;
            padding-right: 15px;
        }

        .submit-btn {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: var(--secondary-color);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
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

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 15px;
            }

            .registration-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Department Registration</h1>
            <p>Register your department for the voting system</p>
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

        <div class="registration-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="department">Department Name</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="English">English</option>
                        <option value="Bengali">Bengali</option>
                        <option value="Economics">Economics</option>
                        <option value="Political Science">Political Science</option>
                        <option value="History">History</option>
                        <option value="Philosophy">Philosophy</option>
                        <option value="Arabic">Arabic</option>
                        <option value="Sanskrit">Sanskrit</option>
                        <option value="Physics">Physics</option>
                        <option value="Chemistry">Chemistry</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Botany">Botany</option>
                        <option value="Zoology">Zoology</option>
                        <option value="Statistics">Statistics</option>
                        <option value="Biotechnology">Biotechnology</option>
                        <option value="Computer Science and Application">Computer Science and Application</option>
                        <option value="B.Com">B.Com</option>
                        <option value="HS 1ST YEAR">HS 1ST YEAR</option>
                        <option value="HS 2ND YEAR">HS 2ND YEAR</option>
                    </select>
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

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="submit-btn">Register Department</button>
            </form>
        </div>

        <div class="login-link">
            <p>Already registered? <a href="index.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 