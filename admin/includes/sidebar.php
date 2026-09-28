<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
        <p>Voting System</p>
    </div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="manage_elections.php" class="nav-link <?php echo $current_page === 'manage_elections.php' ? 'active' : ''; ?>">
                <i class="fas fa-vote-yea"></i> Manage Elections
            </a>
        </li>
        <li class="nav-item">
            <a href="manage_candidates.php" class="nav-link <?php echo $current_page === 'manage_candidates.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Manage Candidates
            </a>
        </li>
        <li class="nav-item">
            <a href="manage_departments.php" class="nav-link <?php echo $current_page === 'manage_departments.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> Manage Departments
            </a>
        </li>
        <li class="nav-item">
            <a href="view_results.php" class="nav-link <?php echo $current_page === 'view_results.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> View Results
            </a>
        </li>
        <li class="nav-item">
            <a href="../logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div> 