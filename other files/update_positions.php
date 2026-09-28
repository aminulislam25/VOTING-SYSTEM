<?php
require_once 'config.php';

// First, delete all existing positions
$conn->query("DELETE FROM positions");

// Insert new positions
$positions = [
    ['President', 'Head of the student body'],
    ['Vice President', 'Assists the President in all matters'],
    ['General Secretary', 'Handles administrative duties'],
    ['Asst General Secretary', 'Assists the General Secretary'],
    ['Cultural Secretary', 'Manages cultural activities'],
    ['Asst Cultural Secretary', 'Assists the Cultural Secretary']
];

$stmt = $conn->prepare("INSERT INTO positions (position_name, description) VALUES (?, ?)");

foreach ($positions as $position) {
    $stmt->bind_param("ss", $position[0], $position[1]);
    if ($stmt->execute()) {
        echo "Added position: " . $position[0] . "\n";
    } else {
        echo "Error adding position " . $position[0] . ": " . $conn->error . "\n";
    }
}

echo "\nAll positions have been updated successfully!";
?> 