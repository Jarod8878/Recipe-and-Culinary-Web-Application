<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $discussionId = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    $checkQuery = "SELECT * FROM discussions WHERE discussion_id = $discussionId AND user_id = $userId";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $deleteCommentsQuery = "DELETE FROM comments WHERE discussion_id = $discussionId";
        mysqli_query($con, $deleteCommentsQuery);

        $deleteDiscussionQuery = "DELETE FROM discussions WHERE discussion_id = $discussionId";
        mysqli_query($con, $deleteDiscussionQuery);
    }

    header("Location: discussion.php");
    exit();
} else {
    echo "Invalid request: Discussion ID not provided.";
}
?>
