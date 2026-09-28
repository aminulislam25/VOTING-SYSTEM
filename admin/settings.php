<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get admin details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Admin not found, clear session and redirect
    session_destroy();
    header('Location: ../index.php');
    exit();
}

$admin = $result->fetch_assoc();

// Get current settings
$settings = [];
$settings_result = $conn->query("SELECT * FROM settings");
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Default values if settings don't exist
$election_status = $settings['election_status'] ?? 'inactive';
$results_visibility = $settings['results_visibility'] ?? 'hidden';

// Get success message if any
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Get error message if any
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_election_status'])) {
        $new_status = $_POST['election_status'];
        
        // Validate input
        if (!in_array($new_status, ['inactive', 'active', 'completed'])) {
            $error_message = "Invalid election status.";
        } else {
            // Update election status
            $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('election_status', ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $new_status, $new_status);
            
            if ($stmt->execute()) {
                $success_message = "Election status updated successfully.";
                $election_status = $new_status;
            } else {
                $error_message = "Error updating election status: " . $conn->error;
            }
        }
    } elseif (isset($_POST['update_results_visibility'])) {
        $new_visibility = $_POST['results_visibility'];
        
        // Validate input
        if (!in_array($new_visibility, ['hidden', 'visible'])) {
            $error_message = "Invalid results visibility.";
        } else {
            // Update results visibility
            $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('results_visibility', ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $new_visibility, $new_visibility);
            
            if ($stmt->execute()) {
                $success_message = "Results visibility updated successfully.";
                $results_visibility = $new_visibility;
            } else {
                $error_message = "Error updating results visibility: " . $conn->error;
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
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Settings</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- General Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">General Settings</h2>
                </div>
                <div class="card-body">
                    <form action="process_settings.php" method="POST" class="settings-form">
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Online Voting System'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" class="form-control" rows="3"><?php 
                                echo htmlspecialchars($settings['site_description'] ?? 'A secure online voting platform for educational institutions.');
                            ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>
            </div>

            <!-- Election Settings -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Election Settings</h2>
                </div>
                <div class="card-body">
                    <form action="process_election_settings.php" method="POST" class="settings-form">
                        <div class="form-group">
                            <label>Election Status</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="election_status" value="inactive" 
                                           <?php echo (!isset($settings['election_status']) || $settings['election_status'] === 'inactive') ? 'checked' : ''; ?>>
                                    Inactive
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="election_status" value="active"
                                           <?php echo (isset($settings['election_status']) && $settings['election_status'] === 'active') ? 'checked' : ''; ?>>
                                    Active
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="election_status" value="completed"
                                           <?php echo (isset($settings['election_status']) && $settings['election_status'] === 'completed') ? 'checked' : ''; ?>>
                                    Completed
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Results Visibility</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="results_visibility" value="hidden"
                                           <?php echo (!isset($settings['results_visibility']) || $settings['results_visibility'] === 'hidden') ? 'checked' : ''; ?>>
                                    Hidden
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="results_visibility" value="visible"
                                           <?php echo (isset($settings['results_visibility']) && $settings['results_visibility'] === 'visible') ? 'checked' : ''; ?>>
                                    Visible
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="max_votes_per_voter">Maximum Votes per Voter</label>
                            <input type="number" id="max_votes_per_voter" name="max_votes_per_voter" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['max_votes_per_voter'] ?? '1'); ?>" min="1">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Election Settings</button>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Security Settings</h2>
                </div>
                <div class="card-body">
                    <form action="process_security_settings.php" method="POST" class="settings-form">
                        <div class="form-group">
                            <label>Enable Two-Factor Authentication</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="enable_2fa" value="0"
                                           <?php echo (!isset($settings['enable_2fa']) || $settings['enable_2fa'] === '0') ? 'checked' : ''; ?>>
                                    Disabled
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="enable_2fa" value="1"
                                           <?php echo (isset($settings['enable_2fa']) && $settings['enable_2fa'] === '1') ? 'checked' : ''; ?>>
                                    Enabled
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" name="session_timeout" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>" min="5">
                        </div>

                        <div class="form-group">
                            <label>Maximum Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-control"
                                   value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>" min="1">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 