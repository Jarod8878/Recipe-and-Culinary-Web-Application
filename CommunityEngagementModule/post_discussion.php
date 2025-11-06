<?php
session_start();
require '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$recipe_id = isset($_GET['recipe_id']) ? (int)$_GET['recipe_id'] : null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $user_id = $_SESSION['user_id'];

    if ($recipe_id) {
        $query = "INSERT INTO discussions (user_id, title, content, recipe_id) VALUES ($user_id, '$title', '$content', $recipe_id)";
    } else {
        $query = "INSERT INTO discussions (user_id, title, content) VALUES ($user_id, '$title', '$content')";
    }

    mysqli_query($con, $query);
    header("Location: " . ($recipe_id ? "../RecipeModule/recipe_view.php?id=$recipe_id" : "discussion.php"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Post Discussion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
    <style>
        .form-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin: 60px auto;
            max-width: 700px;
        }

        .form-title {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <div class="form-container">
            <h2 class="form-title mb-4">Post a New Discussion</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" id="title" placeholder="Enter discussion title" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="8" placeholder="Enter description here..." required></textarea>
                </div>

                <button type="submit" class="btn btn-success">Post Discussion</button>
                <a href="<?= $recipe_id ? "../RecipeModule/recipe_view.php?id=$recipe_id" : "discussion.php" ?>" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</body>

</html>
