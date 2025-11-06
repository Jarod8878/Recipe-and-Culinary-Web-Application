<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Meal plan ID not provided.");
}

$plan_id = (int)$_GET['id'];

$plan_sql = "SELECT plan_name FROM meal_plans WHERE meal_plans_id = $plan_id";
$plan_result = mysqli_query($con, $plan_sql);

if (!$plan_result || mysqli_num_rows($plan_result) == 0) {
    die("Meal plan not found.");
}

$plan_row = mysqli_fetch_assoc($plan_result);
$plan_name = $plan_row['plan_name'];

// Pagination setup
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Get total entries for pagination
$total_sql = "SELECT COUNT(*) AS total FROM meal_entries WHERE plan_id = $plan_id";
$total_result = mysqli_query($con, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_entries = $total_row['total'];
$total_pages = ceil($total_entries / $per_page);

// Get entries with LIMIT for current page
$entries_sql = "
    SELECT meal_entries.*, recipes.name AS recipe_name
    FROM meal_entries
    LEFT JOIN recipes ON meal_entries.recipe_id = recipes.recipe_id
    WHERE meal_entries.plan_id = $plan_id
    ORDER BY meal_date ASC, FIELD(meal_type, 'Breakfast', 'Lunch', 'Dinner')
    LIMIT $per_page OFFSET $offset
";
$entries_result = mysqli_query($con, $entries_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin View - Meal Plan Entries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">🍽️ Meal Plan: <?= htmlspecialchars($plan_name) ?></h2>
            <a href="admin_meal_plans.php" class="btn btn-secondary">← Back to Meal Plans</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (mysqli_num_rows($entries_result) > 0): ?>
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Meal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($entries_result)): ?>
                            <?php
                                $meal = $row['recipe_name'] ?: $row['custom_entry'] ?: '-';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['meal_date']) ?></td>
                                <td><?= htmlspecialchars($row['meal_type']) ?></td>
                                <td><?= htmlspecialchars($meal) ?></td>
                                <td>
                                    <a href="admin_meal_edit.php?id=<?= $row['meal_entries_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="admin_meal_delete.php?id=<?= $row['meal_entries_id'] ?>&plan_id=<?= $plan_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this meal entry?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>

                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?id=<?= $plan_id ?>&page=<?= $p ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <p class="text-muted">No entries found for this meal plan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
