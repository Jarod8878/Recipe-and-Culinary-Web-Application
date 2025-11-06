<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name = mysqli_real_escape_string($con, $_POST['plan_name']);
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);
    $user_id = $_SESSION['user_id'];

    $query = "INSERT INTO meal_plans (user_id, plan_name, start_date, end_date) 
              VALUES ('$user_id', '$plan_name', '$start_date', '$end_date')";
    mysqli_query($con, $query);

    header("Location: meal_plan_index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Meal Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="container container-content py-5">
        <h2 class="mb-4">Create Meal Plan</h2>
        <form method="post" class="form-box">
            <div class="mb-3">
                <label class="form-label">Plan Name</label>
                <input type="text" name="plan_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Plan</button>
            <a href="meal_plan_index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>
</html>
