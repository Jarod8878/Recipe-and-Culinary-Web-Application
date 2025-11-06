<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['plan_id'])) {
    die("Missing parameters.");
}

$entry_id = intval($_GET['id']);
$plan_id = intval($_GET['plan_id']);

// Query to fetch all recipes using MySQLi procedural style
$recipes_result = mysqli_query($con, "SELECT * FROM recipes");

// Fetch the meal entry
$entry_result = mysqli_query($con, "SELECT * FROM meal_entries WHERE id = $entry_id");
$entry = mysqli_fetch_assoc($entry_result);

if (!$entry) {
    die("Meal entry not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['meal_date'];
    $type = $_POST['meal_type'];
    $recipe_id = !empty($_POST['recipe_id']) ? $_POST['recipe_id'] : null;
    $custom = $_POST['custom_entry'];

    // Prepare and execute the update query
    $stmt = mysqli_prepare($con, "UPDATE meal_entries SET meal_date = ?, meal_type = ?, recipe_id = ?, custom_entry = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $date, $type, $recipe_id, $custom, $entry_id);
    mysqli_stmt_execute($stmt);

    header("Location: meal_plan_view.php?id=$plan_id");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Meal Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">Edit Meal Entry</h2>

        <form method="post" class="card p-4">
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="meal_date" value="<?= htmlspecialchars($entry['meal_date']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Meal Type</label>
                <select name="meal_type" class="form-select">
                    <option <?= $entry['meal_type'] == 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                    <option <?= $entry['meal_type'] == 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                    <option <?= $entry['meal_type'] == 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Recipe (optional)</label>
                <select name="recipe_id" class="form-select">
                    <option value="">-- Select Recipe --</option>
                    <?php while ($r = mysqli_fetch_assoc($recipes_result)): ?>
                        <option value="<?= $r['id'] ?>" <?= $entry['recipe_id'] == $r['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Custom Meal (optional)</label>
                <input type="text" name="custom_entry" class="form-control" value="<?= htmlspecialchars($entry['custom_entry']) ?>">
            </div>
            <button type="submit" class="btn btn-success">Update Entry</button>
            <a href="meal_plan_view.php?id=<?= $plan_id ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
