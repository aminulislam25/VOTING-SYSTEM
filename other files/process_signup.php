<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_type = $_POST['user_type'] ?? '';

    // Validate user type
    if ($user_type !== 'department') {
        $_SESSION['error'] = "Invalid user type";
        header('Location: signup.php');
        exit();
    }

    // Department registration
    $department_name = $_POST['department_name'] ?? '';
    $college = $_POST['college'] ?? '';
    $hod_name = $_POST['hod_name'] ?? '';
    $hod_email = $_POST['hod_email'] ?? '';
    $hod_mobile = $_POST['hod_mobile'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($department_name) || empty($college) || empty($hod_name) || 
        empty($hod_email) || empty($hod_mobile) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please fill in all required fields";
        header('Location: signup.php');
        exit();
    }

    // Validate email format
    if (!filter_var($hod_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address";
        header('Location: signup.php');
        exit();
    }

    // Validate mobile number
    if (!preg_match('/^[0-9]{10}$/', $hod_mobile)) {
        $_SESSION['error'] = "Please enter a valid 10-digit mobile number";
        header('Location: signup.php');
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header('Location: signup.php');
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM departments WHERE hod_email = ?");
    $stmt->bind_param("s", $hod_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already registered. Please use a different email.";
        header('Location: signup.php');
        exit();
    }

    // Check if mobile already exists
    $stmt = $conn->prepare("SELECT * FROM departments WHERE hod_mobile = ?");
    $stmt->bind_param("s", $hod_mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Mobile number already registered. Please use a different mobile number.";
        header('Location: signup.php');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert department data
    $stmt = $conn->prepare("INSERT INTO departments (department, college, hod_name, hod_email, hod_mobile, password, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssss", $department_name, $college, $hod_name, $hod_email, $hod_mobile, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Department registration successful. Please wait for admin approval.";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = "Registration failed: " . $conn->error;
        header('Location: signup.php');
        exit();
    }
} else {
    header('Location: signup.php');
    exit();
}
?> 