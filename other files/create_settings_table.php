<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Creating settings table...\n\n";

// Create settings table
$createSettings = "CREATE TABLE IF NOT EXISTS settings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($createSettings)) {
    echo "Settings table created successfully.\n";
    
    // Insert default settings
    $defaultSettings = [
        ['election_status', 'inactive'],
        ['results_visibility', 'hidden']
    ];
    
    $insertStmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?)");
    
    foreach ($defaultSettings as $setting) {
        $insertStmt->bind_param("ss", $setting[0], $setting[1]);
        if ($insertStmt->execute()) {
            echo "Default setting '{$setting[0]}' inserted successfully.\n";
        } else {
            echo "Error inserting default setting '{$setting[0]}': " . $insertStmt->error . "\n";
        }
    }
    
} else {
    echo "Error creating settings table: " . $conn->error . "\n";
}

echo "\nSettings table setup complete.\n";
echo "</pre>";

echo "<p>You can now go back to the <a href='admin/dashboard.php'>admin dashboard</a>.</p>";
?> 