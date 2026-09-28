<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Setting up database tables...\n\n";

// Create voters table
$createVoters = "CREATE TABLE IF NOT EXISTS voters (
    voter_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    registration_no VARCHAR(50) NOT NULL,
    class_roll VARCHAR(50) NOT NULL,
    session VARCHAR(20) NOT NULL,
    stream VARCHAR(50) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createVoters)) {
    echo "Voters table created successfully.\n";
} else {
    echo "Error creating voters table: " . mysqli_error($conn) . "\n";
}

// Create departments table
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

if (mysqli_query($conn, $createDepartments)) {
    echo "Departments table created successfully.\n";
} else {
    echo "Error creating departments table: " . mysqli_error($conn) . "\n";
}

// Create positions table
$createPositions = "CREATE TABLE IF NOT EXISTS positions (
    position_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createPositions)) {
    echo "Positions table created successfully.\n";
} else {
    echo "Error creating positions table: " . mysqli_error($conn) . "\n";
}

// Create candidates table
$createCandidates = "CREATE TABLE IF NOT EXISTS candidates (
    candidate_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position_id INT(11) NOT NULL,
    department VARCHAR(100) NOT NULL,
    photo VARCHAR(255),
    manifesto TEXT,
    last_sem_percentage DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions(position_id)
)";

if (mysqli_query($conn, $createCandidates)) {
    echo "Candidates table created successfully.\n";
} else {
    echo "Error creating candidates table: " . mysqli_error($conn) . "\n";
}

// Create votes table
$createVotes = "CREATE TABLE IF NOT EXISTS votes (
    vote_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    voter_id INT(11) NOT NULL,
    candidate_id INT(11) NOT NULL,
    position_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (voter_id) REFERENCES voters(voter_id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id),
    FOREIGN KEY (position_id) REFERENCES positions(position_id)
)";

if (mysqli_query($conn, $createVotes)) {
    echo "Votes table created successfully.\n";
} else {
    echo "Error creating votes table: " . mysqli_error($conn) . "\n";
}

echo "\nAll tables have been set up successfully!\n";
echo "</pre>";

echo "<p>You can now go back to the <a href='admin/dashboard.php'>admin dashboard</a>.</p>";
?> 