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

// Get all elections for this department
$stmt = $conn->prepare("SELECT * FROM elections WHERE department_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$elections = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - <?php echo htmlspecialchars($department['department']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Include the same root and basic styles as dashboard.php */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --text-primary: #2b2d42;
            --text-secondary: #8d99ae;
            --bg-light: #f8f9fa;
            --border-radius: 12px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            display: flex;
        }

        /* Include sidebar styles from dashboard.php */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: white;
            padding: 20px;
            position: fixed;
            box-shadow: var(--card-shadow);
        }

        .department-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .department-info h2 {
            color: var(--text-primary);
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .department-info p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-links i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .election-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .election-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .election-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .election-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.2);
        }

        .election-body {
            padding: 20px;
        }

        .election-info {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .election-info p {
            color: var(--text-secondary);
            margin: 5px 0;
        }

        .candidates-list {
            list-style: none;
        }

        .candidate-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .candidate-item:last-child {
            border-bottom: none;
        }

        .candidate-info {
            flex: 1;
        }

        .candidate-name {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .vote-count {
            background: var(--success-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .progress-bar {
            height: 8px;
            background: #eee;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            background: var(--primary-color);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .logout-btn {
            margin-top: auto;
            width: 100%;
            padding: 12px;
            background: var(--warning-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
        <div class="sidebar">
            <div class="department-info">
            <h2><?php echo htmlspecialchars($department['department']); ?></h2>
            <p>HOD: <?php echo htmlspecialchars($department['hod_name']); ?></p>
                </div>
        
        <ul class="nav-links">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="manage_candidates.php">
                    <i class="fas fa-user-tie"></i> Manage Candidates
                    </a>
                </li>
            <li>
                <a href="view_students.php">
                    <i class="fas fa-user-graduate"></i> View Students
                    </a>
                </li>
            <li>
                <a href="approve_students.php">
                    <i class="fas fa-user-check"></i> Approve Students
                    </a>
                </li>
            <li>
                <a href="view_results.php" class="active">
                    <i class="fas fa-chart-bar"></i> View Results
                    </a>
                </li>
            <li>
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profile
                    </a>
                </li>
            </ul>

        <form action="../logout.php" method="POST" style="margin-top: auto;">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
        </div>

        <div class="main-content">
        <?php if ($elections->num_rows === 0): ?>
        <div class="election-card">
            <div class="election-body">
                <p style="text-align: center; color: var(--text-secondary);">No elections found for this department.</p>
            </div>
                </div>
        <?php else: ?>
            <?php while ($election = $elections->fetch_assoc()): ?>
            <div class="election-card">
                <div class="election-header">
                    <h3><?php echo htmlspecialchars($election['title']); ?></h3>
                    <span class="election-status">
                        <?php echo ucfirst(htmlspecialchars($election['status'])); ?>
                    </span>
                </div>
                <div class="election-body">
                    <div class="election-info">
                        <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($election['start_date'])); ?></p>
                        <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($election['end_date'])); ?></p>
                        <?php
                        // Get total votes for this election
                        $stmt = $conn->prepare("SELECT COUNT(*) as total_votes FROM votes WHERE election_id = ?");
                        $stmt->bind_param("i", $election['election_id']);
                        $stmt->execute();
                        $total_votes = $stmt->get_result()->fetch_assoc()['total_votes'];
                        ?>
                        <p><strong>Total Votes Cast:</strong> <?php echo $total_votes; ?></p>
            </div>

                    <div class="candidates-list">
                            <?php
                        // Get candidates and their vote counts
                        $stmt = $conn->prepare("
                            SELECT c.*, COUNT(v.vote_id) as vote_count 
                                                  FROM candidates c 
                                                  LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
                            WHERE c.election_id = ? 
                                                  GROUP BY c.candidate_id 
                            ORDER BY vote_count DESC
                        ");
                        $stmt->bind_param("i", $election['election_id']);
                            $stmt->execute();
                        $candidates = $stmt->get_result();
                        
                        while ($candidate = $candidates->fetch_assoc()):
                            $percentage = $total_votes > 0 ? ($candidate['vote_count'] / $total_votes) * 100 : 0;
                        ?>
                        <div class="candidate-item">
                            <div class="candidate-info">
                                                        <div class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                            <span class="vote-count"><?php echo $candidate['vote_count']; ?> votes</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?> 