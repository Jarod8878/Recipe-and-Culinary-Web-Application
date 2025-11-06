<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';

// Update winner
$query = "
    UPDATE competition_entries 
    SET status = 'winner' 
    WHERE competition_entry_id = (
        SELECT competition_entry_id 
        FROM competition_votes 
        GROUP BY competition_entry_id 
        ORDER BY COUNT(vote_id) DESC 
        LIMIT 1
    )
";
if (!mysqli_query($con, $query)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to update winner: ' . mysqli_error($con)];
}

// Fetch results
$query = "
    SELECT ce.competition_entry_id, r.name AS recipe_name, r.ingredients, r.steps, r.image, 
           u.username AS submitter_name, COUNT(cv.vote_id) AS vote_count, ce.status
    FROM competition_entries ce
    JOIN recipes r ON ce.recipe_id = r.recipe_id
    JOIN users u ON ce.submitter_id = u.user_id
    LEFT JOIN competition_votes cv ON ce.competition_entry_id = cv.competition_entry_id
    GROUP BY ce.competition_entry_id
    ORDER BY ce.status = 'winner' DESC, vote_count DESC, ce.submission_date ASC
";
$result = mysqli_query($con, $query);
$entries = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to fetch competition results: ' . mysqli_error($con)];
}

// Separate winner
$winner = null;
$other_entries = [];
foreach ($entries as $entry) {
    if ($entry['status'] === 'winner') {
        $winner = $entry;
    } else {
        $other_entries[] = $entry;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Competition Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styling.css">
    <style>
        .winner-card {
            background: linear-gradient(135deg, #28a745, #218838);
            color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            padding: 30px;
            text-align: center;
            margin-bottom: 40px;
            animation: winner-glow 2s infinite alternate, winner-bounce 1.5s infinite;
        }

        @keyframes winner-glow {
            from {
                box-shadow: 0 0 20px rgba(40, 167, 69, 0.5);
            }

            to {
                box-shadow: 0 0 40px rgba(40, 167, 69, 0.8);
            }
        }

        @keyframes winner-bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .winner-card img {
            border-radius: 15px;
            width: 250px;
            height: 250px;
            object-fit: cover;
            margin-bottom: 20px;
            animation: winner-zoom 3s infinite alternate;
        }

        @keyframes winner-zoom {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.1);
            }
        }

        .winner-card h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        .winner-card p {
            font-size: 1.2rem;
        }

        .entries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .competition-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .competition-card:hover {
            transform: translateY(-5px);
        }

        .card-image-container {
            height: 220px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .card-image {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
            border-radius: 8px;
        }

        .competition-card:hover .card-image {
            transform: scale(1.05);
        }

        .card-content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-title {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .card-meta {
            margin-top: auto;
            font-size: 0.9rem;
            border-top: 1px solid #e9ecef;
            padding-top: 10px;
        }

        .vote-count {
            color: #e74c3c;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include '../navbar.php'; ?>
    <div class="bg-overlay"></div>

    <div class="container container-content">
        <h2 class="text-center mb-4">Competition Results</h2>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> text-center">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if ($winner): ?>
            <div class="winner-card">
                <img src="../uploads/<?= htmlspecialchars($winner['image']) ?>"
                    alt="<?= htmlspecialchars($winner['recipe_name']) ?>">
                <h3>🏆 Winner: <?= htmlspecialchars($winner['recipe_name']) ?></h3>
                <p><strong>Submitter:</strong> <?= htmlspecialchars($winner['submitter_name']) ?></p>
                <p><strong>Votes:</strong> <?= htmlspecialchars($winner['vote_count']) ?></p>
            </div>
        <?php endif; ?>

        <div class="entries-grid">
            <?php foreach ($other_entries as $entry): ?>
                <div class="competition-card">
                    <div class="card-image-container">
                        <img src="../uploads/<?= htmlspecialchars($entry['image']) ?>"
                            alt="<?= htmlspecialchars($entry['recipe_name']) ?>" class="card-image">
                    </div>
                    <div class="card-content">
                        <h5 class="card-title"><?= htmlspecialchars($entry['recipe_name']) ?></h5>
                        <div class="card-meta">
                            <div><strong>By:</strong> <?= htmlspecialchars($entry['submitter_name']) ?></div>
                            <div class="vote-count"><?= htmlspecialchars($entry['vote_count']) ?> votes</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="competition.php" class="btn btn-primary mt-4 d-block mx-auto shadow fw-semibold text-white"
            style="max-width: 220px; padding: 12px 20px; border-radius: 30px;">
            ← Back to Competition
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>