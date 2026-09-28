<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

echo "<pre>";  // For better formatting
echo "Starting admin user creation process...\n";

// Display MySQL version and connection info
echo "\nMySQL Connection Info:\n";
echo "MySQL Server Version: " . mysqli_get_server_info($conn) . "\n";
echo "MySQL Client Version: " . mysqli_get_client_info() . "\n";
echo "Current Database: " . ($conn->select_db($db_name) ? $db_name : "None") . "\n\n";

// Drop the admins table if it exists
$dropTable = mysqli_query($conn, "DROP TABLE IF EXISTS admins");
if (!$dropTable) {
    die("Error dropping table: " . mysqli_error($conn) . "\n");
}
echo "Dropped existing admins table (if any).\n\n";

// Create the admins table
echo "Creating admin table...\n";
$createTable = "CREATE TABLE admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(100) NOT NULL,
    college VARCHAR(100) NOT NULL,
    address TEXT,
    email VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    password VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createTable)) {
    die("Error creating table: " . mysqli_error($conn) . "\n");
}
echo "Admins table created successfully.\n\n";

// Create temporary admin user
echo "Creating temporary admin user...\n";
$adminName = "Temporary Admin";
$college = "Demo College";
$address = "Demo Address";
$email = "admin@example.com";
$userId = "admin123";
$password = "admin@123";

// First, check if admin exists
$checkAdmin = mysqli_query($conn, "SELECT * FROM admins WHERE mobile = '$userId'");
if (mysqli_num_rows($checkAdmin) > 0) {
    // Delete existing admin
    mysqli_query($conn, "DELETE FROM admins WHERE mobile = '$userId'");
    echo "Removed existing admin user.\n";
}

// Insert new admin
$insertAdmin = "INSERT INTO admins (admin_name, college, address, email, mobile, password) 
                VALUES ('$adminName', '$college', '$address', '$email', '$userId', '$password')";

if (mysqli_query($conn, $insertAdmin)) {
    echo "Temporary admin user created successfully.\n";
    echo "User ID: $userId\n";
    echo "Password: $password\n";
    
    // Verify the inserted data
    $verifyAdmin = mysqli_query($conn, "SELECT * FROM admins WHERE mobile = '$userId'");
    if ($verifyAdmin && mysqli_num_rows($verifyAdmin) > 0) {
        $admin = mysqli_fetch_assoc($verifyAdmin);
        echo "\nDebug info:\n";
        echo "Admin ID: " . $admin['id'] . "\n";
        echo "Admin Name: " . $admin['admin_name'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Mobile: " . $admin['mobile'] . "\n";
        echo "Stored Password: " . $admin['password'] . "\n";
        
        echo "\nAll admins in database:\n";
        $allAdmins = mysqli_query($conn, "SELECT * FROM admins");
        while ($row = mysqli_fetch_assoc($allAdmins)) {
            echo "- ID: {$row['id']}, Name: {$row['admin_name']}, Mobile: {$row['mobile']}\n";
        }
    }
} else {
    echo "Error creating admin user: " . mysqli_error($conn) . "\n";
}

echo "</pre>";

echo "<p>You can now go back to the <a href='index.php'>login page</a> and use these credentials to log in as admin.</p>";

// Test the database connection with a simple query
echo "<pre>\nTesting database connection:\n";
$testQuery = mysqli_query($conn, "SELECT 1");
if ($testQuery) {
    echo "Database connection is working properly.\n";
} else {
    echo "Database connection error: " . mysqli_error($conn) . "\n";
}

// Show table structure
echo "\nTable structure:\n";
$showCreate = mysqli_query($conn, "SHOW CREATE TABLE admins");
if ($showCreate) {
    $row = mysqli_fetch_array($showCreate);
    echo $row[1] . "\n";
}
echo "</pre>";
?> 