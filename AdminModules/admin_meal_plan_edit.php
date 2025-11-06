<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Meal plan ID not provided.");
}

$plan_id = (int)$_GET['id'];

$plan_query = "SELECT * FROM meal_plans WHERE meal_plans_id = $plan_id";
$plan_result = mysqli_query($con, $plan_query);
$plan = mysqli_fetch_assoc($plan_result);

if (!$plan) {
    die("Meal plan not found.");
}

$users_query = "SELECT user_id, username FROM users WHERE role != 'admin'";
$users_result = mysqli_query($con, $users_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name = mysqli_real_escape_string($con, $_POST['plan_name']);
    $user_id = (int)$_POST['user_id'];
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);

    $update_query = "UPDATE meal_plans SET plan_name = '$plan_name', 
                     user_id = $user_id, 
                     start_date = '$start_date', 
                     end_date = '$end_date' 
                    WHERE meal_plans_id = $plan_id";
    mysqli_query($con, $update_query);
    header("Location: admin_meal_plans.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Meal Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">✏️ Edit Meal Plan</h2>
            <a href="admin_meal_plans.php" class="btn btn-secondary">← Back</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="plan_name" class="form-label">Plan Name</label>
                        <input type="text" class="form-control" name="plan_name" id="plan_name" value="<?= htmlspecialchars($plan['plan_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <option value="<?= $user['user_id'] ?>" <?= $user['user_id'] == $plan['user_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="<?= htmlspecialchars($plan['start_date']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="<?= htmlspecialchars($plan['end_date']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Meal Plan</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
