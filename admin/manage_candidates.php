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

// Fetch all candidates with their department names
$query = "SELECT c.*, d.department as department_name 
          FROM candidates c 
          LEFT JOIN departments d ON c.department = d.department 
          ORDER BY c.created_at DESC";
$result = $conn->query($query);
$candidates = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Manage Candidates</h1>
                <a href="add_candidate.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Candidate
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

            <div class="candidates-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="card candidate-card">
                        <div class="card-header">
                            <div class="candidate-image">
                                <?php if ($candidate['photo']): ?>
                                    <img src="../uploads/candidates/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></h3>
                            <p class="candidate-department"><?php echo htmlspecialchars($candidate['department']); ?></p>
                        </div>
                        <div class="card-body">
                            <p class="candidate-bio"><?php echo htmlspecialchars($candidate['manifesto'] ?? ''); ?></p>
                            <?php if (isset($candidate['last_sem_percentage'])): ?>
                                <p class="candidate-percentage">Last Semester: <?php echo htmlspecialchars($candidate['last_sem_percentage']); ?>%</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="edit_candidate.php?id=<?php echo $candidate['candidate_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn btn-danger" onclick="deleteCandidate(<?php echo $candidate['candidate_id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($candidates)): ?>
                    <div class="no-candidates">
                        <i class="fas fa-users"></i>
                        <p>No candidates found</p>
                        <a href="add_candidate.php" class="btn btn-primary">Add Your First Candidate</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function deleteCandidate(candidateId) {
        if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
            window.location.href = 'delete_candidate.php?id=' + candidateId;
        }
    }
    </script>
</body>
</html> 