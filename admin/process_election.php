<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Function to set error message and redirect
function setErrorAndRedirect($message) {
    $_SESSION['error_message'] = $message;
    header('Location: manage_elections.php');
    exit();
}

// Function to set success message and redirect
function setSuccessAndRedirect($message) {
    $_SESSION['success_message'] = $message;
    header('Location: manage_elections.php');
    exit();
}

// Function to validate datetime
function validateDateTime($datetime) {
    return (bool)strtotime($datetime);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        setErrorAndRedirect("All fields are required.");
    }
    
    // Validate date formats
    if (!validateDateTime($start_date) || !validateDateTime($end_date)) {
        setErrorAndRedirect("Invalid date format.");
    }
    
    // Convert dates to MySQL format
    $start_date = date('Y-m-d H:i:s', strtotime($start_date));
    $end_date = date('Y-m-d H:i:s', strtotime($end_date));
    
    // Validate date logic
    if (strtotime($end_date) <= strtotime($start_date)) {
        setErrorAndRedirect("End date must be after start date.");
    }
    
    // Check if dates are in the future
    if (strtotime($start_date) < time()) {
        setErrorAndRedirect("Start date must be in the future.");
    }
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Insert new election
        $stmt = $conn->prepare("INSERT INTO elections (title, description, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("ssss", $title, $description, $start_date, $end_date);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating election: " . $stmt->error);
        }
        
        // Get the new election ID
        $election_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        setSuccessAndRedirect("Election created successfully!");
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in process_election.php: " . $e->getMessage());
        setErrorAndRedirect("An error occurred while creating the election. Please try again.");
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
    // Handle election status changes
    $election_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Validate election exists
    $stmt = $conn->prepare("SELECT * FROM elections WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        setErrorAndRedirect("Election not found.");
    }
    
    $election = $result->fetch_assoc();
    
    try {
        switch ($action) {
            case 'activate':
                // Check if dates are valid for activation
                if (strtotime($election['end_date']) < time()) {
                    setErrorAndRedirect("Cannot activate an election that has already ended.");
                }
                
                $new_status = 'active';
                break;
                
            case 'deactivate':
                $new_status = 'inactive';
                break;
                
            case 'complete':
                // Check if election has ended
                if (strtotime($election['end_date']) > time()) {
                    setErrorAndRedirect("Cannot complete an election before its end date.");
                }
                
                $new_status = 'completed';
                break;
                
            case 'cancel':
                $new_status = 'cancelled';
                break;
                
            default:
                setErrorAndRedirect("Invalid action.");
        }
        
        // Update election status
        $stmt = $conn->prepare("UPDATE elections SET status = ? WHERE election_id = ?");
        $stmt->bind_param("si", $new_status, $election_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating election status: " . $stmt->error);
        }
        
        setSuccessAndRedirect("Election status updated successfully!");
        
    } catch (Exception $e) {
        error_log("Error in process_election.php: " . $e->getMessage());
        setErrorAndRedirect("An error occurred while updating the election status. Please try again.");
    }
} else {
    // Invalid request method
    setErrorAndRedirect("Invalid request method.");
}
?> 