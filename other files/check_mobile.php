<?php
header('Content-Type: application/json');
include 'config.php';

$mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
$user_type = mysqli_real_escape_string($conn, $_POST['user_type']);

$table = '';
switch ($user_type) {
    case 'admin':
        $table = 'admins';
        break;
    case 'voter':
        $table = 'voters';
        break;
    case 'dept':
        $table = 'departments';
        break;
    default:
        echo json_encode(['exists' => false]);
        exit;
}

$sql = "SELECT id FROM $table WHERE mobile = '$mobile'";
$result = $conn->query($sql);

echo json_encode(['exists' => $result->num_rows > 0]);
$conn->close();
?> 