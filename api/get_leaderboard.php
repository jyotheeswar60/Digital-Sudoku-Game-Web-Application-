<?php
include 'db_connect.php';
header('Content-Type: application/json');

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'normal'; // 'normal' or 'voice'

if ($mode !== 'normal' && $mode !== 'voice') {
    $mode = 'normal';
}

// This query joins users with their stats, groups by user,
// sums all 'completed' stats for the specified mode, and orders them.
$sql = "SELECT
            u.username,
            SUM(s.completed) AS total_completed
        FROM
            users u
        JOIN
            user_stats s ON u.id = s.user_id
        WHERE
            s.mode = ?
        GROUP BY
            u.id, u.username
        HAVING
            SUM(s.completed) > 0
        ORDER BY
            total_completed DESC
        LIMIT 10"; // Get top 10 players

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mode);
$stmt->execute();
$result = $stmt->get_result();

$leaderboard = [];
$rank = 1;
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = [
        'rank' => $rank++,
        'username' => $row['username'],
        'completed' => (int)$row['total_completed']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
?>