<?php
include '../db_connect.php';
include '../navbar.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../RecipeModule/recipe_index.php");
    exit();
}

$id = (int)$id; 
$sql = "DELETE FROM recipes WHERE recipe_id = $id";

if (mysqli_query($con, $sql)) {
    header("Location: admin_recipe_index.php");
    exit();
} else {
    echo "Error deleting recipe: " . mysqli_error($con);
}
?>
