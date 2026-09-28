<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not logged in']));
}

// Get student ID from session
$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get candidate ID from POST data
    $candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
    
    if ($candidate_id <= 0) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid candidate']));
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get candidate details (including position_id)
        $stmt = $conn->prepare("SELECT position_id FROM candidates WHERE candidate_id = ?");
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid candidate selected");
        }
        
        $candidate = $result->fetch_assoc();
        $position_id = $candidate['position_id'];
        
        // Check if student has already voted for this position
        $stmt = $conn->prepare("SELECT * FROM votes WHERE voter_id = ? AND position_id = ?");
        $stmt->bind_param("ii", $student_id, $position_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("You have already voted for this position");
        }
        
        // Insert vote
        $stmt = $conn->prepare("INSERT INTO votes (voter_id, candidate_id, position_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $student_id, $candidate_id, $position_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error recording vote");
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Vote recorded successfully']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 