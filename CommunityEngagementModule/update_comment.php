<?php
session_start();
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    $content = $_POST['content'];

    $comment_id = mysqli_real_escape_string($con, $comment_id);
    $content = mysqli_real_escape_string($con, $content);

    $discussion_query = "SELECT discussion_id FROM comments WHERE comment_id = $comment_id";
    $discussion_result = mysqli_query($con, $discussion_query);
    $row = mysqli_fetch_assoc($discussion_result);
    $discussion_id = $row['discussion_id'];

    $update_query = "UPDATE comments SET content = '$content' WHERE comment_id = $comment_id";
    mysqli_query($con, $update_query);

    header("Location: discussion.php?open=modal_$discussion_id");
    exit();
}
?>

