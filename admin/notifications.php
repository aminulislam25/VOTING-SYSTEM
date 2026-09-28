<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Handle form submission for new notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = $_POST['title'];
                $message = $_POST['message'];
                $recipient_type = $_POST['recipient_type'];
                $department_id = ($recipient_type === 'department' && isset($_POST['department_id'])) ? $_POST['department_id'] : NULL;

                $stmt = $conn->prepare("INSERT INTO notifications (title, message, sender_type, recipient_type, department_id) VALUES (?, ?, 'admin', ?, ?)");
                $stmt->bind_param("sssi", $title, $message, $recipient_type, $department_id);
                $stmt->execute();
                break;

            case 'update':
                $notification_id = $_POST['notification_id'];
                $title = $_POST['title'];
                $message = $_POST['message'];
                
                $stmt = $conn->prepare("UPDATE notifications SET title = ?, message = ? WHERE notification_id = ?");
                $stmt->bind_param("ssi", $title, $message, $notification_id);
                $stmt->execute();
                break;

            case 'delete':
                $notification_id = $_POST['notification_id'];
                $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
                $stmt->bind_param("i", $notification_id);
                $stmt->execute();
                break;
        }
        header('Location: notifications.php');
        exit();
    }
}

// Get all notifications
$notifications = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Get all departments for the dropdown
$departments = $conn->query("SELECT * FROM departments")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add your existing admin dashboard styles here */
        
        .notification-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .notification-form input[type="text"],
        .notification-form textarea,
        .notification-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .notification-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        .notification-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
        }

        .btn-danger {
            background: #f72585;
            color: white;
        }

        .btn-warning {
            background: #ffd60a;
            color: black;
        }
    </style>
</head>
<body>
    <div class="notification-form">
        <h2>Create New Notification</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <input type="text" name="title" placeholder="Notification Title" required>
            <textarea name="message" placeholder="Notification Message" required></textarea>
            <select name="recipient_type" id="recipient_type" required>
                <option value="all">All Users</option>
                <option value="department">Specific Department</option>
                <option value="student">All Students</option>
            </select>
            <div id="department_select" style="display: none;">
                <select name="department_id">
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['department_id']; ?>">
                            <?php echo htmlspecialchars($dept['department']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Notification</button>
        </form>
    </div>

    <div class="notification-list">
        <h2>All Notifications</h2>
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-item">
                <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <p>Recipient: <?php echo htmlspecialchars($notification['recipient_type']); ?></p>
                <p>Created: <?php echo $notification['created_at']; ?></p>
                
                <button class="btn btn-warning" onclick="editNotification(<?php echo $notification['notification_id']; ?>)">
                    Edit
                </button>
                
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        document.getElementById('recipient_type').addEventListener('change', function() {
            const departmentSelect = document.getElementById('department_select');
            departmentSelect.style.display = this.value === 'department' ? 'block' : 'none';
        });

        function editNotification(id) {
            // Implement edit functionality with a modal or redirect to edit page
            alert('Edit functionality to be implemented');
        }
    </script>
</body>
</html> 