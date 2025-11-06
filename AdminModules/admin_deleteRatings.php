<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $ratingId = (int)$_GET['id'];

    $deleteQuery = "DELETE FROM ratings WHERE rating_id = $ratingId";
    $result = mysqli_query($con, $deleteQuery);

    if (!$result) {
        die("Error deleting rating: " . mysqli_error($con));
    }
}

header("Location: admin_discussion.php");
exit();
?>
