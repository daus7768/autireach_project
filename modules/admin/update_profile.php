<?php
session_start();
require_once '../../db/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data for displaying in the form
$sql = "SELECT username, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_picture = $user['profile_picture'] ? '../../' . $user['profile_picture'] : '../../assets/img/default-profile.png';

$alertMessage = ""; // Placeholder for alert messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $new_password = trim($_POST['new_password']);
    $profile_picture = $_FILES['profile_picture'];

    if (empty($username)) {
        $alertMessage = "<div class='alert alert-danger text-center'><strong>Error!</strong> Username cannot be empty.</div>";
    } else {
        if (!empty($new_password)) {
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['password'])) {
                $alertMessage = "<div class='alert alert-danger text-center'><strong>Error!</strong> Current password is incorrect.</div>";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        if (!empty($profile_picture['name'])) {
            $upload_dir = '../../assets/img/';
            $upload_file = $upload_dir . basename($profile_picture['name']);
            move_uploaded_file($profile_picture['tmp_name'], $upload_file);
            $upload_file = 'assets/img/' . basename($profile_picture['name']);
        } else {
            $upload_file = null;
        }

        $sql = "UPDATE users SET username = ?";
        if (!empty($new_password)) $sql .= ", password = ?";
        if (!empty($upload_file)) $sql .= ", profile_picture = ?";
        $sql .= " WHERE id = ?";

        $stmt = $conn->prepare($sql);
        if (!empty($new_password) && !empty($upload_file)) {
            $stmt->bind_param("sssi", $username, $hashed_password, $upload_file, $user_id);
        } elseif (!empty($new_password)) {
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } elseif (!empty($upload_file)) {
            $stmt->bind_param("ssi", $username, $upload_file, $user_id);
        } else {
            $stmt->bind_param("si", $username, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $alertMessage = "<div class='alert alert-success text-center'><strong>Success!</strong> Your profile has been updated successfully.</div>";
            echo '<script>setTimeout(() => { window.location.href = "../admin/dashboard.php"; }, 3000);</script>';
        } else {
            $alertMessage = "<div class='alert alert-danger text-center'><strong>Error!</strong> Failed to update your profile. Please try again.</div>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .profile-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            margin-left: 30%; /* Push to the right */
            margin-right: 70%;   /* Align to the right */
        }
        .profile-picture-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 3px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <!-- Display alert messages dynamically -->
            <?php if (!empty($alertMessage)) echo $alertMessage; ?>

            <h2 class="text-center mb-4">Old profile will be changed</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img id="profilePreview" src="<?php echo $current_picture; ?>" alt="Profile Picture" class="profile-picture-preview" />
                    <input type="file" id="profilePicture" name="profile_picture" class="form-control mt-3" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter current password to make changes">
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password (Optional)</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank if not changing">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('profilePicture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('profilePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
