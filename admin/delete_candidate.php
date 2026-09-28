<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if candidate ID is provided
if (!isset($_GET['id'])) {
    header('Location: manage_candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

try {
    // First, check if the candidate exists and get their photo
    $stmt = $conn->prepare("SELECT photo FROM candidates WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Candidate not found.";
        header('Location: manage_candidates.php');
        exit();
    }

    $candidate = $result->fetch_assoc();

    // Delete any votes for this candidate
    $stmt = $conn->prepare("DELETE FROM votes WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();

    // Delete the candidate
    $stmt = $conn->prepare("DELETE FROM candidates WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    
    if ($stmt->execute()) {
        // Delete the candidate's photo if it exists
        if (!empty($candidate['photo'])) {
            $photo_path = '../uploads/candidates/' . $candidate['photo'];
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
        $_SESSION['success_message'] = "Candidate deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting candidate. Please try again.";
    }
} catch (Exception $e) {
    error_log("Error deleting candidate: " . $e->getMessage());
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

$conn->close();
header('Location: manage_candidates.php');
exit();
?> 