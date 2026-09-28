<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Checking elections table...\n\n";

// Check if elections table exists
$result = $conn->query("SHOW TABLES LIKE 'elections'");
if ($result->num_rows == 0) {
    die("Elections table does not exist!");
}

// Get all elections
$query = "SELECT * FROM elections ORDER BY status, start_date";
$result = $conn->query($query);

if (!$result) {
    die("Error querying elections: " . $conn->error);
}

echo "Total elections found: " . $result->num_rows . "\n\n";
echo "Elections by status:\n";
echo "==================\n\n";

$elections_by_status = [];
while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    if (!isset($elections_by_status[$status])) {
        $elections_by_status[$status] = [];
    }
    $elections_by_status[$status][] = $row;
}

foreach ($elections_by_status as $status => $elections) {
    echo strtoupper($status) . " Elections (" . count($elections) . "):\n";
    echo "--------------------\n";
    foreach ($elections as $election) {
        echo "ID: " . $election['election_id'] . "\n";
        echo "Title: " . $election['title'] . "\n";
        echo "Department: " . $election['department'] . "\n";
        echo "Start Date: " . $election['start_date'] . "\n";
        echo "End Date: " . $election['end_date'] . "\n";
        echo "Status: " . $election['status'] . "\n";
        echo "\n";
    }
}

echo "</pre>";
?> 