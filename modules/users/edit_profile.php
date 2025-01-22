<?php
session_start();
require_once '../../db/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_picture = $user['profile_picture'] ? '../../' . $user['profile_picture'] : '../../assets/img/default-profile.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Check if password change is requested
        if (!empty($current_password)) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if (!password_verify($current_password, $user_data['password'])) {
                throw new Exception('Current password is incorrect');
            }

            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match');
            }

            if (strlen($new_password) < 8) {
                throw new Exception('New password must be at least 8 characters long');
            }
        }
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }
            
            if ($_FILES['profile_picture']['size'] > $maxSize) {
                throw new Exception('File size too large. Maximum size is 5MB.');
            }
            
            $uploadDir = '../../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
                $profilePicturePath = 'uploads/profiles/' . $fileName;
            } else {
                throw new Exception('Failed to upload file.');
            }
        }

        // Start transaction
        $conn->begin_transaction();

        // Update user information
        $updateFields = ["username = ?", "email = ?"];
        $params = [$username, $email];
        $types = "ss";

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateFields[] = "password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        if (isset($profilePicturePath)) {
            $updateFields[] = "profile_picture = ?";
            $params[] = $profilePicturePath;
            $types .= "s";
        }

        $params[] = $userId;
        $types .= "i";

        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $conn->commit();
        $success_message = 'Profile updated successfully!';

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            background-image: url('https://images.pexels.com/photos/8386114/pexels-photo-8386114.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
            background-size: cover;
        }

        .container {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .profile-picture {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .profile-picture-preview {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .profile-picture-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        }

        .profile-picture-preview::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            border: 3px solid #4CAF50;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .profile-picture-preview:hover::after {
            opacity: 1;
        }

        .custom-file-upload {
            background: #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }

        .custom-file-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .input-group {
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border-radius: 12px;
            overflow: hidden;
        }

        .input-group-text {
            background-color: #ffffff;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #4CAF50;
            padding: 0.7rem 1rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-left: none;
            padding: 0.7rem 1rem;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.15);
        }

        .password-section {
            border-top: 2px solid #f8f9fa;
            margin-top: 3rem;
            padding-top: 2rem;
            position: relative;
        }

        .password-section::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4CAF50, transparent);
        }

        .btn-primary {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            background: linear-gradient(45deg, #45a049, #4CAF50);
        }

        .btn-outline-secondary {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-weight: 600;
            padding: 11px 30px;
            transition: all 0.3s;
        }

        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .alert-success {
            background: linear-gradient(45deg, #4CAF50, #81c784);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(45deg, #f44336, #e57373);
            color: white;
        }

        .alert .btn-close {
            filter: brightness(0) invert(1);
        }

        h2 {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        h4 {
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            
            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Rest of the HTML remains exactly the same as your original -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-center mb-4">Edit Profile</h2>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <div class="profile-picture-preview mb-3" 
                                         style="background-image: url('<?= $current_picture ?>')"
                                         onclick="document.getElementById('profile_picture').click()">
                                    </div>
                                    <label class="custom-file-upload btn btn-primary btn-sm position-absolute bottom-0 end-0">
                                        <i class="fas fa-camera"></i>
                                        <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>

                            <div class="password-section">
                                <h4 class="mb-4">Change Password</h4>
                                <div class="mb-4">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-picture-preview').style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>