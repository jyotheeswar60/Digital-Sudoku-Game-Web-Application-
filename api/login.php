<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

// Ensure POST data exists
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter username and password.']);
    exit;
}

// Prepare statement to prevent SQL injection & fetch email
$stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
if (!$stmt) {
     // Log error: $conn->error
    echo json_encode(['success' => false, 'message' => 'Database prepare error.']);
    $conn->close();
    exit;
}
$stmt->bind_param("ss", $username, $username); // Use the same variable for both username/email check

if (!$stmt->execute()) {
    // Log error: $stmt->error
    echo json_encode(['success' => false, 'message' => 'Database execute error.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No user found with that username/email.']);
    $stmt->close();
    $conn->close();
    exit;
}

// Bind result variables
$stmt->bind_result($id, $db_username, $db_email, $hashed_password); // Use distinct variable names
$stmt->fetch();

// Verify password
if (password_verify($password, $hashed_password)) {
    // Password is correct, start session
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $id;
    $_SESSION["username"] = $db_username; // Store the actual username from DB

    // Create user data array
    $user_data = ['username' => $db_username, 'email' => $db_email];

    // Include it in the JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'user' => $user_data // Add user data here
    ]);

} else {
    // Password is not valid
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
}

$stmt->close();
$conn->close();
?>