<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

$search = $_GET['search'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$user_id = $_GET['user_id'] ?? '';

$searchTerm = "%$search%";

$query = "
    SELECT recipes.*, users.username, users.user_id, users.email 
    FROM recipes 
    JOIN users ON recipes.user_id = users.user_id 
    WHERE (recipes.name LIKE ? OR recipes.cuisine LIKE ?)
";
$params = [$searchTerm, $searchTerm];
$types = "ss";

if ($cuisine) {
    $query .= " AND recipes.cuisine = ?";
    $params[] = $cuisine;
    $types .= "s";
}

if (!empty($user_id)) {
    $query .= " AND recipes.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

$query .= " ORDER BY recipes.recipe_id DESC LIMIT ? OFFSET ?";
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

$count_stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM recipes");
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
    <title>Admin - All Recipes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Recipe Management</h2>
        </div>

        <?php
        $user_query = "SELECT user_id, username FROM users WHERE role != 'admin'";
        $user_result = mysqli_query($con, $user_query);
        ?>

        <form method="GET" class="row g-3 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Recipe Name</label>
                <input type="text" name="search" class="form-control" placeholder="Search recipes..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cuisine</label>
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
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    <?php while ($user = mysqli_fetch_assoc($user_result)): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($user['user_id'] == $user_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['username']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-outline-secondary mt-2">Clear</a>
            </div>
        </form>

        <?php foreach ($recipes as $row): ?>
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="../uploads/<?= htmlspecialchars($row['image'] ?? 'default.jpg') ?>" class="recipe-image" alt="Recipe Image" class="recipe-image">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text"><strong>Cuisine:</strong> <?= htmlspecialchars($row['cuisine']) ?></p>
                            <p class="card-text text-muted"><strong>Owner:</strong> <?= htmlspecialchars($row['username']) ?> (<?= htmlspecialchars($row['email']) ?>)</p>
                            <div class="d-flex gap-2">
                            <a href="admin_recipe_view.php?id=<?= $row['recipe_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="admin_recipe_edit.php?id=<?= $row['recipe_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['recipe_id'] ?>">Delete</button>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal<?= $row['recipe_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['recipe_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel<?= $row['recipe_id'] ?>">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete the recipe "<strong><?= htmlspecialchars($row['name']) ?></strong>" by <strong><?= htmlspecialchars($row['username']) ?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="admin_recipe_delete.php?id=<?= $row['recipe_id'] ?>" class="btn btn-danger">Yes, Delete</a>
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
