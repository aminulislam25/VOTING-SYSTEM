<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student details
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    $_SESSION['error'] = 'Student record not found. Please contact the administrator.';
    header('Location: ../index.php');
    exit();
}

// Get elections
$active_elections = [];
$upcoming_elections = [];
$stmt = $conn->prepare("SELECT *, 
    CASE 
        WHEN NOW() BETWEEN start_date AND end_date THEN 'Currently Active'
        WHEN NOW() < start_date THEN 'Upcoming'
        WHEN NOW() > end_date THEN 'Ended'
    END as time_status
    FROM elections 
    WHERE status = 'active' 
    ORDER BY start_date");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['time_status'] === 'Currently Active') {
        $active_elections[] = $row;
    } elseif ($row['time_status'] === 'Upcoming') {
        $upcoming_elections[] = $row;
    }
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = intval($_POST['candidate_id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get candidate details and position
        $stmt = $conn->prepare("SELECT c.position_id, c.election_id FROM candidates c WHERE c.candidate_id = ?");
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid candidate selected");
        }
        
        $candidate = $result->fetch_assoc();
        $position_id = $candidate['position_id'];
        $election_id = $candidate['election_id'];
        
        // Check if student has already voted for this position in this election
        $stmt = $conn->prepare("SELECT * FROM votes WHERE student_id = ? AND position_id = ? AND election_id = ?");
        $stmt->bind_param("iii", $student_id, $position_id, $election_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("You have already voted for this position in this election");
        }
        
        // Insert vote
        $stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id, position_id, election_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $student_id, $candidate_id, $position_id, $election_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error recording vote");
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = 'Your vote has been recorded successfully!';
        header('Location: cast_vote.php');
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header('Location: cast_vote.php');
        exit();
    }
}

// Get success/error messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Vote - Student Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/student_styles.php'; ?>
    <style>
        .voting-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .election-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .election-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #edf2f7;
        }
        
        .election-title {
            color: #2b2d42;
            font-size: 1.5rem;
            margin: 0;
        }
        
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .candidate-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
        }
        
        .candidate-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            overflow: hidden;
            background: #e2e8f0;
        }
        
        .candidate-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .candidate-photo i {
            font-size: 60px;
            line-height: 120px;
            color: #94a3b8;
        }
        
        .candidate-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .candidate-position {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .vote-btn {
            background: #4361ee;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .vote-btn:hover {
            background: #3730a3;
        }
        
        .voted-badge {
            background: #10b981;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .section-title {
            color: var(--text-primary);
            font-size: 1.5rem;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #edf2f7;
        }
        
        .election-info {
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .election-message {
            color: #64748b;
            font-style: italic;
            margin-top: 10px;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: #10b981;
            color: white;
        }
        
        .badge-info {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Cast Your Vote</h1>
                <p>Select your preferred candidates for each position</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="voting-section">
                <?php if (empty($active_elections) && empty($upcoming_elections)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No elections available at the moment.
                    </div>
                <?php else: ?>
                    <?php if (!empty($active_elections)): ?>
                        <h2 class="section-title">Active Elections</h2>
                        <?php foreach ($active_elections as $election): ?>
                            <div class="election-card">
                                <div class="election-header">
                                    <h2 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h2>
                                    <span class="badge badge-success">Active</span>
                                </div>
                                
                                <?php
                                // Get positions and candidates for this election
                                $stmt = $conn->prepare("
                                    SELECT DISTINCT p.position_id, p.position_name 
                                    FROM positions p 
                                    JOIN candidates c ON p.position_id = c.position_id 
                                    WHERE c.election_id = ?
                                    ORDER BY p.position_name
                                ");
                                $stmt->bind_param("i", $election['election_id']);
                                $stmt->execute();
                                $positions = $stmt->get_result();

                                // Get positions student has already voted for in this election
                                $stmt = $conn->prepare("SELECT position_id FROM votes WHERE student_id = ? AND election_id = ?");
                                $stmt->bind_param("ii", $student_id, $election['election_id']);
                                $stmt->execute();
                                $voted_result = $stmt->get_result();
                                $voted_positions = [];
                                while ($row = $voted_result->fetch_assoc()) {
                                    $voted_positions[] = $row['position_id'];
                                }
                                ?>

                                <?php while ($position = $positions->fetch_assoc()): ?>
                                    <div class="position-section" style="margin-bottom: 30px; padding: 20px; background: #f8fafc; border-radius: 10px;">
                                        <h3 class="position-title" style="color: #2b2d42; font-size: 1.3rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #edf2f7;">
                                            <?php echo htmlspecialchars($position['position_name']); ?>
                                            <?php if (in_array($position['position_id'], $voted_positions)): ?>
                                                <span class="badge badge-success" style="margin-left: 10px;">
                                                    <i class="fas fa-check"></i> Voted
                                                </span>
                                            <?php endif; ?>
                                        </h3>

                                        <?php
                                        // Get candidates for this position
                                        $stmt = $conn->prepare("
                                            SELECT c.* 
                                            FROM candidates c 
                                            WHERE c.election_id = ? AND c.position_id = ?
                                        ");
                                        $stmt->bind_param("ii", $election['election_id'], $position['position_id']);
                                        $stmt->execute();
                                        $candidates = $stmt->get_result();
                                        ?>

                                        <div class="candidates-grid">
                                            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                                                <div class="candidate-card">
                                                    <div class="candidate-photo">
                                                        <?php if ($candidate['photo']): ?>
                                                            <img src="../uploads/candidates/<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                        <?php else: ?>
                                                            <i class="fas fa-user"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="candidate-name">
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                    </div>
                                                    
                                                    <?php if (!in_array($position['position_id'], $voted_positions)): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                                            <button type="submit" class="vote-btn" onclick="return confirm('Are you sure you want to vote for <?php echo htmlspecialchars($candidate['name']); ?> for <?php echo htmlspecialchars($position['position_name']); ?>?')">
                                                                <i class="fas fa-vote-yea"></i> Vote
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($upcoming_elections)): ?>
                        <h2 class="section-title">Upcoming Elections</h2>
                        <?php foreach ($upcoming_elections as $election): ?>
                            <div class="election-card">
                                <div class="election-header">
                                    <h2 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h2>
                                    <span class="badge badge-info">Upcoming</span>
                                </div>
                                <div class="election-info">
                                    <p><strong>Start Date:</strong> <?php echo date('F j, Y g:i A', strtotime($election['start_date'])); ?></p>
                                    <p><strong>End Date:</strong> <?php echo date('F j, Y g:i A', strtotime($election['end_date'])); ?></p>
                                    <p class="election-message">This election will be available for voting when it starts.</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
