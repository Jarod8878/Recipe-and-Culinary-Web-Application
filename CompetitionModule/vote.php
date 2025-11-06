<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $competition_entry_id = intval($_POST['competition_entry_id']);

    $check_query = "SELECT * FROM competition_votes WHERE competition_entry_id = $competition_entry_id AND voter_id = $user_id";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'You have already voted for this entry.'];
    } else {
        $vote_query = "INSERT INTO competition_votes (competition_entry_id, voter_id) VALUES ($competition_entry_id, $user_id)";
        if (mysqli_query($con, $vote_query)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Your vote has been successfully submitted!'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to submit your vote. Please try again.'];
        }
    }
    header("Location: vote.php");
    exit();
}

$query = "
    SELECT ce.competition_entry_id, r.name AS recipe_name, r.ingredients, r.steps, r.image, 
           u.username AS submitter_name, COUNT(cv.vote_id) AS vote_count
    FROM competition_entries ce
    JOIN recipes r ON ce.recipe_id = r.recipe_id
    JOIN users u ON ce.submitter_id = u.user_id
    LEFT JOIN competition_votes cv ON ce.competition_entry_id = cv.competition_entry_id
    GROUP BY ce.competition_entry_id
    ORDER BY vote_count DESC, ce.submission_date ASC
";

$result = mysqli_query($con, $query);
$entries = mysqli_fetch_all($result, MYSQLI_ASSOC);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote for Recipes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
    <style>
        h2 {
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }
        .btn-vote {
            background-color: #ffc107;
            color: #212529;
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-vote:hover {
            background-color: #e0a800;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .popover-img {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            border-radius: 6px;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.97);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-img-top {
            width: 100%; /* Ensures the image spans the width of the card */
            height: 200px; /* Set a fixed height for all images */
            object-fit: cover; /* Ensures the image maintains its aspect ratio and fills the area */
            border-radius: 6px; /* Optional: keeps the rounded corners consistent */
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4 text-center">Vote for Your Favorite Recipes</h2>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> text-center">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (count($entries) > 0): ?>
            <div class="row">
                <?php foreach ($entries as $entry): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <img src="../uploads/<?= htmlspecialchars($entry['image']) ?>"
                                alt="<?= htmlspecialchars($entry['recipe_name']) ?>" class="card-img-top"
                                data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true"
                                data-bs-content="<img src='../uploads/<?= htmlspecialchars($entry['image']) ?>' class='popover-img'>">

                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($entry['recipe_name']) ?></h5>
                                <p class="card-text">
                                    <span data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true"
                                        data-bs-content="<?= htmlspecialchars($entry['ingredients']) ?>">
                                        <strong>Ingredients:</strong>
                                        <?= htmlspecialchars(substr($entry['ingredients'], 0, 30)) ?>...
                                    </span>
                                </p>
                                <p class="card-text">
                                    <span data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true"
                                        data-bs-content="<?= htmlspecialchars($entry['steps']) ?>">
                                        <strong>Steps:</strong> <?= htmlspecialchars(substr($entry['steps'], 0, 30)) ?>...
                                    </span>
                                </p>
                                <p class="card-text"><strong>Votes:</strong> <?= htmlspecialchars($entry['vote_count']) ?></p>
                                <form method="POST">
                                    <input type="hidden" name="competition_entry_id"
                                        value="<?= $entry['competition_entry_id'] ?>">
                                    <button type="submit" class="btn btn-vote">Vote</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No competition entries available for voting.</div>
        <?php endif; ?>

        <a href="competition.php" 
            class="btn btn-primary text-white mt-4 d-block mx-auto shadow-sm fw-semibold" 
            style="max-width: 220px; padding: 12px 20px; font-size: 1rem;">
            ← Back to Competition
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
            popovers.forEach(el => {
                new bootstrap.Popover(el, {
                    html: true
                });
            });
        });
    </script>
</body>

</html>