<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require '../db_connect.php';
include '../navbar.php';

function renderStars($rating, $maxStars = 5)
{
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars >= 0.5);
    $emptyStars = $maxStars - $fullStars - ($halfStar ? 1 : 0);

    $html = '';
    for ($i = 0; $i < $fullStars; $i++)
        $html .= '<i class="bi bi-star-fill text-warning"></i>';
    if ($halfStar)
        $html .= '<i class="bi bi-star-half text-warning"></i>';
    for ($i = 0; $i < $emptyStars; $i++)
        $html .= '<i class="bi bi-star text-warning"></i>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Discussions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styling.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        span.fw-semibold {
            font-size: 1rem;
            color: #333;
        }
    </style>
</head>
<script>
    function editComment(commentId, content) {
        const container = document.getElementById('comment-' + commentId);
        const formHTML = `
        <form method="POST" action="update_comment.php" class="w-100">
            <input type="hidden" name="comment_id" value="${commentId}">
            <div class="d-flex align-items-center gap-2">
                <input type="text" name="content" class="form-control form-control-sm" value="${content}" required>
                <button type="submit" class="btn btn-outline-primary btn-sm" title="Save"><i class="bi bi-check-lg"></i></button>
                <button type="button" class="btn btn-outline-danger btn-sm" title="Cancel" onclick="location.reload()"><i class="bi bi-x-lg"></i></button>
            </div>
        </form>`;
        container.innerHTML = formHTML;
    }
</script>

