<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete entries from meal_entries table
    $delete_entries_query = "DELETE FROM meal_entries WHERE plan_id = $id";
    mysqli_query($con, $delete_entries_query);

    // Delete the meal plan from meal_plans table
    $delete_plan_query = "DELETE FROM meal_plans WHERE meal_plans_id = $id AND user_id = $user_id";
    if (mysqli_query($con, $delete_plan_query)) {
        header("Location: meal_plan_index.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Please try again.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Failed to delete.</div>";
}
?>
