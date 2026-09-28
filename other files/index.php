<?php
session_start();
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit();
} elseif (isset($_SESSION['department_id'])) {
    header('Location: department/dashboard.php');
    exit();
} elseif (isset($_SESSION['student_id'])) {
    header('Location: student/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #0077b6;
        --secondary-color: #023e8a;
        --success-color: #38b000;
        --warning-color: #f77f00;
        --text-primary: #1e1e1e;
        --text-secondary: #5a5a5a;
        --bg-light: #f1f5f9;
        --border-radius: 10px;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #eaf4f9 0%, #ffffff 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    /* 🔵 Animated Background Shapes */
    .shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.07;
        z-index: 0;
        animation: float 10s ease-in-out infinite;
    }

    .shape-1 {
        width: 160px;
        height: 160px;
        background: var(--primary-color);
        top: -50px;
        left: -50px;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 200px;
        height: 200px;
        background: var(--secondary-color);
        bottom: -70px;
        right: -70px;
        animation-delay: 2s;
    }

    .shape-3 {
        width: 120px;
        height: 120px;
        background: var(--warning-color);
        top: 35%;
        left: -60px;
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .container {
        width: 100%;
        max-width: 420px;
        z-index: 1;
        animation: fadeIn 0.8s ease-in-out;
    }

    .login-box {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 30px;
        transition: var(--transition);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(25px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .login-header h2 {
        color: var(--primary-color);
        font-size: 1.8rem;
        margin-bottom: 8px;
    }

    .login-header p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .user-type-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .user-type-btn {
        flex: 1;
        padding: 10px;
        border: 1.5px solid #ced4da;
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
        gap: 6px;
        position: relative;
        overflow: hidden;
    }

    .user-type-btn.active {
        border-color: var(--primary-color);
        background: rgba(0, 119, 182, 0.08);
        color: var(--primary-color);
    }

    .user-type-btn:hover {
        border-color: var(--primary-color);
        background: rgba(0, 119, 182, 0.05);
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        color: var(--text-primary);
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1.5px solid #ced4da;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(0, 119, 182, 0.2);
    }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 10px rgba(0, 119, 182, 0.3);
    }

    .submit-btn:hover {
        background: var(--secondary-color);
    }

    .alert {
        padding: 12px;
        border-radius: var(--border-radius);
        margin-bottom: 18px;
        font-size: 0.9rem;
    }

    .alert-error {
        background: rgba(247, 127, 0, 0.1);
        color: var(--warning-color);
    }

    .alert-success {
        background: rgba(56, 176, 0, 0.1);
        color: var(--success-color);
    }

    .links-container {
        text-align: center;
        margin-top: 18px;
    }

    .links-container a {
        color: var(--secondary-color);
        text-decoration: none;
        margin: 0 8px;
        font-weight: 500;
    }

    .links-container a:hover {
        text-decoration: underline;
    }

    @media (max-width: 480px) {
        .container {
            padding: 10px;
        }

        .login-box {
            padding: 25px;
        }

        .user-type-selector {
            flex-direction: column;
        }

        .user-type-btn {
            width: 100%;
        }
    }
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #eaf4f9 0%, #ffffff 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: 
        linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)),
        url('https://www.karimganjcollege.ac.in/img/college-front-full.jpg') no-repeat center center/cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
}
backdrop-filter: blur(6px);
background: rgba(255, 255, 255, 0.85);
.login-box {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(6px);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    padding: 30px;
    transition: var(--transition);
}

</style>


</head>
<body>
<div class="shape shape-1"></div>
<div class="shape shape-2"></div>
<div class="shape shape-3"></div>

    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h2>ONLINE VOTING SYSTEM</h2>
                <p>Please login to your account</p>
            </div>
            
            <?php if(isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                <div class="alert alert-success">
                    You have been successfully logged out.
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="user-type-selector">
                <button type="button" class="user-type-btn active" data-type="student">
                    <i class="fas fa-user-graduate"></i> Student
                </button>
                <button type="button" class="user-type-btn" data-type="department">
                    <i class="fas fa-building"></i> Department
                </button>
                <button type="button" class="user-type-btn" data-type="admin">
                    <i class="fas fa-user-shield"></i> Admin
                </button>
            </div>

            <form action="process_login.php" method="POST">
                <input type="hidden" name="user_type" id="user_type" value="student">
                
                <div class="form-group">
                    <label for="mobile" id="mobile-label">Mobile Number / Registration Number</label>
                    <input type="text" id="mobile" name="mobile" placeholder="Enter your mobile or registration number">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="links-container">
                <a href="student_register.php" id="student-signup-link">Register as Student</a>
                <a href="department_register.php" id="department-signup-link" style="display: none;">Register Department</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeBtns = document.querySelectorAll('.user-type-btn');
            const userTypeInput = document.getElementById('user_type');
            const mobileLabel = document.getElementById('mobile-label');
            const mobileInput = document.getElementById('mobile');
            const deptSignupLink = document.getElementById('department-signup-link');
            const studentSignupLink = document.getElementById('student-signup-link');
            
            // Set initial state
            updateFormForUserType('student');

            userTypeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    userTypeBtns.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Update hidden input value
                    const userType = this.dataset.type;
                    userTypeInput.value = userType;
                    
                    // Update form based on user type
                    updateFormForUserType(userType);
                });
            });

            function updateFormForUserType(userType) {
                // Update label and pattern based on user type
                if (userType === 'admin') {
                    mobileLabel.textContent = 'Mobile Number';
                    mobileInput.pattern = '[0-9]{10}';
                    mobileInput.placeholder = 'Enter your 10-digit mobile number';
                    deptSignupLink.style.display = 'none';
                    studentSignupLink.style.display = 'none';
                } else if (userType === 'department') {
                    mobileLabel.textContent = 'Mobile Number / Email';
                    mobileInput.removeAttribute('pattern');
                    mobileInput.placeholder = 'Enter your mobile number or email';
                    deptSignupLink.style.display = 'inline';
                    studentSignupLink.style.display = 'none';
                } else if (userType === 'student') {
                    mobileLabel.textContent = 'Registration Number / Mobile';
                    mobileInput.removeAttribute('pattern');
                    mobileInput.placeholder = 'Enter your registration number or mobile';
                    deptSignupLink.style.display = 'none';
                    studentSignupLink.style.display = 'inline';
                }
            }
        });
    </script>
</body>
</html>