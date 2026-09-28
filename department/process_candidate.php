<?php
require_once '../config.php';

// Check if user is logged in and is a department
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'department') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$department_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

function handleUpload($file) {
    $target_dir = "../uploads/candidates/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Check file size and type
    if ($file['size'] > 5000000) { // 5MB limit
        throw new Exception("File is too large. Maximum size is 5MB.");
    }

    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception("Only JPG, JPEG & PNG files are allowed.");
    }

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $new_filename;
    }

    throw new Exception("Error uploading file.");
}

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'add':
            // Validate input
            if (empty($_POST['name']) || empty($_POST['position'])) {
                throw new Exception("Name and position are required.");
            }

            $name = $_POST['name'];
            $position = $_POST['position'];
            $description = $_POST['description'] ?? '';
            $photo = null;

            // Handle photo upload if provided
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = handleUpload($_FILES['photo']);
            }

            // Insert candidate
            $stmt = $conn->prepare("
                INSERT INTO candidates (name, position, description, photo, department_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssi", $name, $position, $description, $photo, $department_id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Candidate added successfully']);
            } else {
                throw new Exception("Error adding candidate");
            }
            break;

        case 'edit':
            if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['position'])) {
                throw new Exception("ID, name and position are required.");
            }

            $id = $_POST['id'];
            $name = $_POST['name'];
            $position = $_POST['position'];
            $description = $_POST['description'] ?? '';

            // Verify candidate belongs to department
            $stmt = $conn->prepare("SELECT id FROM candidates WHERE id = ? AND department_id = ?");
            $stmt->bind_param("ii", $id, $department_id);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                throw new Exception("Unauthorized access to candidate");
            }

            // Handle photo upload if provided
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = handleUpload($_FILES['photo']);
                $stmt = $conn->prepare("
                    UPDATE candidates 
                    SET name = ?, position = ?, description = ?, photo = ? 
                    WHERE id = ? AND department_id = ?
                ");
                $stmt->bind_param("ssssii", $name, $position, $description, $photo, $id, $department_id);
            } else {
                $stmt = $conn->prepare("
                    UPDATE candidates 
                    SET name = ?, position = ?, description = ? 
                    WHERE id = ? AND department_id = ?
                ");
                $stmt->bind_param("sssii", $name, $position, $description, $id, $department_id);
            }

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Candidate updated successfully']);
            } else {
                throw new Exception("Error updating candidate");
            }
            break;

        case 'delete':
            if (empty($_GET['id'])) {
                throw new Exception("Candidate ID is required.");
            }

            $id = $_GET['id'];

            // Verify candidate belongs to department
            $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ? AND department_id = ?");
            $stmt->bind_param("ii", $id, $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidate = $result->fetch_assoc();

            if (!$candidate) {
                throw new Exception("Unauthorized access to candidate");
            }

            // Delete candidate's photo if exists
            if ($candidate['photo']) {
                $photo_path = "../uploads/candidates/" . $candidate['photo'];
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }

            // Delete candidate
            $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ? AND department_id = ?");
            $stmt->bind_param("ii", $id, $department_id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Candidate deleted successfully']);
            } else {
                throw new Exception("Error deleting candidate");
            }
            break;

        case 'get':
            if (empty($_GET['id'])) {
                throw new Exception("Candidate ID is required.");
            }

            $id = $_GET['id'];

            // Get candidate details
            $stmt = $conn->prepare("
                SELECT id, name, position, description, photo 
                FROM candidates 
                WHERE id = ? AND department_id = ?
            ");
            $stmt->bind_param("ii", $id, $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidate = $result->fetch_assoc();

            if (!$candidate) {
                throw new Exception("Candidate not found");
            }

            echo json_encode(['status' => 'success', 'candidate' => $candidate]);
            break;

        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?> 