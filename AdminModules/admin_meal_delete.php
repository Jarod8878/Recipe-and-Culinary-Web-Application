<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['plan_id'])) {
    die("Missing parameters.");
}

$entry_id = (int)$_GET['id'];
$plan_id = (int)$_GET['plan_id'];

$delete_sql = "DELETE FROM meal_entries WHERE meal_entries_id = $entry_id";
if (mysqli_query($con, $delete_sql)) {
    header("Location: admin_meal_view.php?id=" . $plan_id);
    exit();
} else {
    die("Error deleting meal entry: " . mysqli_error($con));
}
?>
