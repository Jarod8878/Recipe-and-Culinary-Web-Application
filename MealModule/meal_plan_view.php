<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Meal plan ID not provided.");
}

$plan_id = (int) $_GET['id'];

// Handle delete
if (isset($_GET['delete'])) {
    $entry_id = (int) $_GET['delete'];
    $query = "DELETE FROM meal_entries WHERE meal_entries_id = $entry_id";
    mysqli_query($con, $query);
    header("Location: meal_plan_view.php?id=$plan_id");
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['meal_date'];
    $type = $_POST['meal_type'];
    $recipe_id = !empty($_POST['recipe_id']) ? (int) $_POST['recipe_id'] : null;
    $custom = $_POST['custom_entry'];
    $entry_id = isset($_POST['meal_entries_id']) ? (int) $_POST['meal_entries_id'] : null;

    if ($recipe_id && $custom) {
        die("Please select either a recipe or enter a custom meal — not both.");
    }

    if ($entry_id) {
        $query = "UPDATE meal_entries 
                  SET meal_date='" . mysqli_real_escape_string($con, $date) . "', 
                      meal_type='" . mysqli_real_escape_string($con, $type) . "', 
                      recipe_id=" . ($recipe_id ?? "NULL") . ", 
                      custom_entry='" . mysqli_real_escape_string($con, $custom) . "' 
                  WHERE meal_entries_id=$entry_id";
    } else {
        $query = "INSERT INTO meal_entries (plan_id, meal_date, meal_type, recipe_id, custom_entry) 
                  VALUES ($plan_id, '" . mysqli_real_escape_string($con, $date) . "', 
                          '" . mysqli_real_escape_string($con, $type) . "', 
                          " . ($recipe_id ?? "NULL") . ", 
                          '" . mysqli_real_escape_string($con, $custom) . "')";
    }

    mysqli_query($con, $query);
    header("Location: meal_plan_view.php?id=$plan_id");
    exit();
}

// Recipe map
$query = "SELECT recipe_id, name FROM recipes";
$result = mysqli_query($con, $query);
$recipeMap = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recipeMap[$row['recipe_id']] = $row['name'];
}

// Filtering and pagination
$filter_query = "SELECT * FROM meal_entries WHERE plan_id = $plan_id";

if (!empty($_GET['filter_date'])) {
    $filter_query .= " AND meal_date = '" . mysqli_real_escape_string($con, $_GET['filter_date']) . "'";
}
if (!empty($_GET['filter_type'])) {
    $filter_query .= " AND meal_type = '" . mysqli_real_escape_string($con, $_GET['filter_type']) . "'";
}

$filter_query .= " ORDER BY meal_date, FIELD(meal_type, 'Breakfast', 'Lunch', 'Dinner', 'Snack')";

// Pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$total_result = mysqli_query($con, $filter_query);
$total_entries = mysqli_num_rows($total_result);
$total_pages = ceil($total_entries / $per_page);

$paginated_query = $filter_query . " LIMIT $per_page OFFSET $offset";
$entries = mysqli_query($con, $paginated_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meal Plan View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
    <div class="bg-overlay"></div>

    <div class="container container-content py-5">
        <h2 class="mb-4 fw-bold text-dark">Meal Plan Entries</h2>

        <form class="row g-3 mb-4" method="get">
            <input type="hidden" name="id" value="<?= $plan_id ?>">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date:</label>
                <input type="date" name="filter_date" class="form-control" value="<?= $_GET['filter_date'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Meal Type:</label>
                <select name="filter_type" class="form-select">
                    <option value="">All</option>
                    <?php foreach (['Breakfast', 'Lunch', 'Dinner', 'Snack'] as $type): ?>
                        <option value="<?= $type ?>" <?= ($_GET['filter_type'] ?? '') === $type ? 'selected' : '' ?>>
                            <?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="meal_plan_view.php?id=<?= $plan_id ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover align-middle rounded bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Meal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($entries)): ?>
                        <?php
                        $meal = '-';
                        if ($row['recipe_id']) {
                            $meal = $recipeMap[$row['recipe_id']] ?? 'Recipe not found';
                        } elseif (!empty($row['custom_entry'])) {
                            $meal = $row['custom_entry'];
                        }
                        ?>
                        <tr data-entry='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8") ?>'>
                            <td><?= htmlspecialchars($row['meal_date']) ?></td>
                            <td><?= htmlspecialchars($row['meal_type']) ?></td>
                            <td><?= htmlspecialchars($meal) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-warning edit-btn">Edit</button>
                                    <a href="?id=<?= $plan_id ?>&delete=<?= $row['meal_entries_id'] ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this entry?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?id=<?= $plan_id ?>&page=<?= $p ?>&filter_date=<?= $_GET['filter_date'] ?? '' ?>&filter_type=<?= $_GET['filter_type'] ?? '' ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <h4 id="formTitle" class="mb-3">Add Meal Entry</h4>
        <form method="post" id="mealEntryForm" class="card p-4 mb-4 bg-light">
            <input type="hidden" name="meal_entries_id" id="meal_entries_id">
            <div class="mb-3">
                <label class="form-label fw-semibold">Date:</label>
                <input type="date" name="meal_date" id="meal_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Meal Type:</label>
                <select name="meal_type" id="meal_type" class="form-select" required>
                    <option>Breakfast</option>
                    <option>Lunch</option>
                    <option>Dinner</option>
                    <option>Snack</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Recipe:</label>
                <select name="recipe_id" id="recipe_id" class="form-select">
                    <option value="">-- Select Recipe --</option>
                    <?php foreach ($recipeMap as $rid => $name): ?>
                        <option value="<?= $rid ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Or write custom meal:</label>
                <input type="text" name="custom_entry" id="custom_entry" class="form-control" placeholder="e.g., Fried Rice">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancel</button>
            </div>
        </form>

        <p class="mt-4"><a href="meal_plan_index.php" class="text-decoration-none">&larr; Back to My Meal Plans</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const entry = JSON.parse(this.closest('tr').dataset.entry);
                    document.getElementById('formTitle').innerText = "Edit Meal Entry";
                    document.getElementById('meal_entries_id').value = entry.meal_entries_id;
                    document.getElementById('meal_date').value = entry.meal_date;
                    document.getElementById('meal_type').value = entry.meal_type;
                    document.getElementById('recipe_id').value = entry.recipe_id || "";
                    document.getElementById('custom_entry').value = entry.custom_entry || "";
                });
            });

            document.getElementById('recipe_id').addEventListener('change', function () {
                if (this.value) {
                    document.getElementById('custom_entry').value = '';
                }
            });

            document.getElementById('custom_entry').addEventListener('input', function () {
                if (this.value.trim() !== '') {
                    document.getElementById('recipe_id').value = '';
                }
            });
        });

        function resetForm() {
            document.getElementById('formTitle').innerText = "Add Meal Entry";
            document.getElementById('meal_entries_id').value = "";
            document.getElementById('mealEntryForm').reset();
        }
    </script>
</body>
</html>
