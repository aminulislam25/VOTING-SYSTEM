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
$elections_query = "SELECT e.* 
                   FROM elections e 
                   ORDER BY e.created_at DESC";
$elections_result = $conn->query($elections_query);
$elections = [];

if ($elections_result) {
    while ($row = $elections_result->fetch_assoc()) {
        $elections[] = $row;
    }
}

// Get selected election results if an election is chosen
$selected_election = null;
$election_results = [];

if (isset($_GET['election_id']) && !empty($_GET['election_id'])) {
    $election_id = $_GET['election_id'];
    
    // Get election details
    $election_query = "SELECT * FROM elections WHERE election_id = ?";
    $stmt = $conn->prepare($election_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $selected_election = $result->fetch_assoc();
        
        // Get voting results
        $results_query = "SELECT c.*, COUNT(v.vote_id) as vote_count 
                         FROM candidates c 
                         LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
                         WHERE c.election_id = ? 
                         GROUP BY c.candidate_id 
                         ORDER BY vote_count DESC";
        $stmt = $conn->prepare($results_query);
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $election_results[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">View Election Results</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Election Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Select Election</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="election-form">
                        <div class="form-group">
                            <select name="election_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Choose an election</option>
                                <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo $election['election_id']; ?>" 
                                            <?php echo (isset($_GET['election_id']) && $_GET['election_id'] == $election['election_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($election['title']); ?> 
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selected_election): ?>
                <!-- Election Results -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="card-title">
                            Results: <?php echo htmlspecialchars($selected_election['title']); ?>
                        </h2>
                       
                    </div>
                    <div class="card-body">
                        <?php if (empty($election_results)): ?>
                            <p>No candidates found for this election.</p>
                        <?php else: ?>
                            <div class="results-grid">
                                <?php foreach ($election_results as $result): ?>
                                    <div class="result-card">
                                        <div class="candidate-info">
                                            <?php if ($result['photo']): ?>
                                                <img src="../uploads/candidates/<?php echo htmlspecialchars($result['photo']); ?>" 
                                                     alt="<?php echo htmlspecialchars($result['name']); ?>"
                                                     class="candidate-photo">
                                            <?php else: ?>
                                                <div class="no-photo">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <h3><?php echo htmlspecialchars($result['name']); ?></h3>
                                        </div>
                                        <div class="vote-info">
                                            <div class="vote-count">
                                                <span class="number"><?php echo $result['vote_count']; ?></span>
                                                <span class="label">Votes</span>
                                            </div>
                                            <?php
                                            // Calculate percentage if there are any votes
                                            $total_votes = array_sum(array_column($election_results, 'vote_count'));
                                            $percentage = $total_votes > 0 ? ($result['vote_count'] / $total_votes) * 100 : 0;
                                            ?>
                                            <div class="vote-percentage">
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <span class="percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="total-votes mt-4">
                                <h3>Total Votes: <?php echo $total_votes; ?></h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 