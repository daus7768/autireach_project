<?php
session_start();
require_once '../../db/db.php';
include '../../includes/mnav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

// Fetch blog posts
$query = "SELECT * FROM blog ORDER BY created_at DESC";
$result = $conn->query($query);
$blogs = [];
while ($row = $result->fetch_assoc()) {
    $blogs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafb;
            color: #333;
        }

        .page-header {
            background: linear-gradient(to right, #6dd5ed, #2193b0);
            color: white;
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 40px;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
        }

        .page-header h1 {
            font-weight: 600;
        }

        .blog-card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            background-color: white;
        }

        .blog-card:hover {
            transform: scale(1.03);
        }

        .blog-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body h5 {
            font-weight: 600;
            color: #343a40;
        }

        .read-more {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .read-more:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Latest Articles From Blog</h1>
        <p>Explore our latest blog posts and updates</p>
    </div>

    <div class="container">
        <div class="row">
            <?php foreach ($blogs as $blog): ?>
                <div class="col-md-4 mb-4">
                    <div class="card blog-card">
                        <?php 
                        // Check media path and type
                        $media_path = '../../assets/media/blog/' . basename($blog['media_path']);
                        $file_ext = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));

                        if (file_exists($media_path)) {
                            if ($file_ext == 'mp4'): ?>
                                <video class="blog-image" controls>
                                    <source src="<?= htmlspecialchars($media_path) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($media_path) ?>" alt="<?= htmlspecialchars($blog['title']) ?>" class="blog-image">
                            <?php endif; 
                        } else { ?>
                            <div style="height: 200px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                        <?php } ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($blog['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($blog['content'], 0, 100)) ?>...</p>
                            <a href="blog_details.php?id=<?= $blog['id'] ?>" class="read-more">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
