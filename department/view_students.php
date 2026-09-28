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

// Get students from this department only
$students = [];
$stmt = $conn->prepare("SELECT * FROM students WHERE department = ? ORDER BY registration_no");
$stmt->bind_param("s", $department['department']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Handle search functionality
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $students = []; // Reset students array
    
    $search_param = '%' . $search_term . '%';
    $stmt = $conn->prepare("SELECT * FROM students 
                           WHERE department = ? 
                           AND (first_name LIKE ? OR last_name LIKE ? OR registration_no LIKE ? OR class_roll LIKE ? OR email LIKE ?)
                           ORDER BY registration_no");
    $stmt->bind_param("ssssss", $department['department'], $search_param, $search_param, $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Pagination
$students_per_page = 10;
$total_students = count($students);
$total_pages = ceil($total_students / $students_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $students_per_page;

$paginated_students = array_slice($students, $offset, $students_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - <?php echo htmlspecialchars($department['department']); ?> Department</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h1 {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .page-title p {
            color: var(--text-secondary);
        }

        .search-container {
            display: flex;
            align-items: center;
        }

        .search-container input {
            width: 250px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            outline: none;
        }

        .search-container button {
            padding: 10px 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            cursor: pointer;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
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

        .students-table td:last-child {
            text-align: center;
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
            color: var(--warning-color);
        }

        .student-count {
            margin-bottom: 20px;
            color: var(--text-secondary);
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 20px 0;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            color: var(--text-primary);
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--primary-color);
            color: white;
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

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-container {
                margin-top: 15px;
                width: 100%;
            }

            .search-container input {
                width: 100%;
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
                <a href="view_students.php" class="active">
                    <i class="fas fa-user-graduate"></i> View Students
                </a>
            </li>
            <li>
                <a href="approve_students.php">
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
            <div class="page-title">
                <h1>Department Students</h1>
                <p>View all students in <?php echo htmlspecialchars($department['department']); ?> department</p>
            </div>
            <div class="search-container">
                <form action="" method="GET">
                    <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="student-count">
            Total students: <strong><?php echo $total_students; ?></strong>
            <?php if (!empty($search_term)): ?>
                (Showing results for: <strong><?php echo htmlspecialchars($search_term); ?></strong> - <a href="view_students.php">Clear search</a>)
            <?php endif; ?>
        </div>

        <div class="card">
            <?php if (count($paginated_students) > 0): ?>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginated_students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['registration_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_roll']); ?></td>
                                <td><?php echo htmlspecialchars($student['session']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $student['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                            <li>
                                <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                                   class="<?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li>
                                <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-students">
                    <i class="fas fa-user-slash"></i>
                    <?php if (!empty($search_term)): ?>
                        <p>No students found matching your search criteria.</p>
                    <?php else: ?>
                        <p>No students found in this department.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 