<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['department_id'])) {
    header('Location: department/dashboard.php');
    exit();
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = $_POST['login_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($login_id) || empty($password)) {
        $error_message = "Email/Mobile and password are required.";
    } else {
        try {
            // Check if login is with email or mobile
            $is_email = filter_var($login_id, FILTER_VALIDATE_EMAIL);
            
            if ($is_email) {
                // Login with email
                $stmt = $conn->prepare("SELECT * FROM departments WHERE hod_email = ?");
            } else {
                // Login with mobile
                $stmt = $conn->prepare("SELECT * FROM departments WHERE hod_mobile = ?");
            }
            
            $stmt->bind_param("s", $login_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error_message = "Invalid credentials. Please try again.";
            } else {
                $department = $result->fetch_assoc();
                
                // Check if department is approved
                if ($department['approved'] != 1) {
                    $error_message = "Your department account is pending approval by the administrator.";
                } else if (password_verify($password, $department['password'])) {
                    // Set session variables
                    $_SESSION['department_id'] = $department['department_id'];
                    $_SESSION['department_name'] = $department['department'];
                    $_SESSION['hod_name'] = $department['hod_name'];
                    
                    // Redirect to department dashboard
                    header('Location: department/dashboard.php');
                    exit();
                } else {
                    $error_message = "Invalid credentials. Please try again.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
            error_log("Error during department login: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Login - Voting System</title>
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
            max-width: 400px;
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

        .login-form {
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
            padding: 12px 15px;
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

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 15px;
            }

            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Department Login</h1>
            <p>Access your department dashboard</p>
        </div>

        <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="login-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="login_id">Email or Mobile Number</label>
                    <input type="text" id="login_id" name="login_id" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>

        <div class="register-link">
            <p>Don't have an account? <a href="department_register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 