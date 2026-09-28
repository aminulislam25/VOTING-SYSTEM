<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if required parameters are present
if (!isset($_POST['department_id']) || !isset($_POST['action'])) {
    $_SESSION['error_message'] = 'Invalid request';
    header('Location: manage_departments.php');
    exit();
}

$department_id = $_POST['department_id'];
$action = $_POST['action'];

// Validate action
if ($action !== 'approve' && $action !== 'reject') {
    $_SESSION['error_message'] = 'Invalid action';
    header('Location: manage_departments.php');
    exit();
}

// Update department status
$status = ($action === 'approve') ? 1 : 0;
$query = "UPDATE departments SET approved = ? WHERE department_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $status, $department_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Department ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully';
} else {
    $_SESSION['error_message'] = 'Error updating department status: ' . $conn->error;
}

$stmt->close();
header('Location: manage_departments.php');
exit(); 