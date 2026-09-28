<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get voter ID from the voters table using student's registration number
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT v.id as voter_id, s.registration_no, s.name as student_name, 
                              s.department, s.roll_no, s.mobile, s.password 
                       FROM students s 
                       LEFT JOIN voters v ON v.registration_no = s.registration_no 
                       WHERE s.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Student record not found.';
    header('Location: dashboard.php');
    exit();
}

$student = $result->fetch_assoc();

// If no voter record exists, create one
if (!$student['voter_id']) {
    // Insert into voters table
    $stmt = $conn->prepare("INSERT INTO voters (registration_no, student_name, class_roll_no, session, 
                                              stream, department, mobile, password, status, approved) 
                           VALUES (?, ?, ?, '2023-24', 'Science', ?, ?, ?, 'active', 1)");
    $stmt->bind_param("ssssss", 
        $student['registration_no'],
        $student['student_name'],
        $student['roll_no'],
        $student['department'],
        $student['mobile'],
        $student['password']
    );
    
    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Error creating voter record: ' . $conn->error;
        header('Location: dashboard.php');
        exit();
    }
    
    $voter_id = $conn->insert_id;
} else {
    $voter_id = $student['voter_id'];
}

// Get all positions with their candidates
$positions = [];
$query = "SELECT p.*, c.candidate_id, c.name as candidate_name, c.photo, c.manifesto 
          FROM positions p 
          LEFT JOIN candidates c ON p.position_id = c.position_id 
          ORDER BY p.position_name, c.name";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $position_id = $row['position_id'];
    if (!isset($positions[$position_id])) {
        $positions[$position_id] = [
            'position_id' => $position_id,
            'position_name' => $row['position_name'],
            'candidates' => []
        ];
    }
    if ($row['candidate_id']) {
        $positions[$position_id]['candidates'][] = [
            'candidate_id' => $row['candidate_id'],
            'name' => $row['candidate_name'],
            'photo' => $row['photo'],
            'manifesto' => $row['manifesto']
        ];
    }
}

// Check which positions the voter has already voted for
$voted_positions = [];
$stmt = $conn->prepare("SELECT position_id FROM votes WHERE voter_id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $voted_positions[] = $row['position_id'];
}

// Get any messages from session
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - Student Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/student_styles.php'; ?>
    <style>
        .voting-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .position-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .position-title {
            color: #2b2d42;
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #edf2f7;
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
        
        .candidate-manifesto {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 15px;
            max-height: 60px;
            overflow-y: auto;
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
        
        .vote-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
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
                <?php foreach ($positions as $position): ?>
                    <div class="position-card">
                        <h2 class="position-title"><?php echo htmlspecialchars($position['position_name']); ?></h2>
                        
                        <?php if (empty($position['candidates'])): ?>
                            <p class="no-candidates">No candidates available for this position.</p>
                        <?php else: ?>
                            <div class="candidates-grid">
                                <?php foreach ($position['candidates'] as $candidate): ?>
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
                                        <?php if ($candidate['manifesto']): ?>
                                            <div class="candidate-manifesto">
                                                <?php echo htmlspecialchars($candidate['manifesto']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($position['position_id'], $voted_positions)): ?>
                                            <span class="voted-badge">
                                                <i class="fas fa-check"></i> Voted
                                            </span>
                                        <?php else: ?>
                                            <button class="vote-btn" onclick="castVote(<?php echo $candidate['candidate_id']; ?>)">
                                                <i class="fas fa-vote-yea"></i> Vote
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    function showAlert(message, type = 'error') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function castVote(candidateId) {
        if (!confirm('Are you sure you want to cast your vote for this candidate?')) {
            return;
        }

        // Create and submit a form to cast_vote.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cast_vote.php';

        const candidateInput = document.createElement('input');
        candidateInput.type = 'hidden';
        candidateInput.name = 'candidate_id';
        candidateInput.value = candidateId;

        form.appendChild(candidateInput);
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</body>
</html> 