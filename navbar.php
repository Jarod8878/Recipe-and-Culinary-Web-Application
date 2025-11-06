<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Culinary App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styling.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Culinary App</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['username'])): ?>
          
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Admin menu -->
            <li class="nav-item"> 
              <a class="nav-link" href="/assignment/AdminModules/admin_index.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/AdminModules/user_management.php">Manage Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/AdminModules/admin_recipe_index.php">Manage Recipes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/AdminModules/admin_meal_plans.php">Manage Meal Plans</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/AdminModules/admin_discussion.php">Manage Discussions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/AdminModules/manage_entries.php">Manage Competition</a>
            </li>
          <?php else: ?>
            <!-- User menu -->
            <li class="nav-item"> 
              <a class="nav-link" href="/assignment/index.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/RecipeModule/recipe_index.php">Recipe</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/MealModule/meal_plan_index.php">Meal Plans</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/CommunityEngagementModule/discussion.php">Discussions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/assignment/CompetitionModule/competition.php">Competition</a>
            </li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link text-danger" href="/assignment/logout.php">Logout</a>
          </li>

        <?php else: ?>
          <!-- Not logged in -->
          <li class="nav-item">
            <a class="nav-link" href="/assignment/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/assignment/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".nav-link");
    const navbarCollapse = document.querySelector(".navbar-collapse");

    navLinks.forEach(link => {
      link.addEventListener("click", function () {
        if (navbarCollapse.classList.contains("show")) {
          new bootstrap.Collapse(navbarCollapse).hide();
        }
      });
    });
  });
</script>
</body>
</html>
