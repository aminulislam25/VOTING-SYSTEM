<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "Checking database tables...\n\n";

// Get all tables in the database
$result = $conn->query("SHOW TABLES");
echo "Tables in database:\n";
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "\n";
}
echo "\n";

// Check specific tables
$tables = ['voters', 'candidates', 'positions'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table '$table' exists.\n";
        
        // Show primary key
        $result = $conn->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "  Primary key: " . $row['Column_name'] . "\n";
        } else {
            echo "  No primary key found!\n";
        }
        
        // Show columns
        echo "  Columns:\n";
        $result = $conn->query("SHOW COLUMNS FROM $table");
        while ($row = $result->fetch_assoc()) {
            echo "    - " . $row['Field'] . " (" . $row['Type'] . ")" . 
                 ($row['Key'] == 'PRI' ? " PRIMARY KEY" : "") . 
                 ($row['Key'] == 'MUL' ? " FOREIGN KEY" : "") . "\n";
        }
        echo "\n";
    } else {
        echo "Table '$table' does not exist!\n\n";
    }
}

echo "</pre>";
?> 