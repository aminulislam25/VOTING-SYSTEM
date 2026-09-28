<?php
require_once 'config.php';

// Drop existing tables if they exist
$tables = ['votes', 'candidates', 'positions', 'voters', 'departments', 'admins'];
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        mobile VARCHAR(15) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        college ENUM('Miranda House', 'Hindu College', 'Lady Shri Ram College', 'St. Stephen\'s College', 'Ramjas College') NOT NULL,
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS voters (
        voter_id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100) NOT NULL,
        mobile VARCHAR(15) NOT NULL UNIQUE,
        registration_no VARCHAR(20) NOT NULL UNIQUE,
        college VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        stream VARCHAR(20) NOT NULL,
        session VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        has_voted BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS departments (
        department_id INT AUTO_INCREMENT PRIMARY KEY,
        department ENUM('Computer Science and Application', 'Botany', 'Zoology', 'Economics') UNIQUE NOT NULL,
        hod_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        mobile VARCHAR(15) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS candidates (
        candidate_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        department ENUM('Computer Science and Application', 'Botany', 'Zoology', 'Economics') NOT NULL,
        position VARCHAR(100) NOT NULL,
        photo VARCHAR(255),
        manifesto TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS votes (
        vote_id INT AUTO_INCREMENT PRIMARY KEY,
        voter_id INT NOT NULL,
        candidate_id INT NOT NULL,
        position VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (voter_id) REFERENCES voters(voter_id) ON DELETE CASCADE,
        FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
        UNIQUE KEY unique_vote (voter_id, position)
    )",

    "CREATE TABLE IF NOT EXISTS positions (
        position_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Create default admin account if not exists
$admin_mobile = "9876543210";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (admin_name, email, mobile, password, college, address) 
                       SELECT 'Admin User', 'admin@example.com', ?, ?, 'Miranda House', 'New Delhi' 
                       WHERE NOT EXISTS (SELECT 1 FROM admins WHERE mobile = ?)");
$stmt->bind_param("sss", $admin_mobile, $admin_password, $admin_mobile);
$stmt->execute();

// Create test voter account if not exists
$voter_mobile = "9876543211";
$voter_password = password_hash("voter123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO voters (student_name, mobile, registration_no, college, department, stream, session, password) 
                       SELECT 'Test Voter', ?, '2024001', 'Miranda House', 'Computer Science', 'B.Tech', '2024-25', ? 
                       WHERE NOT EXISTS (SELECT 1 FROM voters WHERE mobile = ?)");
$stmt->bind_param("sss", $voter_mobile, $voter_password, $voter_mobile);
$stmt->execute();

// Create test department account if not exists
$dept_mobile = "9876543212";
$dept_password = password_hash("dept123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO departments (department, hod_name, email, mobile, password) 
                       SELECT 'Computer Science and Application', 'Test HOD', 'hod@example.com', ?, ? 
                       WHERE NOT EXISTS (SELECT 1 FROM departments WHERE mobile = ?)");
$stmt->bind_param("sss", $dept_mobile, $dept_password, $dept_mobile);
$stmt->execute();

echo "Database setup completed successfully!<br>";
echo "Default admin account:<br>";
echo "Mobile: 9876543210<br>";
echo "Password: admin123<br><br>";
echo "Test voter account:<br>";
echo "Mobile: 9876543211<br>";
echo "Password: voter123<br><br>";
echo "Test department account:<br>";
echo "Mobile: 9876543212<br>";
echo "Password: dept123<br>";

$conn->close();
?> 