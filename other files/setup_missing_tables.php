<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "Setting up missing tables...\n\n";

// Check and create positions table if it doesn't exist
$result = $conn->query("SHOW TABLES LIKE 'positions'");
if ($result->num_rows == 0) {
    echo "Creating positions table...\n";
    $createPositions = "CREATE TABLE IF NOT EXISTS positions (
        position_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        position_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createPositions)) {
        echo "Positions table created successfully.\n";
    } else {
        echo "Error creating positions table: " . $conn->error . "\n";
    }
} else {
    echo "Positions table already exists.\n";
}

// Check and create candidates table if it doesn't exist
$result = $conn->query("SHOW TABLES LIKE 'candidates'");
if ($result->num_rows == 0) {
    echo "Creating candidates table...\n";
    $createCandidates = "CREATE TABLE IF NOT EXISTS candidates (
        candidate_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        position_id INT(11) NOT NULL,
        department VARCHAR(100) NOT NULL,
        photo VARCHAR(255),
        bio TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($createCandidates)) {
        echo "Candidates table created successfully.\n";
    } else {
        echo "Error creating candidates table: " . $conn->error . "\n";
    }
} else {
    echo "Candidates table already exists.\n";
}

// Check and create voters table if it doesn't exist
$result = $conn->query("SHOW TABLES LIKE 'voters'");
if ($result->num_rows == 0) {
    echo "Creating voters table...\n";
    $createVoters = "CREATE TABLE IF NOT EXISTS voters (
        voter_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100) NOT NULL,
        registration_no VARCHAR(50) NOT NULL,
        class_roll_no VARCHAR(50),
        session ENUM('2021-22', '2022-23', '2023-24') NOT NULL,
        stream ENUM('Science', 'Arts', 'Commerce') NOT NULL,
        department VARCHAR(100) NOT NULL,
        mobile VARCHAR(15) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createVoters)) {
        echo "Voters table created successfully.\n";
    } else {
        echo "Error creating voters table: " . $conn->error . "\n";
    }
} else {
    echo "Voters table already exists.\n";
}

// Check and create votes table if it doesn't exist
$result = $conn->query("SHOW TABLES LIKE 'votes'");
if ($result->num_rows == 0) {
    echo "Creating votes table...\n";
    
    // First, check if the required tables exist
    $requiredTables = ['voters', 'candidates', 'positions'];
    $allTablesExist = true;
    
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            echo "Error: Required table '$table' does not exist. Cannot create votes table.\n";
            $allTablesExist = false;
        }
    }
    
    if ($allTablesExist) {
        // Create votes table without foreign key constraints
        $createVotes = "CREATE TABLE IF NOT EXISTS votes (
            vote_id INT(11) AUTO_INCREMENT PRIMARY KEY,
            voter_id INT(11) NOT NULL,
            candidate_id INT(11) NOT NULL,
            position_id INT(11) NOT NULL,
            voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createVotes)) {
            echo "Votes table created successfully (without foreign key constraints).\n";
        } else {
            echo "Error creating votes table: " . $conn->error . "\n";
        }
    }
} else {
    echo "Votes table already exists.\n";
}

echo "\nTable setup complete.\n";
echo "</pre>";
?> 