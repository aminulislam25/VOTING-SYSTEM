<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid department ID.';
    header('Location: manage_departments.php');
    exit();
}

$department_id = intval($_GET['id']);

// Optionally, you can check for dependencies before deleting (e.g., students, candidates, etc.)

$query = "DELETE FROM departments WHERE department_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $department_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Department deleted successfully.';
} else {
    $_SESSION['error_message'] = 'Error deleting department: ' . $conn->error;
}

$stmt->close();
header('Location: manage_departments.php');
exit(); 