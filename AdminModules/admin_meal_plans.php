<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $plan_id = (int)$_GET['id'];

    $delete_query = "DELETE FROM meal_plans WHERE meal_plans_id = ?";
    $delete_stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "i", $plan_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);

    header("Location: admin_meal_plans.php");
    exit();
}

$users_query = "SELECT user_id, username FROM users WHERE role != 'admin'";
$users_result = mysqli_query($con, $users_query);

$user_id = $_GET['user_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Pagination setup
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Get total entries for pagination
$total_query = "SELECT COUNT(*) AS total FROM meal_plans 
                LEFT JOIN users ON meal_plans.user_id = users.user_id 
                WHERE users.role != 'admin'";

if (!empty($user_id)) {
    $total_query .= " AND meal_plans.user_id = " . (int)$user_id;
}
if (!empty($start_date)) {
    $total_query .= " AND meal_plans.start_date >= '" . mysqli_real_escape_string($con, $start_date) . "'";
}
if (!empty($end_date)) {
    $total_query .= " AND meal_plans.end_date <= '" . mysqli_real_escape_string($con, $end_date) . "'";
}

$total_result = mysqli_query($con, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_entries = $total_row['total'];
$total_pages = ceil($total_entries / $per_page);

// Get meal plans with LIMIT and OFFSET for current page
$query = "SELECT meal_plans.*, users.username 
          FROM meal_plans 
          LEFT JOIN users ON meal_plans.user_id = users.user_id 
          WHERE users.role != 'admin'";

if (!empty($user_id)) {
    $query .= " AND meal_plans.user_id = " . (int)$user_id;
}
if (!empty($start_date)) {
    $query .= " AND meal_plans.start_date >= '" . mysqli_real_escape_string($con, $start_date) . "'";
}
if (!empty($end_date)) {
    $query .= " AND meal_plans.end_date <= '" . mysqli_real_escape_string($con, $end_date) . "'";
}

$query .= " ORDER BY meal_plans.start_date DESC LIMIT $per_page OFFSET $offset";
$plans_result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Meal Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">📋 All Meal Plans</h2>
        </div>

        <!-- Filter Form -->
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label>User:</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($user['user_id'] == $user_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Start Date From:</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label>End Date To:</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin_meal_plans.php" class="btn btn-secondary ms-2">Reset</a>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if ($plans_result && mysqli_num_rows($plans_result) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Plan Name</th>
                                <th>User</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($plan = mysqli_fetch_assoc($plans_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($plan['plan_name']) ?></td>
                                <td><?= htmlspecialchars($plan['username']) ?></td>
                                <td><?= htmlspecialchars($plan['start_date']) ?></td>
                                <td><?= htmlspecialchars($plan['end_date']) ?></td>
                                <td><?= htmlspecialchars($plan['created_at']) ?></td>
                                <td>
                                    <a href="admin_meal_view.php?id=<?= $plan['meal_plans_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                    <a href="admin_meal_plan_edit.php?id=<?= $plan['meal_plans_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="?id=<?= $plan['meal_plans_id'] ?>&delete=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this entry?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= ($user_id ? '&user_id=' . $user_id : '') . ($start_date ? '&start_date=' . $start_date : '') . ($end_date ? '&end_date=' . $end_date : '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>

                <?php else: ?>
                    <p class="text-muted">No meal plans found for the selected criteria.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
