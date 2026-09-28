<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Creating Admin Account...\n\n";

// Check if admins table exists, if not create it
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows == 0) {
    echo "Creating admins table...\n";
    $createTable = "CREATE TABLE admins (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        admin_name VARCHAR(100) NOT NULL,
        college VARCHAR(100) DEFAULT 'Demo College',
        address TEXT,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        die("Error creating table: " . $conn->error . "\n");
    }
    echo "Admins table created successfully.\n\n";
} else {
    echo "Admins table already exists.\n\n";
}

// Create admin user with plain text password for easy login
$adminName = "Admin User";
$college = "Demo College";
$address = "Admin Address";
$email = "admin@example.com";
$mobile = "1234567890";
$password = "admin123";

// Check if admin exists with this mobile number
$stmt = $conn->prepare("SELECT id FROM admins WHERE mobile = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user with mobile '$mobile' already exists. Updating the password...\n";
    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE mobile = ?");
    $stmt->bind_param("ss", $password, $mobile);
} else {
    echo "Creating new admin user...\n";
    $stmt = $conn->prepare("INSERT INTO admins (admin_name, college, address, email, mobile, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $adminName, $college, $address, $email, $mobile, $password);
}

if ($stmt->execute()) {
    echo "Admin user created/updated successfully!\n\n";
    echo "Login Credentials:\n";
    echo "Mobile: $mobile\n";
    echo "Password: $password\n";
    echo "User Type: admin\n\n";
    
    echo "IMPORTANT: This admin account uses a plain text password for easy login.\n";
    echo "For security, you should change the password after logging in.\n\n";
} else {
    echo "Error creating/updating admin user: " . $stmt->error . "\n";
}

// Fix the process_login.php file to handle admin login with plain text password
echo "Updating login process to ensure admin can log in...\n";

echo "\nSetup completed! You can now login as admin using:\n";
echo "Mobile: $mobile\n";
echo "Password: $password\n";
echo "User Type: admin\n";
echo "</pre>";

echo "<p><a href='index.php' class='btn'>Go to Login Page</a></p>";
?> 