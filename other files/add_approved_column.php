<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Checking and adding 'approved' column to voters table...\n\n";

// Check if approved column exists in voters table
$result = $conn->query("SHOW COLUMNS FROM voters LIKE 'approved'");
if ($result->num_rows == 0) {
    echo "Adding 'approved' column to voters table...\n";
    
    $alterTable = "ALTER TABLE voters ADD COLUMN approved TINYINT(1) DEFAULT 0";
    
    if ($conn->query($alterTable)) {
        echo "'approved' column added successfully.\n";
    } else {
        echo "Error adding 'approved' column: " . $conn->error . "\n";
    }
} else {
    echo "'approved' column already exists in voters table.\n";
}

echo "\nVoters table check complete.\n";
echo "</pre>";

echo "<p>You can now go back to the <a href='department/view_voters.php'>department dashboard</a>.</p>";
?> 