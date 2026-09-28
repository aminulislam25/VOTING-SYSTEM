<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get student details
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Check if elections table exists
$result = $conn->query("SHOW TABLES LIKE 'elections'");
$elections_table_exists = $result->num_rows > 0;

// Get active and upcoming elections for the student's department
$active_elections = [];
$upcoming_elections = [];
if ($elections_table_exists) {
    // Get active elections
    $stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'active' AND NOW() BETWEEN start_date AND end_date ORDER BY start_date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $active_elections[] = $row;
    }

    // Get upcoming elections
    $stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'upcoming' ORDER BY start_date ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming_elections[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/student_styles.php'; ?>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>!</h1>
            <p>Here's what's happening in your department</p>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-vote-yea"></i>
                    Active Elections
                </h2>
                <?php if (empty($active_elections)): ?>
                    <p>No active elections at the moment.</p>
                <?php else: ?>
                    <?php foreach ($active_elections as $election): ?>
                        <div class="election-card">
                            <h3 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p class="election-description"><?php echo htmlspecialchars($election['description']); ?></p>
                            <p class="election-date">
                                <i class="far fa-calendar-alt"></i>
                                Start: <?php echo date('M d, Y', strtotime($election['start_date'])); ?><br>
                                End: <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                            </p>
                            <a href="cast_vote.php?election=<?php echo $election['election_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-vote-yea"></i> Cast Vote
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-calendar"></i>
                    Upcoming Elections
                </h2>
                <?php if (empty($upcoming_elections)): ?>
                    <p>No upcoming elections scheduled.</p>
                <?php else: ?>
                    <?php foreach ($upcoming_elections as $election): ?>
                        <div class="election-card">
                            <h3 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p class="election-description"><?php echo htmlspecialchars($election['description']); ?></p>
                            <p class="election-date">
                                <i class="far fa-calendar-alt"></i>
                                Starts: <?php echo date('M d, Y', strtotime($election['start_date'])); ?><br>
                                Ends: <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                            </p>
                            <div class="election-status">
                                <span class="badge badge-upcoming">Upcoming</span>
                                <p class="voting-info">Voting will be available when the election starts</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Your Information
                </h2>
                <p><strong>Registration No:</strong> <?php echo htmlspecialchars($student['registration_no']); ?></p>
                <p><strong>Class Roll:</strong> <?php echo htmlspecialchars($student['class_roll']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></p>
                <p><strong>Session:</strong> <?php echo htmlspecialchars($student['session']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>

            
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 