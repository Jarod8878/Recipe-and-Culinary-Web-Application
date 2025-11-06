<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';

$query = "
    SELECT ce.competition_entry_id, r.name AS recipe_name, u.username AS submitter_name, 
           ce.submission_date, ce.status 
    FROM competition_entries ce
    JOIN recipes r ON ce.recipe_id = r.recipe_id
    JOIN users u ON ce.submitter_id = u.user_id
    ORDER BY ce.submission_date DESC
";
$result = mysqli_query($con, $query);
$entries = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to fetch competition entries: ' . mysqli_error($con)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Competition Entries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
    <style>
        .h2 {
            color: black !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="text-center mb-4">Competition Entries</h2>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> text-center">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (count($entries) > 0): ?>
            <table class="table table-hover table-bordered text-center align-middle bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Recipe Name</th>
                        <th>Submitter</th>
                        <th>Submission Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $index => $entry): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($entry['recipe_name']) ?></td>
                            <td><?= htmlspecialchars($entry['submitter_name']) ?></td>
                            <td><?= htmlspecialchars($entry['submission_date']) ?></td>
                            <td>
                                <?php if ($entry['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($entry['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($entry['status'] === 'winner'): ?>
                                    <span class="badge bg-primary">Winner</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">No competition entries found.</div>
        <?php endif; ?>

        <a href="competition.php" class="btn btn-primary mt-3 d-block mx-auto" style="width: 200px;">Back to Competition</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
