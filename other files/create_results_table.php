<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Creating results table...\n\n";

// Drop existing results table if it exists
$dropTable = "DROP TABLE IF EXISTS results";
if ($conn->query($dropTable)) {
    echo "Dropped existing results table.\n";
} else {
    echo "Error dropping results table: " . $conn->error . "\n";
    exit;
}

// Create new results table
$createTable = "CREATE TABLE results (
    result_id INT(11) NOT NULL AUTO_INCREMENT,
    election_id INT(11) NOT NULL,
    position_id INT(11) NOT NULL,
    candidate_id INT(11) NOT NULL,
    total_votes INT(11) NOT NULL DEFAULT 0,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    rank_position INT(11) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (result_id),
    FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidate_result (election_id, position_id, candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($createTable)) {
    echo "Successfully created results table.\n";
} else {
    echo "Error creating results table: " . $conn->error . "\n";
}

// Add visibility control to elections table if not exists
$alterElectionsTable = "ALTER TABLE elections 
    ADD COLUMN IF NOT EXISTS results_visible BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS results_last_updated TIMESTAMP NULL DEFAULT NULL";

if ($conn->query($alterElectionsTable)) {
    echo "Successfully added results visibility control to elections table.\n";
} else {
    echo "Error modifying elections table: " . $conn->error . "\n";
}

// Create a stored procedure to update results
$createProcedure = "
DROP PROCEDURE IF EXISTS update_election_results;
CREATE PROCEDURE update_election_results(IN election_id_param INT)
BEGIN
    -- Start transaction
    START TRANSACTION;
    
    -- Delete existing results for this election
    DELETE FROM results WHERE election_id = election_id_param;
    
    -- Insert new results
    INSERT INTO results (election_id, position_id, candidate_id, total_votes, percentage)
    SELECT 
        v.election_id,
        c.position_id,
        c.candidate_id,
        COUNT(*) as total_votes,
        COUNT(*) * 100.0 / (
            SELECT COUNT(*) 
            FROM votes v2 
            WHERE v2.position_id = c.position_id 
            AND v2.election_id = election_id_param
        ) as percentage
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.candidate_id
    WHERE v.election_id = election_id_param
    GROUP BY v.election_id, c.position_id, c.candidate_id;
    
    -- Update rankings for each position
    UPDATE results r1
    JOIN (
        SELECT 
            result_id,
            @rank := IF(@current_position = position_id, @rank + 1,
                    IF(@current_position := position_id, 1, 1)) as rank_position
        FROM results
        CROSS JOIN (SELECT @rank := 0, @current_position := NULL) as vars
        WHERE election_id = election_id_param
        ORDER BY position_id, total_votes DESC
    ) r2 ON r1.result_id = r2.result_id
    SET r1.rank_position = r2.rank_position;
    
    -- Update the last updated timestamp
    UPDATE elections 
    SET results_last_updated = CURRENT_TIMESTAMP
    WHERE election_id = election_id_param;
    
    -- Commit transaction
    COMMIT;
END;";

if ($conn->multi_query($createProcedure)) {
    echo "Successfully created update_election_results procedure.\n";
    // Clear any remaining results
    while ($conn->more_results()) {
        $conn->next_result();
    }
} else {
    echo "Error creating procedure: " . $conn->error . "\n";
}

echo "\nDone!\n";
echo "</pre>";
?> 