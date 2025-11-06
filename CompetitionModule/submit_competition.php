<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';
$user_id = $_SESSION['user_id'];

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['use_existing'] === 'yes') {
        $recipe_id = (int) $_POST['recipe_id'];
        if (!$recipe_id) {
            setFlashMessage('danger', 'Please select a valid recipe.');
            header("Location: submission_success.php");
            exit();
        }

        $query = "INSERT INTO competition_entries (recipe_id, submitter_id) VALUES ($recipe_id, $user_id)";
        if (mysqli_query($con, $query)) {
            setFlashMessage('success', 'Your recipe has been successfully submitted!');
        } else {
            setFlashMessage('danger', 'Failed to submit your recipe: ' . mysqli_error($con));
        }

    } else {
        $name = $_POST['recipe_name'];
        $ingredients = $_POST['ingredients'];
        $steps = $_POST['steps'];
        $cuisine = $_POST['cuisine'];
        $image = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $validExt = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $validExt)) {
                setFlashMessage('danger', 'Invalid image format.');
                header("Location: submission_success.php");
                exit();
            }
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                setFlashMessage('danger', 'Image size must not exceed 2MB.');
                header("Location: submission_success.php");
                exit();
            }

            $image = uniqid() . '.' . $ext;
            $target = "../uploads/" . $image;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                setFlashMessage('danger', 'Failed to upload image.');
                header("Location: submission_success.php");
                exit();
            }
        }

        $query = "INSERT INTO recipes (name, ingredients, steps, image, cuisine, user_id) 
                  VALUES ('$name', '$ingredients', '$steps', '$image', '$cuisine', $user_id)";
        if (mysqli_query($con, $query)) {
            $recipe_id = mysqli_insert_id($con);
            $query = "INSERT INTO competition_entries (recipe_id, submitter_id) VALUES ($recipe_id, $user_id)";
            if (mysqli_query($con, $query)) {
                setFlashMessage('success', 'Your new recipe has been submitted!');
            } else {
                setFlashMessage('danger', 'Failed to enter the competition.');
            }
        } else {
            setFlashMessage('danger', 'Failed to create new recipe.');
        }
    }

    header("Location: submission_success.php");
    exit();
}

$query = "SELECT recipe_id, name FROM recipes WHERE user_id = $user_id";
$result = mysqli_query($con, $query);
$recipes = mysqli_fetch_all($result, MYSQLI_ASSOC);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Recipe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
    <style>
        .form-wrapper {
            max-width: 750px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="bg-overlay"></div>
<div class="container container-content">
    <div class="form-wrapper">
        <h2>Submit Your Recipe</h2>
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> text-center">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="recipeForm">
            <div class="form-group mb-4">
                <label>Do you want to use an existing recipe?</label>
                <div class="form-check">
                    <input type="radio" name="use_existing" value="yes" id="use_existing_yes" class="form-check-input" checked>
                    <label for="use_existing_yes" class="form-check-label">Yes</label>
                </div>
                <div class="form-check">
                    <input type="radio" name="use_existing" value="no" id="use_existing_no" class="form-check-input">
                    <label for="use_existing_no" class="form-check-label">No, create a new recipe</label>
                </div>
            </div>

            <div id="existing_recipe_section">
                <div class="form-group mb-4">
                    <label for="recipe_id">Select Your Recipe:</label>
                    <select name="recipe_id" id="recipe_id" class="form-select" required>
                        <option value="">-- Select a Recipe --</option>
                        <?php foreach ($recipes as $recipe): ?>
                            <option value="<?= $recipe['recipe_id'] ?>"><?= htmlspecialchars($recipe['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="new_recipe_section" style="display: none;">
                <div class="form-group mb-4">
                    <label for="recipe_name">Recipe Name:</label>
                    <input type="text" name="recipe_name" id="recipe_name" class="form-control">
                </div>
                <div class="form-group mb-4">
                    <label for="ingredients">Ingredients:</label>
                    <textarea name="ingredients" id="ingredients" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group mb-4">
                    <label for="steps">Steps:</label>
                    <textarea name="steps" id="steps" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Cuisine</label>
                    <select name="cuisine" class="form-select">
                        <option value="">-- Select Cuisine --</option>
                        <option>Malay</option>
                        <option>Chinese</option>
                        <option>Indian</option>
                        <option>Western</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label for="image">Upload Recipe Image:</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="competition.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    const radios = document.querySelectorAll('input[name="use_existing"]');
    const existing = document.getElementById('existing_recipe_section');
    const custom = document.getElementById('new_recipe_section');
    const recipeSelect = document.getElementById('recipe_id');

    function toggleSections() {
        const useExisting = document.getElementById('use_existing_yes').checked;
        existing.style.display = useExisting ? 'block' : 'none';
        recipeSelect.required = useExisting;
        custom.style.display = useExisting ? 'none' : 'block';
    }

    radios.forEach(r => r.addEventListener('change', toggleSections));
    toggleSections();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
