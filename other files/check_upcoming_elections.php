<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Checking upcoming elections...\n\n";

// Get all elections
$query = "SELECT election_id, title, department, status, start_date, end_date FROM elections ORDER BY status, start_date";
$result = $conn->query($query);

if (!$result) {
    die("Error querying elections: " . $conn->error);
}

echo "All elections:\n";
echo "=============\n\n";

while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['election_id'] . "\n";
    echo "Title: " . $row['title'] . "\n";
    echo "Department: " . $row['department'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Start Date: " . $row['start_date'] . "\n";
    echo "End Date: " . $row['end_date'] . "\n";
    echo "-------------------\n";
}

echo "\nDone!\n";
echo "</pre>";
?> 