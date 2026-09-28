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

if (!$student) {
    $_SESSION['error'] = 'Student record not found.';
    header('Location: ../index.php');
    exit();
}

// Get elections with visible results
$elections = [];
$query = "SELECT e.*, 
          (SELECT COUNT(vote_id) FROM votes v WHERE v.election_id = e.election_id) as total_votes
          FROM elections e 
          WHERE e.status = 'active'
          ORDER BY e.end_date DESC";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $elections[] = $row;
}

// Get results for a specific election if requested
$selected_election = null;
$election_results = [];
if (isset($_GET['election']) && is_numeric($_GET['election'])) {
    $election_id = intval($_GET['election']);
    
    // Get election details
    $stmt = $conn->prepare("SELECT * FROM elections WHERE election_id = ? AND status = 'active'");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $selected_election = $stmt->get_result()->fetch_assoc();
    
    if ($selected_election) {
        // Get results grouped by position
        $query = "SELECT p.position_id, p.position_name,
                        c.candidate_id, c.name as candidate_name, c.photo,
                        COUNT(v.vote_id) as vote_count,
                        (SELECT COUNT(vote_id) 
                         FROM votes 
                         WHERE election_id = ? AND position_id = p.position_id) as position_total_votes
                 FROM positions p
                 LEFT JOIN candidates c ON c.position_id = p.position_id AND c.election_id = ?
                 LEFT JOIN votes v ON v.candidate_id = c.candidate_id AND v.election_id = ?
                 WHERE EXISTS (SELECT 1 FROM candidates WHERE election_id = ? AND position_id = p.position_id)
                 GROUP BY p.position_id, p.position_name, c.candidate_id, c.name, c.photo
                 ORDER BY p.position_name, vote_count DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $election_id, $election_id, $election_id, $election_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $position = $row['position_name'];
            if (!isset($election_results[$position])) {
                $election_results[$position] = [
                    'candidates' => [],
                    'total_votes' => $row['position_total_votes']
                ];
            }
            if ($row['candidate_id']) { // Only add if there's a candidate
                $election_results[$position]['candidates'][] = $row;
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
    <title>View Results - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'includes/student_styles.php'; ?>
    <style>
        .results-container {
            padding: 20px;
        }
        .position-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .position-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .position-name {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
        }
        .total-votes {
            color: #666;
            font-size: 0.9em;
        }
        .candidate-list {
            display: grid;
            gap: 15px;
        }
        .candidate-card {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 6px;
            background: #f8f9fa;
        }
        .candidate-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .candidate-info {
            flex-grow: 1;
        }
        .candidate-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .vote-count {
            color: #666;
            font-size: 0.9em;
        }
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #4361ee;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .no-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .no-photo i {
            color: #adb5bd;
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Election Results</h1>
        </div>

        <?php if (empty($elections)): ?>
            <div class="alert alert-info">
                No active elections found.
            </div>
        <?php else: ?>
            <div class="election-selector">
                <form method="GET" action="" class="mb-4">
                    <select name="election" class="form-control" onchange="this.form.submit()">
                        <option value="">Select an Election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo $election['election_id']; ?>" 
                                    <?php echo (isset($_GET['election']) && $_GET['election'] == $election['election_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['title']); ?>
                                (Total Votes: <?php echo $election['total_votes']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($selected_election): ?>
                <div class="results-container">
                    <?php if (empty($election_results)): ?>
                        <div class="alert alert-info">
                            No results found for this election.
                        </div>
                    <?php else: ?>
                        <?php foreach ($election_results as $position => $data): ?>
                            <div class="position-card">
                                <div class="position-header">
                                    <span class="position-name"><?php echo htmlspecialchars($position); ?></span>
                                    <span class="total-votes">
                                        Total Votes: <?php echo $data['total_votes']; ?>
                                    </span>
                                </div>
                                <div class="candidate-list">
                                    <?php foreach ($data['candidates'] as $candidate): ?>
                                        <div class="candidate-card">
                                            <?php if ($candidate['photo']): ?>
                                                <img src="../uploads/candidates/<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                                     alt="<?php echo htmlspecialchars($candidate['candidate_name']); ?>"
                                                     class="candidate-photo">
                                            <?php else: ?>
                                                <div class="no-photo">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="candidate-info">
                                                <div class="candidate-name">
                                                    <?php echo htmlspecialchars($candidate['candidate_name']); ?>
                                                </div>
                                                <div class="vote-count">
                                                    <?php 
                                                    $votes = $candidate['vote_count'];
                                                    $percentage = $data['total_votes'] > 0 ? 
                                                        ($votes / $data['total_votes'] * 100) : 0;
                                                    echo "$votes votes (" . number_format($percentage, 1) . "%)";
                                                    ?>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html> 