<body>
    <div class="bg-overlay"></div>
    <div class="container container-content">
        <h2 class="mb-4">Discussions</h2>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-semibold">Sort By</span>
            <div>
                <a href="discussion.php?sort=latest"
                    class="btn btn-outline-primary btn-sm me-2 <?= (!isset($_GET['sort']) || $_GET['sort'] === 'latest') ? 'active' : '' ?>">MostRecent
                </a>
                <a href="discussion.php?sort=comments"
                    class="btn btn-outline-primary btn-sm <?= (isset($_GET['sort']) && $_GET['sort'] === 'comments') ? 'active' : '' ?>">Most Comments
                </a>
                <a href="discussion.php?sort=rating"
                    class="btn btn-outline-primary btn-sm <?= (isset($_GET['sort']) && $_GET['sort'] === 'rating') ? 'active' : '' ?>">Highest Rating
                </a>
                <a href="discussion.php?sort=like"
                    class="btn btn-outline-primary btn-sm <?= (isset($_GET['sort']) && $_GET['sort'] === 'like') ? 'active' : '' ?>">Most Likes
                </a>

            </div>
        </div>
        <div class="row">
            <?php
            $sort = $_GET['sort'] ?? 'latest';
            $userId = $_SESSION['user_id'];
            $query = "SELECT d.*, u.username, r.name AS recipe_name, r.image AS recipe_image, r.recipe_id AS recipe_id,
                 (SELECT COUNT(*) FROM comments c WHERE c.discussion_id = d.discussion_id) AS comment_count,
                 (SELECT AVG(rt.rating_value) FROM ratings rt WHERE rt.recipe_id = d.recipe_id) AS avg_rating,
                 (SELECT COUNT(*) FROM discussion_likes l WHERE l.discussion_id = d.discussion_id) AS like_count,
                 (SELECT COUNT(*) FROM discussion_likes l WHERE l.discussion_id = d.discussion_id AND l.user_id = $userId) AS liked_by_user
          FROM discussions d 
          JOIN users u ON d.user_id = u.user_id 
          LEFT JOIN recipes r ON d.recipe_id = r.recipe_id";

            switch ($sort) {
                case 'comments':
                    $query .= " ORDER BY comment_count DESC, d.created_at DESC";
                    break;
                case 'rating':
                    $query .= " ORDER BY avg_rating DESC, d.created_at DESC";
                    break;
                case 'like': 
                    $query .= " ORDER BY like_count DESC, d.created_at DESC"; 
                    break;
                default:
                    $query .= " ORDER BY d.created_at DESC";
                    break;
            }

            $result = mysqli_query($con, $query);

            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $discussionId = $row['discussion_id'];
                    $modalId = "modal_" . $discussionId;
                    $avgRating = null;
                    $totalRatings = 0;

                    if (!empty($row['recipe_id'])) {
                        $recipeId = (int) $row['recipe_id'];
                        $ratingQuery = "SELECT AVG(rating_value) as avg_rating, COUNT(*) as total FROM ratings WHERE recipe_id = $recipeId";
                        $ratingResult = mysqli_query($con, $ratingQuery);
                        if ($ratingResult && mysqli_num_rows($ratingResult) > 0) {
                            $ratingData = mysqli_fetch_assoc($ratingResult);
                            $avgRating = $ratingData['avg_rating'];
                            $totalRatings = $ratingData['total'];
                        }
                    }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm rounded">
                            <?php if (!empty($row['recipe_image'])): ?>
                                <div class="ratio ratio-16x9 rounded-top overflow-hidden">
                                    <img src="../uploads/<?= htmlspecialchars($row['recipe_image']) ?>" class="card-img-top"
                                        alt="<?= htmlspecialchars($row['recipe_name']) ?>" style="object-fit: cover;">
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column text-center">
                                <h6 class="card-title mb-2"><?= htmlspecialchars($row['title']) ?></h6>
                                <?php if ($avgRating !== null): ?>
                                    <p class="mb-2 small text-muted"><?= renderStars(round($avgRating, 1)) ?> (<?= $totalRatings ?>)
                                    </p>
                                <?php endif; ?>
                                <p class="mb-2 small text-muted">
                                    <i class="bi bi-chat-left-text me-1"></i><?= $row['comment_count'] ?>
                                    comment<?= $row['comment_count'] != 1 ? 's' : '' ?>
                                </p>
                                <div class="d-grid gap-2 mt-auto">
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#<?= $modalId ?>">
                                        View Details
                                    </button>
                                    <button
                                        class="btn btn-sm <?= $row['liked_by_user'] ? 'btn-danger' : 'btn-outline-danger' ?> like-btn"
                                        data-id="<?= $discussionId ?>">
                                        <i class="bi bi-heart<?= $row['liked_by_user'] ? '-fill' : '' ?>"></i>
                                        <span class="like-count"><?= $row['like_count'] ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content position-relative">
                                <button type="button"
                                    class="btn-close position-absolute top-0 end-0 m-2 z-3 bg-white rounded-circle shadow-sm"
                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-body p-0 d-flex" style="height: 500px;">
                                    <div class="col-md-6 p-0">
                                        <img src="../uploads/<?= htmlspecialchars($row['recipe_image']) ?>"
                                            alt="<?= htmlspecialchars($row['recipe_name']) ?>" class="img-fluid h-100 w-100"
                                            style="object-fit: cover; border-radius: 0.3rem 0 0 0.3rem;">
                                    </div>
                                    <div class="col-md-6 d-flex flex-column justify-content-between p-3"
                                        style="overflow-y: auto;">
                                        <div>
                                            <h5 class="fw-bold mb-2"><?= htmlspecialchars($row['title']) ?></h5>
                                            <small class="text-muted d-block mb-2">By <?= htmlspecialchars($row['username']) ?>
                                                on <?= $row['created_at'] ?></small>
                                            <p class="mb-3"><?= nl2br(htmlspecialchars($row['content'])) ?></p>

                                            <?php if ($_SESSION['user_id'] == $row['user_id']): ?>
                                                <div class="d-flex gap-2 mb-3">
                                                    <a href="edit_discussion.php?id=<?= $discussionId ?>"
                                                        class="btn btn-sm text-dark" title="Edit"><i
                                                            class="bi bi-pencil-square"></i></a>
                                                    <a href="deleteDiscussion.php?id=<?= $discussionId ?>"
                                                        class="btn btn-sm text-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this discussion?')"><i
                                                            class="bi bi-trash"></i></a>
                                                </div>
                                            <?php endif; ?>

                                            <div class="border-top pt-2 mt-2">
                                                <h6 class="fw-semibold">Comments</h6>
                                                <?php
                                                $commentQuery = "SELECT c.comment_id, c.content, u.username, c.created_at 
                                                     FROM comments c 
                                                     JOIN users u ON c.user_id = u.user_id 
                                                     WHERE c.discussion_id = $discussionId 
                                                     ORDER BY c.created_at DESC";
                                                $commentResult = mysqli_query($con, $commentQuery);

                                                if (mysqli_num_rows($commentResult) > 0):
                                                    while ($comment = mysqli_fetch_assoc($commentResult)):
                                                        ?>
                                                        <div class="mb-2 d-flex justify-content-between align-items-center"
                                                            id="comment-<?= $comment['comment_id'] ?>">
                                                            <div><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <span
                                                                    class="comment-content"><?= htmlspecialchars($comment['content']) ?></span>
                                                            </div>
                                                            <?php if ($_SESSION['username'] == $comment['username']): ?>
                                                                <div class="ms-2 d-flex align-items-center">
                                                                    <a href="#" class="text-dark me-2" title="Edit"
                                                                        onclick='editComment(<?= $comment['comment_id'] ?>, <?= json_encode($comment['content']) ?>); return false;'><i
                                                                            class="bi bi-pencil-square"></i></a>
                                                                    <a href="deleteComment.php?id=<?= $comment['comment_id'] ?>"
                                                                        class="text-danger" title="Delete"
                                                                        onclick="return confirm('Delete this comment?')"><i
                                                                            class="bi bi-trash"></i></a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endwhile; else: ?>
                                                    <p class="text-muted">No comments yet. Be the first!</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <form method="POST" action="comment.php" class="mt-3">
                                            <input type="hidden" name="discussion_id" value="<?= $discussionId ?>">
                                            <textarea name="comment_text" class="form-control mb-2" rows="2"
                                                placeholder="Add a comment..." required></textarea>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">Post Comment</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                <div class="alert alert-info">No discussions posted yet. Be the first to <a href="post_discussion.php">start
                        one</a>!</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['open'])): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                const modalId = "<?= htmlspecialchars($_GET['open']) ?>";
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });


        </script>
    <?php endif; ?>
    <script>
        document.querySelectorAll('.like-btn').forEach(button => {
            button.addEventListener('click', function () {
                const discussionId = this.dataset.id;
                const btn = this;

                fetch('like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `discussion_id=${discussionId}`
                })
                    .then(res => res.json())
                    .then(data => {
                        const icon = btn.querySelector('i');
                        const countSpan = btn.querySelector('.like-count');
                        let count = parseInt(countSpan.innerText);

                        if (data.liked) {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill');
                            btn.classList.remove('btn-outline-danger');
                            btn.classList.add('btn-danger');
                            countSpan.innerText = count + 1;
                        } else {
                            icon.classList.remove('bi-heart-fill');
                            icon.classList.add('bi-heart');
                            btn.classList.remove('btn-danger');
                            btn.classList.add('btn-outline-danger');
                            countSpan.innerText = count - 1;
                        }
                    });
            });
        });
    </script>
</body>

</html>