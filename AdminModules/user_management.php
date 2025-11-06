<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = (int) $_GET['id'];
    mysqli_begin_transaction($con);

    try {
        mysqli_query($con, "DELETE FROM competition_entries WHERE submitter_id = $user_id");
        mysqli_query($con, "DELETE FROM comments WHERE user_id = $user_id");
        mysqli_query($con, "DELETE FROM recipes WHERE user_id = $user_id");
        mysqli_query($con, "DELETE FROM meal_plans WHERE user_id = $user_id");
        mysqli_query($con, "DELETE FROM users WHERE user_id = $user_id");

        mysqli_commit($con);
        header("Location: user_management.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "<div class='alert alert-danger'>Delete failed: " . $e->getMessage() . "</div>";
    }
}

$users_query = "SELECT user_id, username, email, role, date_created FROM users WHERE username != 'admin'";
$users_result = mysqli_query($con, $users_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
</head>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">User Management</h2>
        <div class="mb-3 text-end">
            <a href="createAdmin.php" class="btn btn-primary">
                <i class="bi bi-plus-lg text-white me-1"></i> Create New Admin
            </a>
        </div>
        <?php if ($users_result && mysqli_num_rows($users_result) > 0): ?>
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                            <td><?= htmlspecialchars($user['date_created']) ?></td>
                            <td>
                                <a href="?delete=1&id=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this user and all their data?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">No users found.</div>
        <?php endif; ?>
    </div>
</body>

</html>
