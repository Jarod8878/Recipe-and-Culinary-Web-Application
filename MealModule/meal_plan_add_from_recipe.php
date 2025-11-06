<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['recipe_id'])) {
    die("Recipe ID is required.");
}

$recipe_id = (int)$_GET['recipe_id'];

// Get recipe name
$recipe_sql = "SELECT name FROM recipes WHERE recipe_id = $recipe_id";
$recipe_result = mysqli_query($con, $recipe_sql);
$recipe_row = mysqli_fetch_assoc($recipe_result);
$recipe_name = $recipe_row['name'] ?? null;

if (!$recipe_name) {
    die("Recipe not found.");
}

// Handle meal plan creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_plan'])) {
    $plan_name = mysqli_real_escape_string($con, $_POST['new_plan_name']);
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);

    $insert_plan_sql = "INSERT INTO meal_plans (user_id, plan_name, start_date, end_date) 
                        VALUES ('$user_id', '$plan_name', '$start_date', '$end_date')";
    mysqli_query($con, $insert_plan_sql);

    // Redirect to the same page to refresh meal plan list without triggering recipe add logic
    header("Location: meal_plan_add_from_recipe.php?recipe_id=$recipe_id&created=1");
    exit();
}

// Fetch user's meal plans
$plans_sql = "SELECT meal_plans_id, plan_name FROM meal_plans WHERE user_id = $user_id";
$plans_result = mysqli_query($con, $plans_sql);

// Handle adding recipe to meal plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe'])) {
    $plan_id = (int)$_POST['plan_id'];
    $meal_date = mysqli_real_escape_string($con, $_POST['meal_date']);
    $meal_type = mysqli_real_escape_string($con, $_POST['meal_type']);

    $insert_sql = "INSERT INTO meal_entries (plan_id, meal_date, meal_type, recipe_id)
                   VALUES ('$plan_id', '$meal_date', '$meal_type', '$recipe_id')";
    mysqli_query($con, $insert_sql);

    header("Location: meal_plan_view.php?id=$plan_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe to Meal Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
<div class="container container-content py-4">
    <h2>Add "<em><?= htmlspecialchars($recipe_name) ?></em>" to a Meal Plan</h2>

    <!-- Add Recipe to Meal Plan Form -->
    <form method="post" class="form-box mt-4">
        <input type="hidden" name="add_recipe" value="1">

        <div class="mb-3">
            <label for="plan_id" class="form-label">Select Meal Plan:</label>
            <select name="plan_id" id="plan_id" class="form-select" required>
                <option value="">-- Choose a plan --</option>
                <?php while ($plan = mysqli_fetch_assoc($plans_result)): ?>
                    <option value="<?= $plan['meal_plans_id'] ?>"><?= htmlspecialchars($plan['plan_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <p>Don't have a meal plan?
            <a class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" href="#createPlanForm" role="button" aria-expanded="false" aria-controls="createPlanForm">
                Create One
            </a>
        </p>

        <div class="mb-3 mt-4">
            <label for="meal_date" class="form-label">Meal Date:</label>
            <input type="date" name="meal_date" id="meal_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="meal_type" class="form-label">Meal Type:</label>
            <select name="meal_type" id="meal_type" class="form-select" required>
                <option value="Breakfast">Breakfast</option>
                <option value="Lunch">Lunch</option>
                <option value="Dinner">Dinner</option>
                <option value="Snack">Snack</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add to Meal Plan</button>
        <a href="../RecipeModule/recipe_index.php" class="btn btn-secondary">Cancel</a>
    </form>

    <!-- Create Meal Plan Form (Separate) -->
    <div class="collapse mt-4" id="createPlanForm">
        <div class="card card-body">
            <form method="post">
                <input type="hidden" name="create_plan" value="1">

                <div class="mb-2">
                    <label for="new_plan_name" class="form-label">Plan Name:</label>
                    <input type="text" class="form-control" name="new_plan_name" id="new_plan_name" required>
                </div>
                <div class="mb-2">
                    <label for="start_date" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" name="start_date" id="start_date" required>
                </div>
                <div class="mb-2">
                    <label for="end_date" class="form-label">End Date:</label>
                    <input type="date" class="form-control" name="end_date" id="end_date" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Plan</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_GET['created'])): ?>
<script>
    const planCollapse = new bootstrap.Collapse(document.getElementById('createPlanForm'), { toggle: false });
    planCollapse.hide();
</script>
<?php endif; ?>
</body>
</html>
