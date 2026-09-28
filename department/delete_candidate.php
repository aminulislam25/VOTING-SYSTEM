<?php
session_start();
require_once '../config.php';

// Check if department is logged in
if (!isset($_SESSION['department_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get department details
$department_id = $_SESSION['department_id'];
$stmt = $conn->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$department = $stmt->get_result()->fetch_assoc();

// Check if candidate ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No candidate specified for deletion.";
    header('Location: manage_candidates.php');
    exit();
}

$candidate_id = $_GET['id'];

// Verify the candidate belongs to this department
$stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = ? AND department = ?");
$stmt->bind_param("is", $candidate_id, $department['department']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Candidate not found or you don't have permission to delete this candidate.";
    header('Location: manage_candidates.php');
    exit();
}

$candidate = $result->fetch_assoc();

// Handle deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    // Delete the candidate
    $stmt = $conn->prepare("DELETE FROM candidates WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    
    if ($stmt->execute()) {
        // Delete candidate photo if exists
        if (!empty($candidate['photo']) && file_exists($candidate['photo'])) {
            unlink($candidate['photo']);
        }
        
        $_SESSION['success_message'] = "Candidate deleted successfully.";
        header('Location: manage_candidates.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error deleting candidate: " . $conn->error;
        header('Location: manage_candidates.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Candidate - Department Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --danger-color: #f44336;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--light-gray);
            color: var(--text-color);
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: white;
            padding: 2rem;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .department-info {
            text-align: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .department-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
        }

        .department-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .department-details {
            font-size: 0.9rem;
            color: #666;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-link i {
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .back-btn {
            background: var(--secondary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: #1976d2;
        }

        .back-btn i {
            margin-right: 0.5rem;
        }

        /* Delete Confirmation */
        .delete-confirmation {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .warning-icon {
            font-size: 4rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        .confirmation-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .confirmation-message {
            margin-bottom: 2rem;
            color: #666;
        }

        .candidate-info {
            background: var(--light-gray);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .candidate-info p {
            margin-bottom: 0.5rem;
        }

        .candidate-info strong {
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .cancel-btn {
            background: var(--light-gray);
            color: var(--text-color);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
        }

        .cancel-btn:hover {
            background: #e0e0e0;
        }

        .delete-btn {
            background: var(--danger-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
        }

        .delete-btn:hover {
            background: #d32f2f;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="department-info">
                <div class="department-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="department-name"><?php echo htmlspecialchars($department['department']); ?></div>
                <div class="department-details">
                    <?php echo htmlspecialchars($department['hod_mobile']); ?>
                </div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_voters.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        View Voters
                    </a>
                </li>
                <li class="nav-item">
                    <a href="view_results.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        View Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-edit"></i>
                        Update Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Delete Candidate</h1>
                <a href="manage_candidates.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Candidates
                </a>
            </div>

            <!-- Delete Confirmation -->
            <div class="delete-confirmation">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="confirmation-title">Confirm Deletion</h2>
                <p class="confirmation-message">Are you sure you want to delete this candidate? This action cannot be undone.</p>
                
                <div class="candidate-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($candidate['name']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($candidate['department']); ?></p>
                </div>
                
                <form action="" method="POST">
                    <input type="hidden" name="confirm_delete" value="yes">
                    <div class="action-buttons">
                        <a href="manage_candidates.php" class="cancel-btn">Cancel</a>
                        <button type="submit" class="delete-btn">Delete Candidate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 