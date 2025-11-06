<?php
session_start();
include '../db_connect.php';
include '../navbar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['clear_likes'])) {
    $clearId = (int)$_GET['clear_likes'];
    mysqli_query($con, "DELETE FROM discussion_likes WHERE discussion_id = $clearId");
    header("Location: admin_discussion.php");
    exit();
}

$query = "SELECT d.*, u.username, r.name AS recipe_name, r.image AS recipe_image, r.recipe_id AS recipe_id,(SELECT COUNT(*) FROM discussion_likes l WHERE l.discussion_id = d.discussion_id) AS like_count FROM discussions d JOIN users u ON d.user_id = u.user_id LEFT JOIN recipes r ON d.recipe_id = r.recipe_id ORDER BY d.created_at DESC";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Discussions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container.container-content {
            max-width: 1400px;
        }
        .toggle-btn {
            cursor: pointer;
            width: 100%;
            text-align: left;
            padding: 12px 16px;
            background-color: #f1f1f1;
            border: none;
            font-weight: 500;
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }
        .toggle-btn:hover {
            background-color: #e2e6ea;
        }
        .collapsed-content {
            display: none;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        .tab-pane {
            background-color: #fcfcfc;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .recipe-image {
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
        .action-buttons .btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            margin: 2px;
        }
    </style>
</head>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">Manage Discussions</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Author</th>
                        <th>Recipe</th>
                        <th>Posted At</th>
                        <th>Avg Rating</th>
                        <th>Likes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                        $discussionId = $row['discussion_id'];
                        $recipeId = $row['recipe_id'];

                        $avgRating = '-';
                        if (!empty($recipeId)) {
                            $avgQuery = "SELECT AVG(rating_value) AS avg_rating FROM ratings WHERE recipe_id = $recipeId";
                            $avgResult = mysqli_query($con, $avgQuery);
                            if ($avgResult && mysqli_num_rows($avgResult) > 0) {
                                $avgData = mysqli_fetch_assoc($avgResult);
                                if ($avgData['avg_rating']) {
                                    $avgRating = round($avgData['avg_rating'], 1) . ' ★';
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['content']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <?php if (!empty($row['recipe_name'])): ?>
                                    <?= htmlspecialchars($row['recipe_name']) ?><br>
                                    <img src="../uploads/<?= htmlspecialchars($row['recipe_image']) ?>" class="recipe-image"
                                        style="max-width: 60px; height: auto;">
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td><?= $avgRating ?></td>
                            <td><?= $row['like_count'] ?? 0 ?></td>
                            <td class="text-center action-buttons">
                                <a href="admin_editDiscussion.php?id=<?= $discussionId ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="admin_deleteDiscussion.php?id=<?= $discussionId ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this discussion?')" title="Delete"><i class="bi bi-trash"></i></a>
                                <a href="admin_discussion.php?clear_likes=<?= $discussionId ?>" class="btn btn-outline-danger" onclick="return confirm('Clear all likes for this discussion?')" title="Clear Likes"><i class="bi bi-eraser"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="9" class="p-0">
                                <button class="toggle-btn w-100" onclick="toggleContent('content<?= $discussionId ?>')">
                                    <i class="bi bi-chevron-down me-2"></i> View Comments and Ratings
                                </button>
                                <div id="content<?= $discussionId ?>" class="collapsed-content p-3 border-bottom">
                                    <ul class="nav nav-tabs" id="tab<?= $discussionId ?>" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="comments-tab<?= $discussionId ?>" data-bs-toggle="tab" data-bs-target="#comments<?= $discussionId ?>" type="button" role="tab" aria-controls="comments<?= $discussionId ?>" aria-selected="true">Comments</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="ratings-tab<?= $discussionId ?>" data-bs-toggle="tab" data-bs-target="#ratings<?= $discussionId ?>" type="button" role="tab" aria-controls="ratings<?= $discussionId ?>" aria-selected="false">Ratings</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-3">
                                        <div class="tab-pane fade show active" id="comments<?= $discussionId ?>" role="tabpanel" aria-labelledby="comments-tab<?= $discussionId ?>">
                                            <?php
                                            $commentQuery = "SELECT c.comment_id, c.content, u.username FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.discussion_id = $discussionId ORDER BY c.created_at DESC";
                                            $comments = mysqli_query($con, $commentQuery);
                                            if (mysqli_num_rows($comments) > 0):
                                                while ($comment = mysqli_fetch_assoc($comments)):
                                                    $commentId = $comment['comment_id'];
                                                    $commentContent = htmlspecialchars($comment['content']);
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center border p-2 my-1 rounded">
                                                <div><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= $commentContent ?></div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button class="btn btn-sm text-dark" data-bs-toggle="modal" data-bs-target="#editCommentModal<?= $commentId ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <a href="admin_deleteComment.php?id=<?= $commentId ?>" class="text-danger" onclick="return confirm('Delete this comment?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="editCommentModal<?= $commentId ?>" tabindex="-1" aria-labelledby="editCommentModalLabel<?= $commentId ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="admin_updateComment.php">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editCommentModalLabel<?= $commentId ?>">Edit Comment</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                                                                <input type="text" name="content" class="form-control" value="<?= $commentContent ?>" required>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
                                                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i></button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; else: ?>
                                            <p class="text-muted">No comments yet.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tab-pane fade" id="ratings<?= $discussionId ?>" role="tabpanel" aria-labelledby="ratings-tab<?= $discussionId ?>">
                                            <?php
                                            if (!empty($recipeId)) {
                                                $ratingQuery = "SELECT r.rating_id, r.rating_value, r.created_at, u.username FROM ratings r JOIN users u ON r.user_id = u.user_id WHERE r.recipe_id = $recipeId ORDER BY r.created_at DESC";
                                                $ratings = mysqli_query($con, $ratingQuery);
                                                if (mysqli_num_rows($ratings) > 0):
                                                    while ($rating = mysqli_fetch_assoc($ratings)):
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center border p-2 my-1 rounded">
                                                <div><strong><?= htmlspecialchars($rating['username']) ?>:</strong> <?= $rating['rating_value'] ?> ★ (<?= $rating['created_at'] ?>)</div>
                                                <a href="admin_deleteRatings.php?id=<?= $rating['rating_id'] ?>" class="text-danger" onclick="return confirm('Delete this rating?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                            <?php endwhile; else: ?>
                                            <p class="text-muted">No ratings yet.</p>
                                            <?php endif; } else { ?>
                                                <p class="text-muted">No recipe linked.</p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No discussions found.</div>
        <?php endif; ?>
    </div>
    <script>
    function toggleContent(id) {
        const content = document.getElementById(id);
        const button = content.previousElementSibling;
        const icon = button.querySelector('i');
        
        if (content.style.display === "block") {
            content.style.display = "none";
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        } else {
            content.style.display = "block";
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        }
    }
    </script>
</body>
</html>