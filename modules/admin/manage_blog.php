<?php
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

// Add blog post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_by = $_SESSION['user_id'];

    // File upload
    $media_path = null;
    if (!empty($_FILES['media']['name'])) {
        $target_dir = "../../assets/media/blog/";
        $target_file = $target_dir . basename($_FILES['media']['name']);
        $media_path = "assets/media/blog/" . basename($_FILES['media']['name']);

        if (!move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to upload file!'
                });
            </script>";
        }
    }

    $sql = "INSERT INTO blog (title, content, media_path, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $content, $media_path, $created_by);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Blog post added successfully!'
            }).then(() => {
                window.location.reload();
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to add blog post!'
            });
        </script>";
    }
}

// Edit blog post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_blog'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $media_path = $_POST['existing_media'];

    // File upload (if new file is uploaded)
    if (!empty($_FILES['media']['name'])) {
        $target_dir = "../../assets/media/blog/";
        $target_file = $target_dir . basename($_FILES['media']['name']);
        $media_path = "assets/media/blog/" . basename($_FILES['media']['name']);

        if (!move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to upload file!'
                });
            </script>";
        }
    }

    $sql = "UPDATE blog SET title = ?, content = ?, media_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $content, $media_path, $id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Blog post updated successfully!'
            }).then(() => {
                window.location.reload();
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to update blog post!'
            });
        </script>";
    }
}

// Delete blog post
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $sql = "DELETE FROM blog WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Blog post deleted successfully!'
            }).then(() => {
                window.location.reload();
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to delete blog post!'
            });
        </script>";
    }
}

$sql = "SELECT * FROM blog";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <style>
        :root {
            --primary-color: #2563EB;
            --secondary-color: #3B82F6;
            --accent-color: #10B981;
            --background-color: #F3F4F6;
            --text-color: #1F2937;
            --card-bg: #FFFFFF;
            --hover-color: #1E40AF;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-container {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.2rem;
            margin-top: 0rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .btn-add {
            background-color: var(--accent-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background-color: var(--hover-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid var(--background-color);
            vertical-align: middle;
            padding: 1rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.05);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background-color: #DC2626;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .form-container {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 2rem auto;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }
        }
        .modal-media {
            max-width: 80%;
            max-height: 80%;
        }

    </style>
</head>
<body>
    <div class="main-content">
    
        <h1 class="page-title">Blog Management Dashboard</h1>
        
        <button class="btn btn-add w-10" onclick="showAddForm()">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New Blog
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content Preview</th>
                        <th>Media</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr data-id="<?= $row['id'] ?>">
            <td><?= $row['id'] ?></td>
            <td class="blog-title"><?= htmlspecialchars($row['title']) ?></td>
            <td class="blog-content"><?= htmlspecialchars(substr($row['content'], 0, 50)) ?>...</td>
            <td class="blog-media" data-media-path="<?= htmlspecialchars($row['media_path'] ?? '') ?>">
                <?php if ($row['media_path']): ?>
                    <a href="../../<?= $row['media_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Media</a>
                <?php else: ?>
                    <span class="text-muted">No Media</span>
                <?php endif; ?>
            </td>
            <td>
                <button class="btn btn-action btn-edit" onclick="showEditForm(<?= $row['id'] ?>)">Edit</button>
                <button class="btn btn-action btn-delete" onclick="confirmDelete(<?= $row['id'] ?>)">Delete</button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

            </table>
        </div>
    </div>

    <!-- Add Blog Modal -->
    <div class="modal fade" id="addBlogModal" tabindex="-1" aria-labelledby="addBlogModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBlogModalLabel">Add New Blog</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add_blog" value="1">
                        <div class="mb-3">
                            <label for="blogTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="blogTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="blogContent" class="form-label">Content</label>
                            <textarea class="form-control" id="blogContent" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="blogMedia" class="form-label">Media</label>
                            <input type="file" class="form-control" id="blogMedia" name="media" accept="image/*,video/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Blog</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Blog Modal -->
    <div class="modal fade" id="editBlogModal" tabindex="-1" aria-labelledby="editBlogModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBlogModalLabel">Edit Blog</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                        <input type="hidden" name="edit_blog" value="1">
                        <input type="hidden" name="id" id="editBlogId">
                        <div class="mb-3">
                            <label for="editBlogTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editBlogTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBlogContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editBlogContent" name="content" rows="5" required></textarea>
                        </div>
                        <input type="hidden" name="existing_media" id="existingMediaPath">
                        <div class="mb-3">
                            <label for="editBlogMedia" class="form-label">Update Media</label>
                            <input type="file" class="form-control" id="editBlogMedia" name="media" accept="image/*,video/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Blog</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewMediaModal" tabindex="-1" aria-labelledby="viewMediaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMediaModalLabel">View Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex justify-content-center">
                    <div class="modal-media">
                        <img id="viewMediaImage" src="" alt="Media">
                        <video id="viewMediaVideo" controls>
                            <source src="" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>



        // Use Bootstrap modals instead of custom show/hide
        function showAddForm() {
            var addModal = new bootstrap.Modal(document.getElementById('addBlogModal'));
            addModal.show();
        }

        function showEditForm(id) {
    // Find the table row for the blog post
    const row = document.querySelector(`tr[data-id="${id}"]`);

    // Populate the modal form with the blog data
    document.getElementById('editBlogId').value = id;
    document.getElementById('editBlogTitle').value = row.querySelector('.blog-title').textContent.trim();
    document.getElementById('editBlogContent').value = row.querySelector('.blog-content').textContent.trim();

    function showViewMediaModal(mediaPath) {
            const viewMediaModal = new bootstrap.Modal(document.getElementById('viewMediaModal'));
            const viewMediaImage = document.getElementById('viewMediaImage');
            const viewMediaVideo = document.getElementById('viewMediaVideo');

            if (mediaPath.endsWith('.png') || mediaPath.endsWith('.jpg') || mediaPath.endsWith('.jpeg')) {
                viewMediaImage.src = '../../' + mediaPath;
                viewMediaImage.style.display = 'block';
                viewMediaVideo.style.display = 'none';
            } else if (mediaPath.endsWith('.mp4')) {
                viewMediaVideo.querySelector('source').src = '../../' + mediaPath;
                viewMediaImage.style.display = 'none';
                viewMediaVideo.style.display = 'block';
            }

            viewMediaModal.show();
        }
    // Show the edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editBlogModal'));
    editModal.show();
}

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=${id}`;
                }
            });
        }
    </script>
</body>
</html>
