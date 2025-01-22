<?php

require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

// Validate blog ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid blog ID.";
    exit;
}

$blog_id = intval($_GET['id']);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $query = "INSERT INTO comments (blog_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $blog_id, $_SESSION['user_id'], $comment);
        if ($stmt->execute()) {
            echo "<script>alert('Comment added successfully!');</script>";
        } else {
            echo "<script>alert('Failed to add comment.');</script>";
        }
        $stmt->close();
    }
}

// Fetch blog comments
$query = "SELECT c.comment, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.blog_id = ? ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - Blog Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #333;
        }
        .comments-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .comment-form textarea {
            resize: none;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .comment-form button {
            background-color: #6c63ff;
            border-color: #6c63ff;
            transition: all 0.3s;
        }
        .comment-form button:hover {
            background-color: #5848e7;
            border-color: #5848e7;
        }
        .comment {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        .comment p {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #555;
        }
        .comment-meta {
            font-size: 0.85rem;
            color: #777;
        }
        .comment-meta strong {
            color: #333;
        }
        h5 {
            color: #444;
            font-weight: 600;
        }
        hr {
            margin: 1.5rem 0;
            border: none;
            border-top: 1px solid #e1e1e1;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="comments-container">
        <h5>Leave a Comment</h5>
        <form method="POST" class="comment-form mb-4">
            <textarea class="form-control mb-2" name="comment" rows="4" placeholder="Write your comment here..." required></textarea>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <hr>
        <h5>Comments</h5>
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><?= htmlspecialchars($comment['comment']) ?></p>
                    <div class="comment-meta">
                        By <strong><?= htmlspecialchars($comment['username']) ?></strong> on <?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
