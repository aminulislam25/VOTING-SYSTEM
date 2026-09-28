<?php
require_once 'config.php';

echo "<pre>";
echo "Checking departments table structure:\n\n";

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