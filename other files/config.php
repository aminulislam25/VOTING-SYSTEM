<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$db_host = 'localhost';  // Using IP instead of localhost
$db_user = 'root';
$db_pass = '';
$db_name = 'voting_system';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Function to check user type
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Function to get user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 