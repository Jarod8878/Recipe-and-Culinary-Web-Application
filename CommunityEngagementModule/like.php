<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$discussionId = (int)($_POST['discussion_id'] ?? 0);

if ($discussionId === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid discussion ID']);
    exit();
}

$checkQuery = "SELECT * FROM discussion_likes WHERE user_id = $userId AND discussion_id = $discussionId";
$checkResult = mysqli_query($con, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    $deleteQuery = "DELETE FROM discussion_likes WHERE user_id = $userId AND discussion_id = $discussionId";
    mysqli_query($con, $deleteQuery);
    echo json_encode(['liked' => false]);
} else {
    $insertQuery = "INSERT INTO discussion_likes (user_id, discussion_id) VALUES ($userId, $discussionId)";
    mysqli_query($con, $insertQuery);
    echo json_encode(['liked' => true]);
}
?>
