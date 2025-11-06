<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $plan_id = intval($_GET['id']);

    $sql = "SELECT * FROM meal_plans WHERE meal_plans_id = $plan_id AND user_id = $user_id";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) == 1) {
        $plan = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='alert alert-danger'> Meal Plan not found</div>";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_id = intval($_POST['plan_id']);
    $plan_name = $_POST['plan_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "UPDATE meal_plans SET plan_name = '$plan_name', start_date = '$start_date', end_date = '$end_date' 
            WHERE meal_plans_id = $plan_id AND user_id = $user_id";

    if (mysqli_query($con, $sql)) {
        header("Location: meal_plan_index.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Meal Plan</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>

    <div class="container container-content py-5">
        <h2 class="fw-bold mb-4 text-dark">Edit Meal Plan</h2>

        <form action="meal_plan_edit.php" method="post" class="card p-4 bg-light shadow-sm">
            <div class="mb-3">
                <label class="form-label fw-semibold">Plan Name</label>
                <input type="text" name="plan_name" class="form-control" value="<?= htmlspecialchars($plan['plan_name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $plan['start_date'] ?? '' ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $plan['end_date'] ?? '' ?>" required>
            </div>

            <input type="hidden" name="plan_id" value="<?= $plan['meal_plans_id'] ?? '' ?>">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Plan</button>
                <a href="meal_plan_index.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</body>

</html>
