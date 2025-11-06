<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../db_connect.php';
$user_id = $_SESSION['user_id'];

// Retrieve flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Fetch user details (procedural)
$user = null;
$query = "SELECT username FROM users WHERE user_id = $user_id";
$result = mysqli_query($con, $query);
if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submission Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            background: #ffffff;
            max-width: 600px;
            margin: 80px auto;
        }
        .card h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #2e7d32;
        }
        .card p {
            font-size: 1.2rem;
            color: #616161;
        }
        .btn-primary {
            background-color: #2e7d32;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1rem;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #1b5e20;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .success-icon {
            font-size: 4rem;
            color: #2e7d32;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="card">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h2>Submission Successful!</h2>

        <?php if ($flash): ?>
            <p><?= htmlspecialchars($flash['message']) ?></p>
        <?php else: ?>
            <p>Your submission has been processed successfully.</p>
        <?php endif; ?>

        <?php if ($user): ?>
            <p>Welcome back, <?= htmlspecialchars($user['username']) ?>!</p>
        <?php endif; ?>

        <a href="competition.php" class="btn btn-primary mt-4">Back to Competition</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
