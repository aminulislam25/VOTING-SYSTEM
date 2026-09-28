<?php
session_start();
require_once 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_register.php');
    exit();
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$registration_no = trim($_POST['registration_no'] ?? '');
$class_roll = trim($_POST['class_roll'] ?? '');
$department = trim($_POST['department'] ?? '');
$session = trim($_POST['session'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($registration_no) || 
    empty($class_roll) || empty($department) || empty($session) || 
    empty($email) || empty($mobile) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required";
    header('Location: student_register.php');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header('Location: student_register.php');
    exit();
}

// Validate mobile number (10 digits)
if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    $_SESSION['error'] = "Mobile number must be 10 digits";
    header('Location: student_register.php');
    exit();
}

// Check if passwords match
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header('Location: student_register.php');
    exit();
}

// Create students table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    registration_no VARCHAR(100) NOT NULL UNIQUE,
    class_roll VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    session VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($create_table_query)) {
    $_SESSION['error'] = "Error creating students table: " . $conn->error;
    header('Location: student_register.php');
    exit();
}

// Check if registration number already exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE registration_no = ?");
$stmt->bind_param("s", $registration_no);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    $_SESSION['error'] = "Registration number already exists";
    header('Location: student_register.php');
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    $_SESSION['error'] = "Email already exists";
    header('Location: student_register.php');
    exit();
}

// Check if mobile already exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE mobile = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    $_SESSION['error'] = "Mobile number already exists";
    header('Location: student_register.php');
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert student into database
$stmt = $conn->prepare("INSERT INTO students (first_name, last_name, registration_no, class_roll, department, session, email, mobile, password, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("sssssssss", $first_name, $last_name, $registration_no, $class_roll, $department, $session, $email, $mobile, $hashed_password);

try {
    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Your account is pending approval by your department. You'll be able to login once approved.";
        header('Location: index.php');
        exit();
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: student_register.php');
    exit();
}
?> 