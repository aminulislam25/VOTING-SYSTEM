<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get success message if any
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Get error message if any
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);

// Fetch all departments
$query = "SELECT * FROM departments ORDER BY created_at DESC";
$result = $conn->query($query);
$departments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Manage Departments</h1>
                <a href="add_department.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Department
                </a>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            <div class="departments-grid">
                <?php foreach ($departments as $department): ?>
                    <div class="card department-card">
                        <div class="card-header">
                            <h3 class="department-name"><?php echo htmlspecialchars($department['department']); ?></h3>
                            <div class="department-status <?php echo $department['approved'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $department['approved'] ? 'Approved' : 'Pending'; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><strong>HOD Name:</strong> <?php echo htmlspecialchars($department['hod_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($department['hod_email']); ?></p>
                            <p><strong>Mobile:</strong> <?php echo htmlspecialchars($department['hod_mobile']); ?></p>
                        </div>
                        <div class="card-footer">
                            <?php if (!$department['approved']): ?>
                                <form action="process_department_status.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="department_id" value="<?php echo $department['department_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="process_department_status.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="department_id" value="<?php echo $department['department_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-danger" onclick="deleteDepartment(<?php echo $department['department_id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($departments)): ?>
                    <div class="no-departments">
                        <i class="fas fa-building"></i>
                        <p>No departments found</p>
                        <a href="add_department.php" class="btn btn-primary">Add Your First Department</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function deleteDepartment(departmentId) {
        if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
            window.location.href = `delete_department.php?id=${departmentId}`;
        }
    }
    </script>
</body>
</html> 