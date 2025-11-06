<?php
session_start();
include '../db_connect.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied.");
}

// Get recipe ID
$recipe_id = $_GET['id'] ?? null;
if (!$recipe_id) {
    die("Invalid recipe ID.");
}

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cuisine = $_POST['cuisine'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image_name = basename($_FILES['image']['name']);
        $target = "../uploads/" . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $sql = "UPDATE recipes SET name=?, cuisine=?, ingredients=?, steps=?, image=? WHERE recipe_id=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $name, $cuisine, $ingredients, $steps, $image_name, $recipe_id);
    } else {
        $sql = "UPDATE recipes SET name=?, cuisine=?, ingredients=?, steps=? WHERE recipe_id=?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $cuisine, $ingredients, $steps, $recipe_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_recipe_index.php?msg=Recipe+updated");
        exit;
    } else {
        $error = "Update failed: " . mysqli_error($con);
    }
}

// Fetch existing recipe
$sql = "SELECT * FROM recipes WHERE recipe_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $recipe_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) die("Recipe not found.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">Edit Recipe</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Recipe Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Cuisine</label>
                <select name="cuisine" class="form-select">
                    <option value="Malay" <?= $recipe['cuisine'] == 'Malay' ? 'selected' : '' ?>>Malay</option>
                    <option value="Chinese" <?= $recipe['cuisine'] == 'Chinese' ? 'selected' : '' ?>>Chinese</option>
                    <option value="Indian" <?= $recipe['cuisine'] == 'Indian' ? 'selected' : '' ?>>Indian</option>
                    <option value="Western" <?= $recipe['cuisine'] == 'Western' ? 'selected' : '' ?>>Western</option>
                    <option value="Other" <?= $recipe['cuisine'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ingredients</label>
                <textarea name="ingredients" class="form-control" rows="4"><?= htmlspecialchars($recipe['ingredients']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Steps</label>
                <textarea name="steps" class="form-control" rows="4"><?= htmlspecialchars($recipe['steps']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <img src="../uploads/<?= htmlspecialchars($recipe['image']) ?>" width="200" class="img-thumbnail mb-2"><br>
                <label class="form-label">Change Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="admin_recipe_index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
