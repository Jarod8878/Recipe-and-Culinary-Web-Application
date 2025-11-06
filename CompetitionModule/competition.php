<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cooking Competition</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../styling.css">
  <style>
    .btn {
      border-radius: 30px;
      font-size: 1.1rem;
      padding: 10px 20px;
    }

    .btn-primary {
      background-color: #6c757d;
      border: none;
    }
    .btn-primary:hover {
      background-color: #5a6268;
    }

    .btn-outline-secondary {
      border-color: #6c757d;
      color: #6c757d;
    }
    .btn-outline-secondary:hover {
      background-color: #6c757d;
      color: #ffffff;
    }

    .btn-success {
      background-color: #28a745;
      border: none;
    }
    .btn-success:hover {
      background-color: #218838;
    }

    .btn-warning {
      background-color: #ffc107;
      border: none;
    }
    .btn-warning:hover {
      background-color: #e0a800;
    }

    .d-grid .btn {
      transition: all 0.3s ease-in-out;
    }
    .d-grid .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>
<body>
  <?php include '../navbar.php'; ?>
  <div class="container text-center container-content">
    <h1 class="mb-4">Welcome to the Cooking Competition</h1>
    <p class="lead">Participate by submitting your recipes, viewing entries, voting for your favorites, or checking the results.</p>
    <div class="d-grid gap-4 col-6 mx-auto mt-5">
      <a href="submit_competition.php" class="btn btn-primary btn-lg">Submit Entry</a>
      <a href="competition_entries.php" class="btn btn-outline-secondary btn-lg">View Entries</a>
      <a href="vote.php" class="btn btn-success btn-lg">Vote for Recipes</a>
      <a href="competition_results.php" class="btn btn-warning btn-lg">View Results</a>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
