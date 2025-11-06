<?php
include 'db_connect.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // Check if email exists
    $check_user = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check_user) > 0) {
        $user = mysqli_fetch_assoc($check_user);
        $user_id = $user['user_id']; // Fetch user_id

        // Generate reset token
        $token = bin2hex(random_bytes(50));

        // Insert token into password_resets table
        $insert = mysqli_query($con, "INSERT INTO password_resets (user_id, token, created_at) VALUES ('$user_id', '$token', NOW())");

        if ($insert) {
            // Redirect to reset page with token
            header("Location: reset_password.php?token=$token");
            exit();
        } else {
            $message = "Error inserting token.";
            $messageClass = "danger";
        }
    } else {
        $message = "Email not found.";
        $messageClass = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
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
            <h2 class="text-center">Forgot Password</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageClass ?> text-center"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Enter your email address:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset your password</button>
            </form>

            <p class="mt-3 text-center">
                Remembered your password?
                <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

</body>

</html>