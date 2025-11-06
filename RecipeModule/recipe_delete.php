<?php
include '../db_connect.php';
include '../navbar.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: recipe_index.php");
    exit();
}

$query = "DELETE FROM recipes WHERE recipe_id = $id";
if (mysqli_query($con, $query)) {
    header("Location: recipe_index.php");
    exit();
} else {
    echo "Error deleting recipe: " . mysqli_error($con);
}
?>
