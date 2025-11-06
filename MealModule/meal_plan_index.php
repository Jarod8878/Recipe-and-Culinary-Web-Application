<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $search . '%';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit;

// Count total records
$count_sql = "SELECT COUNT(*) AS total FROM meal_plans WHERE user_id = $user_id AND plan_name LIKE ?";
$count_stmt = mysqli_prepare($con, $count_sql);
mysqli_stmt_bind_param($count_stmt, "s", $searchTerm);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch paginated data
$sql = "SELECT * FROM meal_plans WHERE user_id = $user_id AND plan_name LIKE ? ORDER BY start_date DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "sii", $searchTerm, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meal Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
<div class="bg-overlay"></div>

<div class="container container-content py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Meal Plans</h2>
        <a href="meal_plan_create.php" class="btn btn-success">+ Add Meal Plan</a>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-8">
            <input type="text" name="search" class="form-control" placeholder="Search by Plan Name..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle bg-white rounded">
                <thead class="table-dark">
                    <tr>
                        <th>Plan Name</th>
                        <th>Start</th>
                        <th>End</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['plan_name']) ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="meal_plan_view.php?id=<?= $row['meal_plans_id'] ?>" class="btn btn-sm btn-primary">View</a>
                                    <a href="meal_plan_edit.php?id=<?= $row['meal_plans_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="meal_plan_delete.php?id=<?= $row['meal_plans_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this plan?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php else: ?>
        <div class="alert alert-info">You haven't created any meal plans yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
