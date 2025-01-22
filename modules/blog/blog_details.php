<?php
session_start();
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

// Fetch the blog post
$query = "SELECT * FROM blog WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    echo "Blog post not found.";
    exit;
}
$stmt->close();

// Media Path
$media_path = '../../assets/media/blog/' . basename($blog['media_path']);
$file_ext = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']) ?> Awareness Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color:rgb(129, 177, 225);
            color: #343a40;
        }

        .page-header {
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            color: white;
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 40px;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }

        .page-header h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .blog-container {
            background: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            margin: auto;
            padding: 20px;
            max-width: 800px;
        }

        .blog-media {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-weight: 600;
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
            text-decoration: none;
        }

        .back-link i {
            margin-right: 8px;
        }

        .comments-section {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <h1><?= htmlspecialchars($blog['title']) ?></h1>
        <p>Published on <?= date('F j, Y', strtotime($blog['created_at'])) ?></p>
    </div>

    <!-- Blog Content -->
    <div class="container">
        <a href="blog.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Awareness Hub
        </a>
        <div class="blog-container">
            <!-- Blog Media -->
            <?php if (file_exists($media_path)): ?>
                <?php if ($file_ext === 'mp4'): ?>
                    <video class="blog-media" controls>
                        <source src="<?= htmlspecialchars($media_path) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($media_path) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="blog-media">
                <?php endif; ?>
            <?php else: ?>
                <div style="height: 200px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image fa-3x" style="color: #adb5bd;"></i>
                </div>
            <?php endif; ?>

            <!-- Blog Content -->
            <div class="content">
                <p><?= nl2br(htmlspecialchars($blog['content'])) ?></p>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <?php include 'comments.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
