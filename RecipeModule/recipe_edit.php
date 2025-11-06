<?php
include '../db_connect.php';
include '../navbar.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: recipe_index.php");
    exit();
}

$query = "SELECT * FROM recipes WHERE recipe_id = $id";
$result = mysqli_query($con, $query);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    echo "Recipe not found.";
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $cuisine = $_POST['cuisine'];
    $imageName = $recipe['image']; // Default: keep existing image

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if ($fileType === 'image/jpeg') {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = uniqid() . '.jpg';
            $targetPath = $uploadDir . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
        } else {
            $error = "Only JPG/JPEG images are allowed.";
        }
    }

    if (empty($error)) {
        $updateQuery = "UPDATE recipes SET name=?, ingredients=?, steps=?, cuisine=?, image=? WHERE recipe_id=?";
        $stmt = mysqli_prepare($con, $updateQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $ingredients, $steps, $cuisine, $imageName, $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: recipe_index.php");
                exit();
            } else {
                $error = "Error updating recipe: " . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error.";
        }
    }
}
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
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Recipe Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($recipe['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ingredients</label>
                <textarea name="ingredients" class="form-control" rows="3" required><?= htmlspecialchars($recipe['ingredients']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Preparation Steps</label>
                <textarea name="steps" class="form-control" rows="4" required><?= htmlspecialchars($recipe['steps']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Cuisine</label>
                <select name="cuisine" class="form-select">
                    <option <?= $recipe['cuisine'] === 'Malay' ? 'selected' : '' ?>>Malay</option>
                    <option <?= $recipe['cuisine'] === 'Chinese' ? 'selected' : '' ?>>Chinese</option>
                    <option <?= $recipe['cuisine'] === 'Indian' ? 'selected' : '' ?>>Indian</option>
                    <option <?= $recipe['cuisine'] === 'Western' ? 'selected' : '' ?>>Western</option>
                    <option <?= $recipe['cuisine'] === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <?php if (!empty($recipe['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($recipe['image']) ?>" alt="Recipe Image" class="recipe-image">
                <?php else: ?>
                    <p class="text-muted">No image available</p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload New Image (JPG only)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg">
                <div class="form-text">Leave empty to keep current image.</div>
            </div>
            <button type="submit" class="btn btn-primary">Update Recipe</button>
            <a href="recipe_index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
