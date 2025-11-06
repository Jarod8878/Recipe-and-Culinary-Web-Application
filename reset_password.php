<?php
require('db_connect.php');

$message = "";
$messageClass = "";

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($con, $_GET['token']);

    $query = "SELECT * FROM password_resets WHERE token='$token' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $email = $row['email'];

        if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['password'])) {
            $password = mysqli_real_escape_string($con, $_POST['password']);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            mysqli_query($con, "UPDATE users SET password='$hashed_password' WHERE email='$email'");
            mysqli_query($con, "DELETE FROM password_resets WHERE email='$email'");

            $message = "Password reset successful! You can now log in.";
            $messageClass = "success";
        }
    } else {
        $message = "Invalid or expired token.";
        $messageClass = "danger";
    }
} else {
    $message = "No reset token provided.";
    $messageClass = "danger";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
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
    <div class="container mt-5">
        <div class="login-container">
            <h2 class="text-center mb-4">Reset Password</h2>
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageClass ?> text-center"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($messageClass !== "success"): ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
            <?php endif; ?>

            <p class="mt-3 text-center">
                <a href="login.php">Back to Login</a>
            </p>
        </div>

    </div>
</body>

</html>