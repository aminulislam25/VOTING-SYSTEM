<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get student details if not already set
if (!isset($student)) {
    require_once '../config.php';
    $student_id = $_SESSION['student_id'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if (!$student) {
        $_SESSION['error'] = 'Student record not found.';
        header('Location: ../index.php');
        exit();
    }
}
?>

<div class="sidebar">
    <div class="student-info">
        <div class="student-photo">
            <i class="fas fa-user"></i>
        </div>
        <h2><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
        <p class="student-department"><?php echo htmlspecialchars($student['department']); ?></p>
        <p class="student-roll">Roll: <?php echo htmlspecialchars($student['class_roll']); ?></p>
    </div>
    
    <nav class="nav-links">
        <a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="cast_vote.php" class="<?php echo $current_page === 'cast_vote.php' ? 'active' : ''; ?>">
            <i class="fas fa-vote-yea"></i>
            <span>Cast Vote</span>
        </a>
        <a href="view_results.php" class="<?php echo $current_page === 'view_results.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>View Results</span>
        </a>
        <a href="profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
    
    <form action="../logout.php" method="POST" class="logout-form">
        <button type="submit" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </button>
    </form>
</div>

<div class="mobile-header">
    <button class="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <h1>Student Portal</h1>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
    });
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });
});
</script> 