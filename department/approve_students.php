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

// Handle student approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_student'])) {
        $student_id = intval($_POST['student_id']);
        
        // Verify student belongs to this department
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND department = ?");
        $stmt->bind_param("is", $student_id, $department['department']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Approve the student
            $stmt = $conn->prepare("UPDATE students SET approved = 1, status = 'active' WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Student approved successfully.";
            } else {
                $_SESSION['error'] = "Error approving student: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Invalid student or not from your department.";
        }
    } elseif (isset($_POST['reject_student'])) {
        $student_id = intval($_POST['student_id']);
        
        // Verify student belongs to this department
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND department = ?");
        $stmt->bind_param("is", $student_id, $department['department']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Reject the student
            $stmt = $conn->prepare("UPDATE students SET status = 'inactive' WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Student rejected successfully.";
            } else {
                $_SESSION['error'] = "Error rejecting student: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Invalid student or not from your department.";
        }
    }
}

// Get pending students for this department
$stmt = $conn->prepare("SELECT * FROM students WHERE department = ? AND (approved = 0 OR status = 'pending') ORDER BY created_at DESC");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$pending_students = $stmt->get_result();

// Get all students for this department
$stmt = $conn->prepare("SELECT * FROM students WHERE department = ? ORDER BY approved DESC, status ASC, created_at DESC");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$all_students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Students - <?php echo htmlspecialchars($department['department']); ?> Department</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --danger-color: #ef233c;
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

        .tabs {
            display: flex;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 20px;
            background: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            cursor: pointer;
            margin-right: 5px;
            color: var(--text-secondary);
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab:hover {
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .alert-error {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table th,
        .students-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .students-table th {
            background-color: #f8f9fa;
            color: var(--text-primary);
            font-weight: 600;
        }

        .students-table tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }

        .status-inactive {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
        }

        .status-pending {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning-color);
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .btn-approve {
            background-color: var(--success-color);
            color: white;
        }

        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .student-count {
            margin-bottom: 15px;
            color: var(--text-secondary);
        }

        .no-students {
            text-align: center;
            padding: 30px;
            color: var(--text-secondary);
        }

        .no-students i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--warning-color);
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

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }

            .main-content {
                margin-left: 0;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
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
                <a href="view_students.php">
                    <i class="fas fa-user-graduate"></i> View Students
                </a>
            </li>
            <li>
                <a href="approve_students.php" class="active">
                    <i class="fas fa-user-check"></i> Approve Students
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
        <div class="page-header">
            <h1>Approve Students</h1>
            <p>Manage student approvals for <?php echo htmlspecialchars($department['department']); ?> department</p>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="pending">Pending Approvals</div>
            <div class="tab" data-tab="all">All Students</div>
        </div>

        <div id="pending" class="tab-content active">
            <div class="student-count">
                Pending students: <strong><?php echo $pending_students->num_rows; ?></strong>
            </div>

            <div class="card">
                <?php if ($pending_students->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Registration No.</th>
                                    <th>Name</th>
                                    <th>Class Roll</th>
                                    <th>Session</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $pending_students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['registration_no']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_roll']); ?></td>
                                        <td><?php echo htmlspecialchars($student['session']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                Pending
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <button type="submit" name="approve_student" class="btn btn-approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <button type="submit" name="reject_student" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this student?');">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-students">
                        <i class="fas fa-user-check"></i>
                        <p>No pending student approvals.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="all" class="tab-content">
            <div class="student-count">
                Total students: <strong><?php echo $all_students->num_rows; ?></strong>
            </div>

            <div class="card">
                <?php if ($all_students->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Registration No.</th>
                                    <th>Name</th>
                                    <th>Class Roll</th>
                                    <th>Session</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $all_students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['registration_no']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_roll']); ?></td>
                                        <td><?php echo htmlspecialchars($student['session']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                        <td>
                                            <?php if ($student['approved'] == 1 && $student['status'] == 'active'): ?>
                                                <span class="status-badge status-active">Active</span>
                                            <?php elseif ($student['status'] == 'inactive'): ?>
                                                <span class="status-badge status-inactive">Inactive</span>
                                            <?php else: ?>
                                                <span class="status-badge status-pending">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($student['approved'] != 1 || $student['status'] != 'active'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                        <button type="submit" name="approve_student" class="btn btn-approve">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($student['status'] != 'inactive'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                        <button type="submit" name="reject_student" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this student?');">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-students">
                        <i class="fas fa-user-graduate"></i>
                        <p>No students found in your department.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Deactivate all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                });
                
                // Activate clicked tab and content
                tab.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 