<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $commentId = (int)$_GET['id'];

    $delete = "DELETE FROM comments WHERE comment_id = $commentId";
    mysqli_query($con, $delete);
}

header("Location: admin_discussion.php");
exit();
