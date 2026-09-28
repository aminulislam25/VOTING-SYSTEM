<?php
require_once 'config.php';

// Add mobile column if it doesn't exist
$check_column = "SHOW COLUMNS FROM departments LIKE 'mobile'";
$result = $conn->query($check_column);

if ($result->num_rows === 0) {
    $add_column = "ALTER TABLE departments ADD COLUMN mobile VARCHAR(15) AFTER email";
    if ($conn->query($add_column)) {
        echo "Mobile column added successfully.<br>";
    } else {
        echo "Error adding mobile column: " . $conn->error . "<br>";
    }
}

// Add name column if it doesn't exist
$check_name_column = "SHOW COLUMNS FROM departments LIKE 'name'";
$result = $conn->query($check_name_column);

if ($result->num_rows === 0) {
    $add_name_column = "ALTER TABLE departments ADD COLUMN name VARCHAR(100) AFTER id";
    if ($conn->query($add_name_column)) {
        echo "Department name column added successfully.<br>";
    } else {
        echo "Error adding department name column: " . $conn->error . "<br>";
    }
}

// Update existing departments to have a default mobile number if null
$update_mobile = "UPDATE departments SET mobile = CONCAT('+91', FLOOR(RAND() * 9000000000) + 1000000000) WHERE mobile IS NULL";
if ($conn->query($update_mobile)) {
    echo "Default mobile numbers added to existing departments.<br>";
} else {
    echo "Error updating mobile numbers: " . $conn->error . "<br>";
}

// Update existing departments to have a default name if null
$update_name = "UPDATE departments SET name = CONCAT('Department ', id) WHERE name IS NULL";
if ($conn->query($update_name)) {
    echo "Default department names added to existing departments.<br>";
} else {
    echo "Error updating department names: " . $conn->error . "<br>";
}

echo "Database update completed.";
$conn->close();
?> 