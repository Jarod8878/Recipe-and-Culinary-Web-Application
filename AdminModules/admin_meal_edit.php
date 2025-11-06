<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Meal entry ID not provided.");
}

$entry_id = (int)$_GET['id'];

// Fetch existing entry
$entry_sql = "SELECT * FROM meal_entries WHERE meal_entries_id = $entry_id";
$entry_result = mysqli_query($con, $entry_sql);

if (mysqli_num_rows($entry_result) === 0) {
    die("Meal entry not found.");
}

$entry = mysqli_fetch_assoc($entry_result);

// Fetch available recipes
$recipes_result = mysqli_query($con, "SELECT recipe_id, name FROM recipes");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_date = mysqli_real_escape_string($con, $_POST['meal_date']);
    $meal_type = mysqli_real_escape_string($con, $_POST['meal_type']);
    $recipe_id = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : null;
    $custom_entry = !empty($_POST['custom_entry']) ? mysqli_real_escape_string($con, $_POST['custom_entry']) : null;

    // Prevent both recipe and custom entry from being set at the same time
    if ($recipe_id && $custom_entry) {
        die("Choose either a recipe or a custom entry, not both.");
    }

    $recipe_id_sql = $recipe_id !== null ? $recipe_id : 'NULL';
    $custom_entry_sql = $custom_entry !== null ? "'$custom_entry'" : 'NULL';

    $update_sql = "UPDATE meal_entries SET meal_date = '$meal_date', 
                   meal_type = '$meal_type', 
                   recipe_id = $recipe_id_sql, 
                   custom_entry = $custom_entry_sql 
                   WHERE meal_entries_id = $entry_id";

    if (mysqli_query($con, $update_sql)) {
        header("Location: admin_meal_view.php?id=" . $entry['plan_id']);
        exit();
    } else {
        $error = "Failed to update meal entry.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Meal Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2>Edit Meal Entry</h2>

        <form method="post" class="card p-4 shadow-sm">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="meal_date" class="form-label">Meal Date</label>
                <input type="date" class="form-control" name="meal_date" id="meal_date" value="<?= htmlspecialchars($entry['meal_date']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="meal_type" class="form-label">Meal Type</label>
                <select class="form-select" name="meal_type" id="meal_type" required>
                    <option value="Breakfast" <?= $entry['meal_type'] == 'Breakfast' ? 'selected' : '' ?>>Breakfast</option>
                    <option value="Lunch" <?= $entry['meal_type'] == 'Lunch' ? 'selected' : '' ?>>Lunch</option>
                    <option value="Dinner" <?= $entry['meal_type'] == 'Dinner' ? 'selected' : '' ?>>Dinner</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="recipe_id" class="form-label">Select a Recipe (Optional)</label>
                <select class="form-select" name="recipe_id" id="recipe_id">
                    <option value="">-- None --</option>
                    <?php
                    $recipes_result = mysqli_query($con, "SELECT recipe_id, name FROM recipes");
                    while ($recipe = mysqli_fetch_assoc($recipes_result)): ?>
                        <option value="<?= $recipe['recipe_id'] ?>" <?= $entry['recipe_id'] == $recipe['recipe_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($recipe['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="custom_entry" class="form-label">Custom Meal Entry (Optional)</label>
                <input type="text" class="form-control" name="custom_entry" id="custom_entry" value="<?= htmlspecialchars($entry['custom_entry']) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Update Meal Entry</button>
            <a href="admin_meal_plan_entries.php?id=<?= $entry['plan_id'] ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        document.getElementById('recipe_id').addEventListener('change', function () {
            if (this.value !== '') {
                document.getElementById('custom_entry').value = '';
            }
        });

        document.getElementById('custom_entry').addEventListener('input', function () {
            if (this.value.trim() !== '') {
                document.getElementById('recipe_id').value = '';
            }
        });
    </script>
</body>
</html>
