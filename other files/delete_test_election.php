<?php
require_once 'config.php';

// Delete the test election
$stmt = $conn->prepare("DELETE FROM elections WHERE election_id = 7");
if ($stmt->execute()) {
    echo "Test election (KCSU ELECTION 2026) has been successfully deleted.";
} else {
    echo "Error deleting the election: " . $conn->error;
}
?> 