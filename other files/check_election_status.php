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
$query = "SELECT election_id, title, department, status, start_date, end_date, 
          CASE 
            WHEN NOW() BETWEEN start_date AND end_date THEN 'Currently Active'
            WHEN NOW() < start_date THEN 'Not Started'
            WHEN NOW() > end_date THEN 'Ended'
          END as time_status
          FROM elections 
          ORDER BY status, start_date";

$result = $conn->query($query);

if (!$result) {
    die("Error querying elections: " . $conn->error);
}

echo "Total elections found: " . $result->num_rows . "\n\n";

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
        echo "Status: " . $election['status'] . "\n";
        echo "Time Status: " . $election['time_status'] . "\n";
        echo "Start Date: " . $election['start_date'] . "\n";
        echo "End Date: " . $election['end_date'] . "\n";
        echo "\n";
    }
    echo "\n";
}

// Check for any elections that might need status updates
$query = "SELECT election_id, title, status, start_date, end_date
          FROM elections
          WHERE (status = 'upcoming' AND NOW() >= start_date)
             OR (status = 'active' AND NOW() > end_date)";

$result = $conn->query($query);
if ($result->num_rows > 0) {
    echo "\nElections that might need status updates:\n";
    echo "-------------------------------------\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['election_id'] . "\n";
        echo "Title: " . $row['title'] . "\n";
        echo "Current Status: " . $row['status'] . "\n";
        echo "Start Date: " . $row['start_date'] . "\n";
        echo "End Date: " . $row['end_date'] . "\n";
        if ($row['status'] == 'upcoming' && strtotime($row['start_date']) <= time()) {
            echo "Suggestion: Should be changed to 'active'\n";
        }
        if ($row['status'] == 'active' && strtotime($row['end_date']) < time()) {
            echo "Suggestion: Should be changed to 'completed'\n";
        }
        echo "\n";
    }
}

echo "</pre>";
?> 