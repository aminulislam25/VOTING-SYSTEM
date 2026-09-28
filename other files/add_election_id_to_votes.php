<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Adding election_id column to votes table...\n\n";

// Check if the column already exists
$checkColumn = $conn->query("SHOW COLUMNS FROM votes LIKE 'election_id'");
if ($checkColumn->num_rows === 0) {
    // Add election_id column
    $alterTable = "ALTER TABLE votes 
                   ADD COLUMN election_id INT(11) NOT NULL AFTER vote_id,
                   ADD FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE";
    
    if ($conn->query($alterTable)) {
        echo "Successfully added election_id column to votes table.\n";
    } else {
        echo "Error adding election_id column: " . $conn->error . "\n";
    }
} else {
    echo "election_id column already exists in votes table.\n";
}

echo "\nDone!\n";
echo "</pre>";
?> 