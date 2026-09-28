<?php
require_once 'config.php';

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_name VARCHAR(100),
        mobile VARCHAR(15) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS voters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100),
        mobile VARCHAR(15) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hod_name VARCHAR(100),
        hod_phone VARCHAR(15) UNIQUE,
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS candidates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        department_id INT,
        position VARCHAR(100),
        description TEXT,
        photo VARCHAR(255),
        votes INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        voter_id INT,
        candidate_id INT,
        voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE CASCADE,
        FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
        UNIQUE KEY unique_vote (voter_id, candidate_id)
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Create test users
$test_users = [
    [
        'table' => 'admins',
        'name_field' => 'admin_name',
        'mobile_field' => 'mobile',
        'name' => 'Test Admin',
        'mobile' => '1234567890',
        'password' => 'admin123'
    ],
    [
        'table' => 'voters',
        'name_field' => 'student_name',
        'mobile_field' => 'mobile',
        'name' => 'Test Voter',
        'mobile' => '9876543210',
        'password' => 'voter123'
    ],
    [
        'table' => 'departments',
        'name_field' => 'hod_name',
        'mobile_field' => 'hod_phone',
        'name' => 'Test HOD',
        'mobile' => '5555555555',
        'password' => 'dept123'
    ]
];

foreach ($test_users as $user) {
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    
    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM {$user['table']} WHERE {$user['mobile_field']} = ?");
    $check->bind_param("s", $user['mobile']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO {$user['table']} ({$user['name_field']}, {$user['mobile_field']}, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user['name'], $user['mobile'], $hashed_password);
        
        if ($stmt->execute()) {
            echo "Created {$user['table']} user with mobile: {$user['mobile']} and password: {$user['password']}<br>";
        } else {
            echo "Error creating {$user['table']} user: " . $stmt->error . "<br>";
        }
    } else {
        echo "User already exists in {$user['table']} with mobile: {$user['mobile']}<br>";
    }
}

// Add some test candidates
if ($conn->query("SELECT id FROM candidates LIMIT 1")->num_rows === 0) {
    // Get the first department ID
    $dept_result = $conn->query("SELECT id FROM departments LIMIT 1");
    if ($dept_row = $dept_result->fetch_assoc()) {
        $dept_id = $dept_row['id'];
        
        $test_candidates = [
            [
                'name' => 'John Doe',
                'position' => 'President',
                'description' => 'Experienced leader with great vision'
            ],
            [
                'name' => 'Jane Smith',
                'position' => 'Vice President',
                'description' => 'Dedicated to student welfare'
            ]
        ];
        
        foreach ($test_candidates as $candidate) {
            $stmt = $conn->prepare("INSERT INTO candidates (name, department_id, position, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $candidate['name'], $dept_id, $candidate['position'], $candidate['description']);
            
            if ($stmt->execute()) {
                echo "Created candidate: {$candidate['name']} - {$candidate['position']}<br>";
            } else {
                echo "Error creating candidate: " . $stmt->error . "<br>";
            }
        }
    }
}

$conn->close();
echo "<br>You can now try logging in with any of these credentials.";
?> 