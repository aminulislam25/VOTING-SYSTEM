<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Adding election_id column to candidates table...\n\n";

// Add election_id column if it doesn't exist
$checkColumn = $conn->query("SHOW COLUMNS FROM candidates LIKE 'election_id'");
if ($checkColumn->num_rows === 0) {
    $alterTable = "ALTER TABLE candidates 
                   ADD COLUMN election_id INT(11) DEFAULT NULL,
                   ADD FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE SET NULL";
    
    if ($conn->query($alterTable)) {
        echo "Successfully added election_id column to candidates table.\n";
    } else {
        echo "Error adding election_id column: " . $conn->error . "\n";
    }
} else {
    echo "election_id column already exists in candidates table.\n";
}

echo "\nDone!\n";
echo "</pre>";

echo "<p>You can now go back to <a href='manage_election.php'>manage elections</a> or <a href='dashboard.php'>dashboard</a>.</p>";
?> 