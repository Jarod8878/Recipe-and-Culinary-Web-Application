<?php
require 'db_connect.php';
$message = '';
$messageClass = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $email = mysqli_real_escape_string($con, $email);
    $username = mysqli_real_escape_string($con, $username);
    $hashed = mysqli_real_escape_string($con, $hashed);

    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($con, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Email already exists.";
        $messageClass = "danger";
    } else {
        $insert_sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed', 'user')";
        if (mysqli_query($con, $insert_sql)) {
            $message = "Registration successful. <a href='login.php' class='alert-link'>Login now</a>";
            $messageClass = "success";
        } else {
            $message = "Something went wrong. Please try again.";
            $messageClass = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styling.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand fw-bold text-white" href="#">Culinary App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Back to Login</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="login-container">
        <h2 class="form-title text-center">Register</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageClass ?>"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="mt-3 text-center">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</body>

</html>