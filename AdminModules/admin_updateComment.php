<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['content'])) {
    $commentId = (int)$_POST['comment_id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $content = mysqli_real_escape_string($con, $content);
        $query = "UPDATE comments SET content = '$content' WHERE comment_id = $commentId";
        mysqli_query($con, $query);
    }
}

header("Location: admin_discussion.php");
exit();
