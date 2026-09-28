<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$success_message = '';
$error_message = '';

// Get all unlinked candidates (where election_id is NULL)
$candidates = [];
try {
    $query = "SELECT c.*, p.position_name, d.department as department_name 
              FROM candidates c 
              LEFT JOIN positions p ON c.position_id = p.position_id 
              LEFT JOIN departments d ON c.department = d.department 
              WHERE c.election_id IS NULL 
              ORDER BY c.created_at DESC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching candidates: " . $e->getMessage());
    $error_message = "Error fetching candidates. Please try again.";
}

// Get all active and upcoming elections
$elections = [];
try {
    $query = "SELECT * FROM elections WHERE status IN ('active', 'upcoming') ORDER BY created_at DESC";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $elections[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching elections: " . $e->getMessage());
    $error_message = "Error fetching elections. Please try again.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidate_id = $_POST['candidate_id'] ?? '';
    $election_id = $_POST['election_id'] ?? '';
    
    if (empty($candidate_id) || empty($election_id)) {
        $error_message = "Both candidate and election must be selected.";
    } else {
        try {
            // Update the candidate's election_id
            $stmt = $conn->prepare("UPDATE candidates SET election_id = ? WHERE candidate_id = ?");
            $stmt->bind_param("ii", $election_id, $candidate_id);
            
            if ($stmt->execute()) {
                $success_message = "Candidate successfully linked to the election!";
                
                // Refresh the candidates list
                $result = $conn->query("SELECT c.*, p.position_name, d.department as department_name 
                                      FROM candidates c 
                                      LEFT JOIN positions p ON c.position_id = p.position_id 
                                      LEFT JOIN departments d ON c.department = d.department 
                                      WHERE c.election_id IS NULL 
                                      ORDER BY c.created_at DESC");
                $candidates = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $candidates[] = $row;
                    }
                }
            } else {
                $error_message = "Error linking candidate to election. Please try again.";
            }
        } catch (Exception $e) {
            error_log("Error linking candidate: " . $e->getMessage());
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Candidates to Election - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
    <style>
        .link-candidate-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .candidate-list {
            margin-top: 2rem;
        }
        
        .candidate-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .candidate-info {
            flex-grow: 1;
        }
        
        .candidate-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .no-candidates {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Link Candidates to Election</h1>
                <a href="manage_candidates.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Candidates
                </a>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($elections)): ?>
            <div class="alert alert-warning">
                No active or upcoming elections found. Please create an election first.
            </div>
            <?php else: ?>
                <?php if (empty($candidates)): ?>
                <div class="no-candidates">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #4CAF50; margin-bottom: 1rem;"></i>
                    <h2>All Candidates are Linked!</h2>
                    <p>There are no unlinked candidates at the moment.</p>
                </div>
                <?php else: ?>
                <div class="candidate-list">
                    <?php foreach ($candidates as $candidate): ?>
                    <div class="candidate-card">
                        <div class="candidate-info">
                            <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                            <p>
                                <strong>Position:</strong> <?php echo htmlspecialchars($candidate['position_name']); ?><br>
                                <strong>Department:</strong> <?php echo htmlspecialchars($candidate['department_name']); ?>
                            </p>
                        </div>
                        <div class="candidate-actions">
                            <form method="POST" action="" class="link-form" style="display: flex; gap: 1rem; align-items: center;">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                <select name="election_id" required class="form-select">
                                    <option value="">Select Election</option>
                                    <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo $election['election_id']; ?>">
                                        <?php echo htmlspecialchars($election['title'] . ' (' . $election['status'] . ')'); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-link"></i> Link
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 