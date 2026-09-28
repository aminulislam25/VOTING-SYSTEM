<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Admin not found, clear session and redirect
    session_destroy();
    header('Location: ../index.php');
    exit();
}

$admin = $result->fetch_assoc();

// Initialize counts
$departments_count = 0;
$candidates_count = 0;
$votes_count = 0;

// Get total departments (with error handling)
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    if ($result) {
        $departments_count = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    error_log("Error counting departments: " . $e->getMessage());
}

// Get total candidates (with error handling)
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM candidates");
    if ($result) {
        $candidates_count = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    error_log("Error counting candidates: " . $e->getMessage());
}

// Get total votes (with error handling)
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM votes");
    if ($result) {
        $votes_count = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    error_log("Error counting votes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                <a href="../logout.php" class="btn btn-warning">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Departments</h3>
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $departments_count; ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Candidates</h3>
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $candidates_count; ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Votes</h3>
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="card-body">
                        <h2><?php echo $votes_count; ?></h2>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Get recent votes
                    $recent_votes = [];
                    try {
                        $query = "SELECT v.*, c.name as candidate_name, s.student_name as voter_name 
                                 FROM votes v 
                                 JOIN candidates c ON v.candidate_id = c.candidate_id 
                                 JOIN students s ON v.voter_id = s.student_id 
                                 ORDER BY v.voted_at DESC LIMIT 5";
                        $result = $conn->query($query);
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $recent_votes[] = $row;
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error getting recent votes: " . $e->getMessage());
                    }
                    ?>

                    <?php if (empty($recent_votes)): ?>
                    <?php else: ?>
                        <?php foreach ($recent_votes as $vote): ?>
                            <div class="activity-item">
                                <div class="activity-icon vote">
                                    <i class="fas fa-vote-yea"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">
                                        <?php echo htmlspecialchars($vote['voter_name']); ?> voted for 
                                        <?php echo htmlspecialchars($vote['candidate_name']); ?>
                                    </p>
                                    <p class="activity-time">
                                        <?php echo date('M j, Y g:i A', strtotime($vote['voted_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 