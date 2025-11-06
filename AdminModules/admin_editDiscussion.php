<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$discussionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = $_SESSION['user_id'];
$error = '';
$title = '';
$content = '';
$recipeImage = '';
$recipeName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discussionId = intval($_POST['discussion_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title && $content) {
        $safeTitle = mysqli_real_escape_string($con, $title);
        $safeContent = mysqli_real_escape_string($con, $content);
        $discussionId = (int)$discussionId;
        $userId = (int)$userId;

        if ($_SESSION['role'] === 'admin') {
            $query = "UPDATE discussions SET title = '$safeTitle', content = '$safeContent' WHERE discussion_id = $discussionId";
        } else {
            $query = "UPDATE discussions SET title = '$safeTitle', content = '$safeContent' WHERE discussion_id = $discussionId AND user_id = $userId";
        }
        $result = mysqli_query($con, $query);
        if ($result) {
            header("Location: admin_discussion.php");
            exit();
        } else {
            $error = "Failed to execute update.";
        }
    } else {
        $error = "All fields are required.";
    }
} else {
    if ($_SESSION['role'] === 'admin') {
        $query = "SELECT d.*, r.name AS recipe_name, r.image AS recipe_image FROM discussions d LEFT JOIN recipes r ON d.recipe_id = r.recipe_id WHERE d.discussion_id = $discussionId";
    } else {
        $query = "SELECT d.*, r.name AS recipe_name, r.image AS recipe_image FROM discussions d LEFT JOIN recipes r ON d.recipe_id = r.recipe_id WHERE d.discussion_id = $discussionId AND d.user_id = $userId";
    }
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) === 1) {
        $discussion = mysqli_fetch_assoc($result);
        $title = $discussion['title'];
        $content = $discussion['content'];
        $recipeImage = $discussion['recipe_image'];
        $recipeName = $discussion['recipe_name'];
    } else {
        echo "Discussion not found or access denied.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Discussion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="bg-overlay"></div>
<div class="container container-content">
    <h3 class="mb-4 text-center">Edit Your Discussion</h3>

    <?php if (!empty($recipeImage)): ?>
        <div class="text-center mb-3">
            <img src="../uploads/<?= htmlspecialchars($recipeImage) ?>" alt="<?= htmlspecialchars($recipeName) ?>" class="img-fluid rounded" style="max-height: 250px;">
            <?php if (!empty($recipeName)): ?>
                <p class="text-muted mt-2"><em>Related Recipe: <?= htmlspecialchars($recipeName) ?></em></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="discussion_id" value="<?= $discussionId ?>">

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="title" required
                   value="<?= htmlspecialchars($title) ?>">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" id="content" rows="5" class="form-control" required><?= htmlspecialchars($content) ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="admin_discussion.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>
</body>
</html>
