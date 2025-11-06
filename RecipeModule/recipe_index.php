<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

$search = $_GET['search'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$searchTerm = "%$search%";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("User not logged in.");
}

$query = "SELECT recipes.*, users.username, AVG(ratings.rating_value) AS avg_rating, COUNT(ratings.rating_value) AS rating_count
          FROM recipes 
          JOIN users ON recipes.user_id = users.user_id 
          LEFT JOIN ratings ON recipes.recipe_id = ratings.recipe_id
          WHERE (recipes.name LIKE ? OR recipes.cuisine LIKE ?)";

$params = [$searchTerm, $searchTerm];
$types = "ss";

if ($cuisine) {
    $query .= " AND recipes.cuisine = ?";
    $params[] = $cuisine;
    $types .= "s";
}

$query .= " GROUP BY recipes.recipe_id";
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$recipes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recipes[] = $row;
}
mysqli_stmt_close($stmt);

$count_query = "SELECT COUNT(*) as count FROM recipes WHERE name LIKE ? OR cuisine LIKE ?";
$count_params = [$searchTerm, $searchTerm];
$count_types = "ss";

if ($cuisine) {
    $count_query .= " AND cuisine = ?";
    $count_params[] = $cuisine;
    $count_types .= "s";
}

$count_stmt = mysqli_prepare($con, $count_query);
mysqli_stmt_bind_param($count_stmt, $count_types, ...$count_params);
mysqli_stmt_execute($count_stmt);
mysqli_stmt_bind_result($count_stmt, $total);
mysqli_stmt_fetch($count_stmt);
mysqli_stmt_close($count_stmt);

$totalPages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Recipe List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
</head>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>All Recipes</h2>
            <div>
                <a href="recipe_create.php" class="btn btn-success">+ Add Recipe</a>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search recipes..."
                    value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="cuisine" class="form-select">
                    <option value="">All Cuisines</option>
                    <option value="Malay" <?= $cuisine == 'Malay' ? 'selected' : '' ?>>Malay</option>
                    <option value="Chinese" <?= $cuisine == 'Chinese' ? 'selected' : '' ?>>Chinese</option>
                    <option value="Indian" <?= $cuisine == 'Indian' ? 'selected' : '' ?>>Indian</option>
                    <option value="Western" <?= $cuisine == 'Western' ? 'selected' : '' ?>>Western</option>
                    <option value="Other" <?= $cuisine == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-3">
                <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>

        <?php foreach ($recipes as $row): ?>
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="../uploads/<?= htmlspecialchars($row['image'] ?? 'default.jpg') ?>" class="recipe-image"
                            alt="Recipe Image">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text"><strong>Cuisine:</strong> <?= htmlspecialchars($row['cuisine']) ?></p>
                            <p class="card-text"><strong>Author:</strong> <?= htmlspecialchars($row['username']) ?></p>
                            <p class="card-text">
                                <strong>Rating:</strong>
                                <?php if ($row['avg_rating'] !== null): ?>
                                    <?= round($row['avg_rating'], 1) ?>
                                    <?php
                                    $full = floor($row['avg_rating']);
                                    $half = ($row['avg_rating'] - $full >= 0.5);
                                    $empty = 5 - $full - ($half ? 1 : 0);

                                    for ($i = 0; $i < $full; $i++) echo '<i class="bi bi-star-fill text-warning"></i>';
                                    if ($half) echo '<i class="bi bi-star-half text-warning"></i>';
                                    for ($i = 0; $i < $empty; $i++) echo '<i class="bi bi-star text-warning"></i>';
                                    ?>
                                    <span class="text-muted ms-2">
                                        (<?= $row['rating_count'] ?> rating<?= $row['rating_count'] == 1 ? '' : 's' ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">No ratings yet</span>
                                <?php endif; ?>
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="../MealModule/meal_plan_add_from_recipe.php?recipe_id=<?= $row['recipe_id'] ?>" class="btn btn-success btn-sm me-2">Add to Meal Plan</a>
                                <a href="recipe_view.php?id=<?= $row['recipe_id'] ?>" class="btn btn-primary btn-sm me-2">View</a>
                                <?php if ($row['user_id'] == $user_id): ?>
                                    <a href="recipe_edit.php?id=<?= $row['recipe_id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                                    <button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['recipe_id'] ?>">Delete</button>
                                <?php endif; ?>
                            </div>

                            <div class="modal fade" id="deleteModal<?= $row['recipe_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['recipe_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel<?= $row['recipe_id'] ?>">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete the recipe "<strong><?= htmlspecialchars($row['name']) ?></strong>"?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="recipe_delete.php?id=<?= $row['recipe_id'] ?>" class="btn btn-danger">Yes</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&cuisine=<?= urlencode($cuisine) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
