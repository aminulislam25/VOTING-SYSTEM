<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Setting up students table...\n\n";

// Create students table
$createStudents = "CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    registration_no VARCHAR(100) NOT NULL UNIQUE,
    class_roll VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    session VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createStudents)) {
    echo "Students table created successfully.\n";
} else {
    echo "Error creating students table: " . mysqli_error($conn) . "\n";
}

echo "\nSetup completed!\n";
echo "</pre>";

$conn->close();
?> 