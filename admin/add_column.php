<?php
require_once '../config.php';

try {
    // First check if the column exists
    $check_column = $conn->query("SHOW COLUMNS FROM candidates LIKE 'last_sem_percentage'");
    if ($check_column->num_rows == 0) {
        // Add the last_sem_percentage column
        $sql = "ALTER TABLE candidates ADD COLUMN last_sem_percentage DECIMAL(5,2) DEFAULT NULL AFTER department";
        if ($conn->query($sql)) {
            echo "Column 'last_sem_percentage' added successfully!";
        } else {
            echo "Error adding column: " . $conn->error;
        }
    } else {
        echo "Column 'last_sem_percentage' already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 