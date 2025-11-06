<?php
session_start();
require '../db_connect.php';

if (isset($_GET['id'])) {
    $comment_id = (int)$_GET['id'];

    $discussion_query = "SELECT discussion_id FROM comments WHERE comment_id = $comment_id";
    $discussion_result = mysqli_query($con, $discussion_query);
    $row = mysqli_fetch_assoc($discussion_result);
    $discussion_id = $row['discussion_id'];

    $delete_query = "DELETE FROM comments WHERE comment_id = $comment_id";
    mysqli_query($con, $delete_query);

    header("Location: discussion.php?open=modal_$discussion_id");
    exit();
}
?>
