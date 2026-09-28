<?php
session_start();
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id']) || isset($_SESSION['department_id']) || isset($_SESSION['student_id'])) {
    if (isset($_SESSION['admin_id'])) {
        header("Location: admin/dashboard.php");
    } elseif (isset($_SESSION['department_id'])) {
        header("Location: department/dashboard.php");
    } elseif (isset($_SESSION['student_id'])) {
        header("Location: student/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Voting System</title>
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
    background: linear-gradient(135deg, #f6f8fd 0%, #f1f4f9 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
body {
    font-family: 'Poppins', sans-serif;
    background: 
        linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)),
        url('https://www.karimganjcollege.ac.in/img/college-front-full.jpg') no-repeat center center/cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

        

        .container {
            width: 100%;
            max-width: 500px;
        }

        .register-box {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 30px;
}
.register-box {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 30px;
}


        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            color: var(--text-primary);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .register-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .submit-btn:hover {
            background: var(--secondary-color);
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
        }

        .links-container {
            text-align: center;
            margin-top: 20px;
        }

        .links-container a {
            color: #2196F3;
            text-decoration: none;
        }

        .links-container a:hover {
            text-decoration: underline;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="register-box">
            <div class="register-header">
                <h2>STUDENT REGISTRATION</h2>
                <p>Create your account to access the voting system</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 3000);
                </script>
            <?php endif; ?>

            <form action="process_student_register.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="registration_no">Registration Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="registration_no" name="registration_no" required>
                </div>

                <div class="form-group">
                    <label for="class_roll">Class Roll <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="class_roll" name="class_roll" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department <span class="text-danger">*</span></label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM departments WHERE approved = 1 ORDER BY department");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['department']) . '">' . htmlspecialchars($row['department']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="session">Session <span class="text-danger">*</span></label>
                        <select class="form-control" id="session" name="session" required>
                            <option value="">Select Session</option>
                            <?php
                            $current_year = date('Y');
                            for ($i = 0; $i < 5; $i++) {
                                $year = $current_year - $i;
                                $session = $year . '-' . ($year + 1);
                                echo '<option value="' . $session . '">' . $session . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="mobile" name="mobile" pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <div class="password-field">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                    <div class="password-field">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <span class="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Register</button>
            </form>
            
            <div class="links-container">
                <a href="index.php">Already registered? Login</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        });
    </script>
</body>
</html> 