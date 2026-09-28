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

// Fetch all elections
$query = "SELECT * FROM elections ORDER BY created_at DESC";
$result = $conn->query($query);
$elections = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $elections[] = $row;
    }
}

// Fetch all candidates for linking to elections
$candidates_query = "SELECT * FROM candidates ORDER BY name";
$candidates_result = $conn->query($candidates_query);
$candidates = [];

if ($candidates_result) {
    while ($row = $candidates_result->fetch_assoc()) {
        $candidates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Manage Elections</h1>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Create New Election Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Create New Election</h2>
                </div>
                <div class="card-body">
                    <form action="process_election.php" method="POST" class="election-form">
                        <div class="form-group">
                            <label for="title">Election Title</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Election</button>
                    </form>
                </div>
            </div>

            <!-- Existing Elections Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Existing Elections</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($elections)): ?>
                        <p>No elections found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($elections as $election): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($election['title']); ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($election['start_date'])); ?></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($election['end_date'])); ?></td>
                                            <td>
                                                <button class="btn btn-danger btn-sm" onclick="deleteElection(<?php echo $election['election_id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Link Candidates to Elections Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Link Candidates to Elections</h2>
                </div>
                <div class="card-body">
                    <form action="link_candidate.php" method="POST" class="link-form">
                        <div class="form-group">
                            <label for="election_id">Select Election</label>
                            <select id="election_id" name="election_id" class="form-control" required>
                                <option value="">Choose an election</option>
                                <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo $election['election_id']; ?>">
                                        <?php echo htmlspecialchars($election['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="candidate_id">Select Candidate</label>
                            <select id="candidate_id" name="candidate_id" class="form-control" required>
                                <option value="">Choose a candidate</option>
                                <?php foreach ($candidates as $candidate): ?>
                                    <option value="<?php echo $candidate['candidate_id']; ?>">
                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Link Candidate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function deleteElection(electionId) {
        if (confirm('Are you sure you want to delete this election? This action cannot be undone.')) {
            window.location.href = 'delete_election.php?id=' + electionId;
        }
    }
    </script>
</body>
</html> 