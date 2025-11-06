<?php
session_start();
include 'db_connect.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styling.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            min-height: 100vh;
            padding-bottom: 60px;
            position: relative;
        }
        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.65);
            z-index: -1;
        }
        .container-content h1 {
            font-size: 2.5rem;
            text-shadow: 1px 1px 6px rgba(0, 0, 0, 0.4);
            color:rgb(65, 59, 59);
        }
        .container-content p {
            font-size: 1.1rem;
            color:rgba(0, 0, 0, 0.7);
        }
        .card {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s forwards;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
        }
        .card-body {
            padding: 30px;
        }
        .card-title {
            font-size: 1.5rem;
            color: #222;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        .card-text {
            color: #333;
            font-size: 1rem;
            margin-bottom: 20px;
        }
        .card .btn {
            font-weight: 600;
            padding: 10px;
            font-size: 1rem;
            border-radius: 8px;
        }
        .row > div:nth-child(1) .card { animation-delay: 0.1s; }
        .row > div:nth-child(2) .card { animation-delay: 0.2s; }
        .row > div:nth-child(3) .card { animation-delay: 0.3s; }
        .row > div:nth-child(4) .card { animation-delay: 0.4s; }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="bg-overlay"></div>

    <div class="container container-content py-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Welcome to Your Dashboard</h1>
            <p>Manage your recipes, meal plans, engage with the community, and participate in competitions with your self-made recipes</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Recipe -->
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">Recipe</h5>
                        <p class="card-text">Create and manage your recipes here and rate recipes with others</p>
                        <a href="RecipeModule/recipe_index.php" class="btn btn-primary w-100">Go to Recipe</a>
                    </div>
                </div>
            </div>

            <!-- Meal Plan -->
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">Meal Planning</h5>
                        <p class="card-text">Create meal plans and add recipes to your meal plans here</p>
                        <a href="MealModule/meal_plan_index.php" class="btn btn-success w-100">Go to Meal Plan</a>
                    </div>
                </div>
            </div>

            <!-- Community Engagement -->
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">Community Engagement</h5>
                        <p class="card-text">Engage in discussions by posting and viewing comments</p>
                        <a href="CommunityEngagementModule/discussion.php" class="btn btn-warning w-100">Go to Community Engagement</a>
                    </div>
                </div>
            </div>

            <!-- Cooking Competition -->
            <div class="col-md-6 col-lg-5">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <h5 class="card-title">Cooking Competition</h5>
                        <p class="card-text">Participate in cooking competitions using your own recipes</p>
                        <a href="CompetitionModule/competition.php" class="btn btn-info w-100">Go to Cooking Competition</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
