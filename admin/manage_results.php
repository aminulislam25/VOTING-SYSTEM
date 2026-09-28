<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle form submission for updating results
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_results']) && isset($_POST['election_id'])) {
        $election_id = intval($_POST['election_id']);
        
        // Call the stored procedure to update results
        $stmt = $conn->prepare("CALL update_election_results(?)");
        $stmt->bind_param("i", $election_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Results have been updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating results: " . $conn->error;
        }
        
        header('Location: manage_results.php');
        exit();
    }
    
    if (isset($_POST['toggle_visibility']) && isset($_POST['election_id'])) {
        $election_id = intval($_POST['election_id']);
        $visibility = isset($_POST['results_visible']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE elections SET results_visible = ? WHERE election_id = ?");
        $stmt->bind_param("ii", $visibility, $election_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Results visibility has been updated.";
        } else {
            $_SESSION['error'] = "Error updating visibility: " . $conn->error;
        }
        
        header('Location: manage_results.php');
        exit();
    }
}

// Get all elections with their results status
$elections = [];
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM results r WHERE r.election_id = e.election_id) as has_results
          FROM elections e 
          ORDER BY e.created_at DESC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $elections[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Election Results - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .results-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .election-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .election-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #edf2f7;
        }
        
        .election-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .election-status {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #c6f6d5;
            color: #2f855a;
        }
        
        .status-completed {
            background-color: #e9d8fd;
            color: #6b46c1;
        }
        
        .status-upcoming {
            background-color: #feebc8;
            color: #c05621;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3182ce;
        }
        
        .btn-secondary {
            background-color: #cbd5e0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background-color: #a0aec0;
        }
        
        .visibility-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: .4s;
            border-radius: 24px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #48bb78;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background-color: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        
        .results-info {
            margin-top: 10px;
            font-size: 0.875rem;
            color: #718096;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="results-section">
        <h1>Manage Election Results</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php foreach ($elections as $election): ?>
            <div class="election-card">
                <div class="election-header">
                    <div class="election-title">
                        <?php echo htmlspecialchars($election['title']); ?>
                    </div>
                    <div class="election-status">
                        <span class="status-badge status-<?php echo $election['status']; ?>">
                            <?php echo ucfirst($election['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="results-info">
                    <?php if ($election['has_results'] > 0): ?>
                        Last updated: <?php echo $election['results_last_updated'] ? date('M d, Y H:i:s', strtotime($election['results_last_updated'])) : 'Never'; ?>
                    <?php else: ?>
                        No results calculated yet
                    <?php endif; ?>
                </div>
                
                <div class="button-group">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="election_id" value="<?php echo $election['election_id']; ?>">
                        <button type="submit" name="update_results" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Update Results
                        </button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="election_id" value="<?php echo $election['election_id']; ?>">
                        <div class="visibility-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="results_visible" 
                                       <?php echo $election['results_visible'] ? 'checked' : ''; ?>
                                       onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Show Results to Students</span>
                            <input type="hidden" name="toggle_visibility" value="1">
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php include 'includes/admin_footer.php'; ?>
</body>
</html> 