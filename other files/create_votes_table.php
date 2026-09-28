<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "Creating votes table...\n\n";

// Drop existing votes table if it exists
$dropTable = "DROP TABLE IF EXISTS votes";
if ($conn->query($dropTable)) {
    echo "Dropped existing votes table.\n";
} else {
    echo "Error dropping votes table: " . $conn->error . "\n";
    exit;
}

// Create new votes table with correct structure
$createTable = "CREATE TABLE votes (
    vote_id INT(11) NOT NULL AUTO_INCREMENT,
    election_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    candidate_id INT(11) NOT NULL,
    position_id INT(11) NOT NULL,
    voted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (vote_id),
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (election_id, student_id, position_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($createTable)) {
    echo "Successfully created votes table with correct structure.\n";
} else {
    echo "Error creating votes table: " . $conn->error . "\n";
}

echo "\nDone!\n";
echo "</pre>";
?> 