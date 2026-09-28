<?php
session_start();
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id']) || isset($_SESSION['department_id'])) {
    // If user is already logged in, redirect to dashboard
    if (isset($_SESSION['admin_id'])) {
        header("Location: admin/dashboard.php");
    } elseif (isset($_SESSION['department_id'])) {
        header("Location: department/dashboard.php");
    }
    exit();
}
if (isset($_POST['submit'])) {
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];
    
    // Fix SQL queries syntax
    $select1 = "SELECT * FROM admins WHERE mobile = '$mobile' AND password = '$password'";
    $select2 = "SELECT * FROM voters WHERE mobile = '$mobile' AND password = '$password'";
    $select3 = "SELECT * FROM departments WHERE hod_mobile = '$mobile' AND password = '$password'";
    
    if(mysqli_num_rows(mysqli_query($conn, $select1)) > 0) {
        $_SESSION['error'] = "Admin already exists";
        header('Location: signup.php');
    }
    
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System - Sign Up</title>
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

        .container {
            width: 100%;
            max-width: 500px;
        }

        .signup-box {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 30px;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header h2 {
            color: var(--text-primary);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .signup-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .user-type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .user-type-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            background: white;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .user-type-btn.active {
            border-color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
            color: var(--primary-color);
        }

        .user-type-btn:hover {
            border-color: var(--primary-color);
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

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
            min-height: 100px;
            resize: vertical;
        }

        .form-group textarea:focus {
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

        .links-container {
            text-align: center;
            margin-top: 20px;
        }

        .links-container a {
            color: #2196F3;
            text-decoration: none;
            margin: 0 10px;
        }

        .links-container a:hover {
            text-decoration: underline;
        }

        .voter-fields, .department-fields, .admin-fields {
            display: none;
        }

        .voter-fields.active, .department-fields.active, .admin-fields.active {
            display: block;
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .signup-box {
                padding: 20px;
            }
            
            .user-type-selector {
                flex-direction: column;
            }
            
            .user-type-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-box">
            <div class="signup-header">
                <h2>Create Account</h2>
                <p>Please fill in the details to sign up</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="user-type-selector">
                <button type="button" class="user-type-btn active" data-type="department">
                    <i class="fas fa-building"></i> Department
                </button>
            </div>

            <form action="process_signup.php" method="POST">
                <input type="hidden" name="user_type" id="user_type" value="department">
                
                <!-- Department Fields -->
                <div class="department-fields active">
                    <div class="form-group">
                        <label for="department_name">Department Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="department_name" name="department_name" required>
                    </div>
                    <div class="form-group">
                        <label for="college">College/University <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="college" name="college" required>
                    </div>
                    <div class="form-group">
                        <label for="hod_name">HOD Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hod_name" name="hod_name" required>
                    </div>
                    <div class="form-group">
                        <label for="hod_email">HOD Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="hod_email" name="hod_email" required>
                    </div>
                    <div class="form-group">
                        <label for="hod_mobile">HOD Mobile <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hod_mobile" name="hod_mobile" pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" required>
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
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            
            <div class="links-container">
                <a href="index.php">Already registered? Log In</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeBtns = document.querySelectorAll('.user-type-btn');
            const userTypeInput = document.getElementById('user_type');
            const voterFields = document.querySelector('.voter-fields');
            const departmentFields = document.querySelector('.department-fields');
            const adminFields = document.querySelector('.admin-fields');
            const form = document.getElementById('signupForm');

            userTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    userTypeBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Update hidden input
                    const userType = this.dataset.type;
                    userTypeInput.value = userType;

                    // Show/hide appropriate fields
                    voterFields.classList.remove('active');
                    departmentFields.classList.remove('active');
                    adminFields.classList.remove('active');

                    switch(userType) {
                        case 'voter':
                            voterFields.classList.add('active');
                            break;
                        case 'department':
                            departmentFields.classList.add('active');
                            break;
                        case 'admin':
                            adminFields.classList.add('active');
                            break;
                    }
                });
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const mobile = document.getElementById('mobile').value;
                const email = document.getElementById('email').value;
                const userType = document.getElementById('user_type').value;

                let errors = [];

                // Common validations
                if (!mobile) {
                    errors.push('Mobile number is required');
                } else if (!/^\d{10}$/.test(mobile)) {
                    errors.push('Please enter a valid 10-digit mobile number');
                }

                if (!email) {
                    errors.push('Email is required');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors.push('Please enter a valid email address');
                }

                if (!password) {
                    errors.push('Password is required');
                } else if (password.length < 6) {
                    errors.push('Password must be at least 6 characters long');
                }

                if (!confirmPassword) {
                    errors.push('Confirm password is required');
                } else if (password !== confirmPassword) {
                    errors.push('Passwords do not match');
                }

                // User type specific validations
                if (userType === 'voter') {
                    if (!document.getElementById('student_name').value) {
                        errors.push('Student name is required');
                    }
                    if (!document.getElementById('registration_no').value) {
                        errors.push('Registration number is required');
                    }
                    if (!document.getElementById('class_roll').value) {
                        errors.push('Class roll is required');
                    }
                    if (!document.getElementById('session').value) {
                        errors.push('Session is required');
                    }
                    if (!document.getElementById('stream').value) {
                        errors.push('Stream is required');
                    }
                    if (!document.getElementById('department').value) {
                        errors.push('Department is required');
                    }
                } else if (userType === 'department') {
                    if (!document.getElementById('dept_name').value) {
                        errors.push('Department name is required');
                    }
                    if (!document.getElementById('hod_name').value) {
                        errors.push('HOD name is required');
                    }
                } else if (userType === 'admin') {
                    if (!document.getElementById('admin_name').value) {
                        errors.push('Admin name is required');
                    }
                    if (!document.getElementById('college').value) {
                        errors.push('College is required');
                    }
                    if (!document.getElementById('address').value) {
                        errors.push('Address is required');
                    }
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        });

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