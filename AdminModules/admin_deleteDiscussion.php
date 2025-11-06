<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $discussionId = (int) $_GET['id'];

    // Delete associated comments first
    $deleteComments = "DELETE FROM comments WHERE discussion_id = $discussionId";
    mysqli_query($con, $deleteComments);

    // Delete discussion
    $deleteDiscussion = "DELETE FROM discussions WHERE discussion_id = $discussionId";
    mysqli_query($con, $deleteDiscussion);
}

header("Location: admin_discussion.php");
exit();
?>
