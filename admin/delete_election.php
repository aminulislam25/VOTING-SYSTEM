<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid election ID.';
    header('Location: manage_elections.php');
    exit();
}

$election_id = intval($_GET['id']);

// Optionally, you can check for dependencies before deleting (e.g., votes, candidates linked to this election, etc.)

$query = "DELETE FROM elections WHERE election_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $election_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Election deleted successfully.';
} else {
    $_SESSION['error_message'] = 'Error deleting election: ' . $conn->error;
}

$stmt->close();
header('Location: manage_elections.php');
exit(); 