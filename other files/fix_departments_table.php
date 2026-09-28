<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "Fixing departments table structure...\n\n";

// First, check if the table exists
$result = $conn->query("SHOW TABLES LIKE 'departments'");
if ($result->num_rows > 0) {
    // Drop the existing table
    if ($conn->query("DROP TABLE departments")) {
        echo "Dropped existing departments table.\n";
    } else {
        echo "Error dropping departments table: " . $conn->error . "\n";
    }
} else {
    echo "Departments table does not exist yet.\n";
}

// Create the departments table with the correct structure
$createDepartments = "CREATE TABLE IF NOT EXISTS departments (
    department_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100) NOT NULL,
    hod_name VARCHAR(100) NOT NULL,
    hod_email VARCHAR(100) NOT NULL,
    hod_mobile VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createDepartments)) {
    echo "Departments table created successfully with the correct structure.\n";
} else {
    echo "Error creating departments table: " . $conn->error . "\n";
}

// Verify the new structure
echo "\nVerifying new departments table structure:\n";
$result = $conn->query("SHOW COLUMNS FROM departments");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}

echo "</pre>";
?> 