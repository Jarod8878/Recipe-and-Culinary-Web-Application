<?php
session_start();
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $discussion_id = (int)$_POST['discussion_id'];
    $comment_text = trim($_POST['comment_text']);

    if (!empty($comment_text)) {
        $comment = mysqli_real_escape_string($con, $comment_text);

        $query = "INSERT INTO comments (user_id, discussion_id, content, created_at) VALUES ($user_id, $discussion_id, '$comment', NOW())";
        mysqli_query($con, $query);
    }
}

header("Location: discussion.php?open=modal_$discussion_id");
exit();
