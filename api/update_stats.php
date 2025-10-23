<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Get data from JavaScript
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['mode']) || !isset($data['difficulty']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$user_id = $_SESSION["id"];
$mode = $data['mode'];
$difficulty = $data['difficulty'];
$status = $data['status']; // 'completed' or 'dropped'

if ($status == 'completed') {
    $column_to_update = 'completed';
} elseif ($status == 'dropped') {
    $column_to_update = 'dropped';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

// Use "INSERT ... ON DUPLICATE KEY UPDATE" to create or update the row
$sql = "INSERT INTO user_stats (user_id, mode, difficulty, $column_to_update)
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
        $column_to_update = $column_to_update + 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iss", $user_id, $mode, $difficulty);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Stats updated.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>