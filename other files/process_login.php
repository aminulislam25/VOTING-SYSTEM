<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_log("=== New Login Attempt ===");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Debug log
    error_log("Login attempt - User Type: $user_type, User ID/Mobile: $mobile");

    // Validate required fields
    if (empty($mobile) || empty($password) || empty($user_type)) {
        $_SESSION['error'] = "Please fill in all fields";
        header('Location: index.php');
        exit();
    }

    // Validate mobile number format for non-admin users
    if ($user_type === 'student' && !preg_match('/^[0-9]{10}$/', $mobile)) {
        $_SESSION['error'] = "Please enter a valid 10-digit registration number";
        header('Location: index.php');
        exit();
    }

    // Validate user_type
    if (!in_array($user_type, ['admin', 'department', 'student'])) {
        $_SESSION['error'] = "Invalid user type selected.";
        header('Location: index.php');
        exit();
    }

    try {
        // Determine table and name field based on user type
        switch ($user_type) {
            case 'admin':
                $table = 'admins';
                $id_field = 'id';
                $name_field = 'admin_name';
                break;
            case 'department':
                $table = 'departments';
                $id_field = 'department_id';
                $name_field = 'department';
                break;
            case 'student':
                $table = 'students';
                $id_field = 'student_id';
                $name_field = 'first_name';
                break;
            default:
                throw new Exception("Invalid user type");
        }

        // Debug log
        error_log("Querying table: $table");

        // Query to check credentials
        if ($user_type === 'department') {
            // Check if login is with email or mobile
            $is_email = filter_var($mobile, FILTER_VALIDATE_EMAIL);
            
            if ($is_email) {
                // Login with email
                $stmt = $conn->prepare("SELECT * FROM $table WHERE hod_email = ?");
                $stmt->bind_param("s", $mobile);
            } else {
                // Login with mobile
                $stmt = $conn->prepare("SELECT * FROM $table WHERE hod_mobile = ?");
                $stmt->bind_param("s", $mobile);
            }
        } else if ($user_type === 'student') {
            // Login with registration number or mobile
            $stmt = $conn->prepare("SELECT * FROM $table WHERE registration_no = ? OR mobile = ?");
            $stmt->bind_param("ss", $mobile, $mobile);
        } else {
            // Admin login - allow login with mobile
            $stmt = $conn->prepare("SELECT * FROM $table WHERE mobile = ?");
            $stmt->bind_param("s", $mobile);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        // Debug log
        error_log("Query result rows: " . $result->num_rows);

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Debug log
            error_log("Found user: " . json_encode($user));
            
            // For admin, check if it's plain text password
            if ($user_type === 'admin') {
                // Try plain text comparison first, then password_verify
                if ($password === $user['password'] || password_verify($password, $user['password'])) {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user[$name_field];
                    error_log("Admin login successful - ID: " . $user['id']);
                    header('Location: admin/dashboard.php');
                    exit();
                } else {
                    error_log("Admin password mismatch");
                    throw new Exception("Invalid password");
                }
            }
            // For other users or hashed passwords
            else if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Set session variables based on user type
                switch ($user_type) {
                    case 'admin':
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_name'] = $user[$name_field];
                        header('Location: admin/dashboard.php');
                        break;
                    case 'department':
                        // Check if department is approved
                        if ($user['approved'] != 1) {
                            error_log("Department not approved: " . $user[$id_field]);
                            throw new Exception("Your department account is pending approval by the administrator.");
                        }
                        $_SESSION['department_id'] = $user[$id_field];
                        $_SESSION['department_name'] = $user[$name_field];
                        header('Location: department/dashboard.php');
                        break;
                    case 'student':
                        // Check if student account is active and approved
                        if ($user['approved'] != 1) {
                            error_log("Student account not approved: " . $user[$id_field]);
                            throw new Exception("Your account is pending approval by your department. Please check back later.");
                        }
                        if ($user['status'] != 'active') {
                            error_log("Student account not active: " . $user[$id_field]);
                            throw new Exception("Your student account is inactive. Please contact your department.");
                        }
                        $_SESSION['student_id'] = $user[$id_field];
                        $_SESSION['student_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['student_department'] = $user['department'];
                        header('Location: student/dashboard.php');
                        break;
                }
                exit();
            } else {
                error_log("Password verification failed");
                throw new Exception("Invalid password");
            }
        } else {
            error_log("No user found with mobile/ID: $mobile");
            throw new Exception("User not found");
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "Invalid credentials. Please try again.";
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?> 