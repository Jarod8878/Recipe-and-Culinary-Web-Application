<?php
session_start();
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['rating'])) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $recipe_id = (int)$_POST['recipe_id'];
    $rating = (int)$_POST['rating'];

    if ($user_id > 0 && $rating >= 1 && $rating <= 5) {
        $query = "INSERT INTO ratings (user_id, recipe_id, rating_value) VALUES ($user_id, $recipe_id, $rating) ON DUPLICATE KEY UPDATE rating_value = $rating";
        mysqli_query($con, $query) or die("Error updating rating: " . mysqli_error($con));
    }
}
header("Location: ../RecipeModule/recipe_view.php?id=$recipe_id");
exit();
?>
