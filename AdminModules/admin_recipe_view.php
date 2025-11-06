<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

$recipe_id = $_GET['id'] ?? null;
if (!$recipe_id) {
    header("Location: recipe_index.php");
    exit();
}

$query = "SELECT * FROM recipes WHERE recipe_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $recipe_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "Recipe not found.";
    exit();
}

$recipe = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Recipe - <?= htmlspecialchars($recipe['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
    

</head>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?= htmlspecialchars($recipe['name']) ?></h2>
        </div>

        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="../uploads/<?= htmlspecialchars($recipe['image'] ?? 'default.jpg') ?>" alt="Recipe Image" class="recipe-image">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($recipe['name']) ?></h5>
                        <p class="card-text"><strong>Cuisine:</strong> <?= htmlspecialchars($recipe['cuisine']) ?></p>
                        <p class="card-text"><strong>Ingredients:</strong></p>
                        <ul>
                            <?php foreach (explode(',', $recipe['ingredients']) as $ingredient): ?>
                                <li><?= htmlspecialchars(trim($ingredient)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="card-text"><strong>Steps:</strong></p>
                        <ol>
                            <?php foreach (explode("\n", $recipe['steps']) as $step): ?>
                                <li><?= htmlspecialchars(trim($step)) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-grid gap-2">

                    <a href="admin_recipe_index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back to Recipe List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
