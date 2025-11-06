<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $cuisine = $_POST['cuisine'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if ($fileType === 'image/jpeg') {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = uniqid() . '.jpg';
            $targetPath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $query = "INSERT INTO recipes (name, ingredients, steps, cuisine, image, user_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssssi", $name, $ingredients, $steps, $cuisine, $imageName, $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: recipe_index.php");
                        exit();
                    } else {
                        $error = "Error inserting recipe: " . mysqli_error($con);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error = "Database error.";
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Only JPG/JPEG images are allowed.";
        }
    } else {
        $error = "Image is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">Add New Recipe</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Recipe Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ingredients</label>
                <textarea name="ingredients" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Preparation Steps</label>
                <textarea name="steps" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Cuisine</label>
                <select name="cuisine" class="form-select" required>
                    <option value="">-- Select Cuisine --</option>
                    <option>Malay</option>
                    <option>Chinese</option>
                    <option>Indian</option>
                    <option>Western</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Recipe Image (JPG only)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg" required>
            </div>
            <button type="submit" class="btn btn-success">Add Recipe</button>
            <a href="recipe_index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
