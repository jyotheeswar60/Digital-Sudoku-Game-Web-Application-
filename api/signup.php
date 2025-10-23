<?php
include 'db_connect.php';
header('Content-Type: application/json');

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields.']); exit;
}

$hashedpassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedpassword);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful! You can now log in.']);
} else if ($conn->errno == 1062) {
    echo json_encode(['success' => false, 'message' => 'This email or username is already taken.']);
} else {
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
$stmt->close();
$conn->close();
?>