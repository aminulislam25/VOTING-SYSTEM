<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
        }

        #sidebar {
            min-height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: all 0.3s;
            z-index: 1000;
        }

        #content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .nav-link {
            color: rgba(255,255,255,.8);
            padding: 1rem;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }

        .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }

        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .stats-card.primary { border-left: 4px solid var(--primary-color); }
        .stats-card.success { border-left: 4px solid var(--success-color); }
        .stats-card.info { border-left: 4px solid var(--info-color); }
        .stats-card.warning { border-left: 4px solid var(--warning-color); }

        .stats-title {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .stats-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-color);
        }

        /* Hamburger Menu Animation */
        .hamburger-menu {
            width: 30px;
            height: 25px;
            position: relative;
            cursor: pointer;
            margin-right: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger-menu span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: all 0.3s ease-in-out;
        }

        .hamburger-menu.active span:nth-child(1) {
            transform: translateY(11px) rotate(45deg);
        }

        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-menu.active span:nth-child(3) {
            transform: translateY(-11px) rotate(-45deg);
        }

        #sidebar.collapsed {
            margin-left: -250px;
        }

        #content.expanded {
            margin-left: 0;
        }

        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
            
            #content {
                margin-left: 0;
            }
            
            #content.active {
                margin-left: 250px;
            }
        }

        .nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .welcome-text {
            font-size: 16px;
            color: #333;
        }

        .welcome-text span {
            font-weight: 600;
            color: #1a73e8;
        }
    </style>
</head>
<body>
    <div class="nav-header">
        <div class="welcome-text">
            Welcome, <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="p-4">
            <h3 class="text-white mb-4">Voting System</h3>
            <ul class="nav flex-column">
                <?php if (getUserType() === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/admin/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/admin/candidates.php">
                            <i class="fas fa-user-tie me-2"></i>Candidates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/admin/voters.php">
                            <i class="fas fa-users me-2"></i>Voters
                        </a>
                    </li>
                <?php elseif (getUserType() === 'voter'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/voter/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/voter/vote.php">
                            <i class="fas fa-vote-yea me-2"></i>Vote
                        </a>
                    </li>
                <?php elseif (getUserType() === 'department'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/department/dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/vote/department/candidates.php">
                            <i class="fas fa-user-tie me-2"></i>Candidates
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/vote/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <div id="content">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="hamburger-menu d-md-none">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <?php if (isLoggedIn()): ?>
                <div class="ms-auto">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <div class="container-fluid">
            <div class="content">
                <!-- Content will be injected here -->
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const hamburger = $('.hamburger-menu');
            const sidebar = $('#sidebar');
            const content = $('#content');
            
            // Toggle sidebar
            hamburger.on('click', function(e) {
                e.stopPropagation();
                $(this).toggleClass('active');
                sidebar.toggleClass('collapsed');
                content.toggleClass('expanded');
            });
            
            // Close sidebar when clicking outside
            $(document).on('click', function(e) {
                if (!sidebar.is(e.target) && 
                    !hamburger.is(e.target) && 
                    sidebar.has(e.target).length === 0 && 
                    hamburger.has(e.target).length === 0 && 
                    window.innerWidth < 769) {
                    
                    hamburger.removeClass('active');
                    sidebar.addClass('collapsed');
                    content.addClass('expanded');
                }
            });
            
            // Handle window resize
            $(window).resize(function() {
                if (window.innerWidth >= 769) {
                    hamburger.removeClass('active');
                    sidebar.removeClass('collapsed');
                    content.removeClass('expanded');
                }
            });
        });
    </script>
</body>
</html> 