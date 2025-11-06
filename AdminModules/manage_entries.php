<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $entry_id = (int)$_POST['entry_id'];
    $new_status = $_POST['status'];

    if (in_array($new_status, ['pending', 'approved', 'winner'])) {
        $query = "UPDATE competition_entries SET status = '$new_status' WHERE competition_entry_id = $entry_id";
        if (mysqli_query($con, $query)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status updated successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to update status.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid status.'];
    }
    header("Location: manage_entries.php");
    exit();
}

// Handle delete entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_entry'])) {
    $entry_id = (int)$_POST['entry_id'];

    // First delete dependent votes
    $delete_votes = "DELETE FROM competition_votes WHERE competition_entry_id = $entry_id";
    mysqli_query($con, $delete_votes);

    $delete_entry = "DELETE FROM competition_entries WHERE competition_entry_id = $entry_id";
    if (mysqli_query($con, $delete_entry)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entry deleted successfully!'];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to delete entry.'];
    }
    header("Location: manage_entries.php");
    exit();
}

// Fetch entries
$query = "
    SELECT ce.competition_entry_id, r.name AS recipe_name, r.ingredients, r.steps, r.image,
           u.username AS submitter_name, ce.submission_date, ce.status
    FROM competition_entries ce
    JOIN recipes r ON ce.recipe_id = r.recipe_id
    JOIN users u ON ce.submitter_id = u.user_id
    ORDER BY ce.submission_date DESC
";
$result = mysqli_query($con, $query);
$entries = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Entries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../uploads/styling.css">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="bg-overlay"></div>
<div class="container container-content">
    <h2 class="text-center mb-4">Manage Competition Entries</h2>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> text-center">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (count($entries) > 0): ?>
        <table class="table table-hover table-bordered align-middle text-center">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Recipe Name</th>
                <th>Image</th>
                <th>Ingredients</th>
                <th>Steps</th>
                <th>Submitter</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($entries as $index => $entry): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($entry['recipe_name']) ?></td>
                    <td>
                        <img src="../uploads/<?= htmlspecialchars($entry['image']) ?>" alt="Image"
                             style="width: 100px; height: auto; object-fit: cover;">
                    </td>
                    <td><?= htmlspecialchars(substr($entry['ingredients'], 0, 30)) ?>...</td>
                    <td><?= htmlspecialchars(substr($entry['steps'], 0, 30)) ?>...</td>
                    <td><?= htmlspecialchars($entry['submitter_name']) ?></td>
                    <td><?= htmlspecialchars($entry['submission_date']) ?></td>
                    <td>
                        <span class="badge
                            <?= $entry['status'] === 'winner' ? 'bg-success' :
                               ($entry['status'] === 'approved' ? 'bg-primary' : 'bg-secondary') ?>">
                            <?= ucfirst(htmlspecialchars($entry['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="entry_id" value="<?= $entry['competition_entry_id'] ?>">
                            <select name="status" class="form-select form-select-sm mb-1">
                                <option value="pending" <?= $entry['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $entry['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="winner" <?= $entry['status'] === 'winner' ? 'selected' : '' ?>>Winner</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="entry_id" value="<?= $entry['competition_entry_id'] ?>">
                            <button type="submit" name="delete_entry" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center">No competition entries found.</div>
    <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
