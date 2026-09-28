<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Fixing all database issues...\n\n";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to check if a column exists in a table
function columnExists($conn, $tableName, $columnName) {
    $result = $conn->query("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
    return $result->num_rows > 0;
}

// 1. Check and create settings table
if (!tableExists($conn, 'settings')) {
    echo "Creating settings table...\n";
    
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
} else {
    echo "Settings table already exists.\n";
}

// 2. Check and fix voters table
if (tableExists($conn, 'voters')) {
    // Check if has_voted column exists
    if (!columnExists($conn, 'voters', 'has_voted')) {
        echo "Adding has_voted column to voters table...\n";
        
        $alterTable = "ALTER TABLE voters ADD COLUMN has_voted BOOLEAN DEFAULT FALSE";
        
        if ($conn->query($alterTable)) {
            echo "has_voted column added successfully.\n";
        } else {
            echo "Error adding has_voted column: " . $conn->error . "\n";
        }
    } else {
        echo "has_voted column already exists in voters table.\n";
    }
    
    // Check if approved column exists
    if (!columnExists($conn, 'voters', 'approved')) {
        echo "Adding approved column to voters table...\n";
        
        $alterTable = "ALTER TABLE voters ADD COLUMN approved TINYINT(1) DEFAULT 0";
        
        if ($conn->query($alterTable)) {
            echo "approved column added successfully.\n";
        } else {
            echo "Error adding approved column: " . $conn->error . "\n";
        }
    } else {
        echo "approved column already exists in voters table.\n";
    }
} else {
    echo "Voters table doesn't exist. Please run setup_database.php first.\n";
}

// 3. Check and create admins table
if (!tableExists($conn, 'admins')) {
    echo "Creating admins table...\n";
    
    $createAdmins = "CREATE TABLE IF NOT EXISTS admins (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createAdmins)) {
        echo "Admins table created successfully.\n";
        
        // Create a default admin account
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@example.com';
        
        $insertAdmin = $conn->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $insertAdmin->bind_param("sss", $username, $password, $email);
        
        if ($insertAdmin->execute()) {
            echo "Default admin account created successfully.\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
        } else {
            echo "Error creating default admin account: " . $insertAdmin->error . "\n";
        }
    } else {
        echo "Error creating admins table: " . $conn->error . "\n";
    }
} else {
    echo "Admins table already exists.\n";
}

// 4. Check and create positions table
if (!tableExists($conn, 'positions')) {
    echo "Creating positions table...\n";
    
    $createPositions = "CREATE TABLE IF NOT EXISTS positions (
        position_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        position_name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createPositions)) {
        echo "Positions table created successfully.\n";
    } else {
        echo "Error creating positions table: " . $conn->error . "\n";
    }
} else {
    echo "Positions table already exists.\n";
}

// 5. Check and create candidates table
if (!tableExists($conn, 'candidates')) {
    echo "Creating candidates table...\n";
    
    $createCandidates = "CREATE TABLE IF NOT EXISTS candidates (
        candidate_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        position_id INT(11) NOT NULL,
        department VARCHAR(100) NOT NULL,
        photo VARCHAR(255),
        bio TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($createCandidates)) {
        echo "Candidates table created successfully.\n";
    } else {
        echo "Error creating candidates table: " . $conn->error . "\n";
    }
} else {
    echo "Candidates table already exists.\n";
}

// 6. Check and create votes table
if (!tableExists($conn, 'votes')) {
    echo "Creating votes table...\n";
    
    $createVotes = "CREATE TABLE IF NOT EXISTS votes (
        vote_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        voter_id INT(11) NOT NULL,
        candidate_id INT(11) NOT NULL,
        position_id INT(11) NOT NULL,
        voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createVotes)) {
        echo "Votes table created successfully.\n";
    } else {
        echo "Error creating votes table: " . $conn->error . "\n";
    }
} else {
    echo "Votes table already exists.\n";
}

echo "\nAll database issues have been fixed.\n";
echo "</pre>";

echo "<p>You can now go back to the <a href='admin/dashboard.php'>admin dashboard</a>.</p>";
?> 