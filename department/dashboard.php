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

// Get department statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM candidates WHERE department = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$total_candidates = $stmt->get_result()->fetch_assoc()['total'];

// Get total students in this department
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE department = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total'];

// Get pending student approvals
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE department = ? AND (approved = 0 OR status = 'pending')");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$pending_approvals = $stmt->get_result()->fetch_assoc()['total'];

// Get pending students count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE department = ? AND status = 'pending'");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$pending_students = $stmt->get_result()->fetch_assoc()['total'];

// Check if votes table exists
$total_votes = 0;
$result = $conn->query("SHOW TABLES LIKE 'votes'");
if ($result->num_rows > 0) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM votes v 
                              JOIN candidates c ON v.candidate_id = c.candidate_id 
                              WHERE c.department = ?");
        $stmt->bind_param("s", $department['department']);
        $stmt->execute();
        $total_votes = $stmt->get_result()->fetch_assoc()['total'];
    } catch (Exception $e) {
        // If there's an error, keep total_votes as 0
        error_log("Error counting votes: " . $e->getMessage());
    }
}

// Get recent candidates
$stmt = $conn->prepare("SELECT * FROM candidates 
                       WHERE department = ? 
                       ORDER BY created_at DESC 
                       LIMIT 5");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$recent_candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['department']); ?> Dashboard - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        .page-header {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }

        .page-header h1 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .page-header p {
            color: var(--text-secondary);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .card h3 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .card p {
            color: var(--text-secondary);
            font-size: 1.5rem;
            font-weight: 600;
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

        .card-link {
            display: inline-block;
            margin-top: 10px;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .card-link:hover {
            text-decoration: underline;
        }

        .notification-bar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .notification-header {
            background: var(--primary-color);
            color: white;
            padding: 15px 20px;
        }

        .notification-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification-list {
            padding: 0;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item h4 {
            margin: 0 0 5px 0;
            color: var(--text-primary);
        }

        .notification-item p {
            margin: 0 0 5px 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .notification-item small {
            color: var(--text-secondary);
            font-size: 0.8rem;
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
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="view_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate"></i> View Students
                    </a>
                </li>
            <li>
                <a href="approve_students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'approve_students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-check"></i> Approve Students
                    </a>
                </li>
            <li>
            <li>
                <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
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
        <div class="page-header">
            <h1>Welcome, <?php echo htmlspecialchars($department['hod_name']); ?>!</h1>
            <p><?php echo htmlspecialchars($department['department']); ?> Department Dashboard</p>
        </div>

        <!-- Add Notification Bar -->
        <div class="notification-bar">
            <?php
            // Get notifications for this department
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE 
                (recipient_type = 'all' OR 
                (recipient_type = 'department' AND department_id = ?) OR
                (recipient_type = 'department' AND department_id IS NULL))
                ORDER BY created_at DESC LIMIT 5");
            $stmt->bind_param("i", $department_id);
            $stmt->execute();
            $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (count($notifications) > 0):
            ?>
            <div class="notification-header">
                <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
            </div>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small>Posted: <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
            <div class="card">
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
                <a href="view_students.php" class="card-link">View Students <i class="fas fa-arrow-right"></i></a>
                    </div>
            <div class="card">
                <h3>Pending Student Approvals</h3>
                <p><?php echo $pending_approvals; ?></p>
                <?php if ($pending_approvals > 0): ?>
                <a href="approve_students.php" class="card-link">Approve Students <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 