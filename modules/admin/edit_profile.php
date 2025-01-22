<?php
session_start();
require_once '../../db/db.php';
// include '../../includes/anav.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html");
    exit;
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f4f7f6;
            --text-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .profile-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            transition: all 0.3s ease;
        }

        .profile-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-header h1 {
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .profile-header p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 42px;
            color: #ababab;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .file-input-wrapper .btn-file-upload {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .file-input-wrapper .btn-file-upload:hover {
            background-color: #2980b9;
        }

        .btn-update {
            width: 100%;
            padding: 12px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        .btn-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .btn-back {
            flex-grow: 1;
            padding: 12px;
            background-color: var(--back-button-color);
            color: black;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background-color: #555f66;
        }

        .btn-update:hover {
            background-color: #27ae60;
        }

        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
            margin: 0 auto 20px;
            display: block;
        }
        .profile-preview-container {
                position: relative;
                width: 120px; /* Adjust as needed */
                height: 120px; /* Adjust as needed */
                margin: 10px auto; /* Center horizontally */
                border-radius: 50%;
                border: 4px solid #007bff; /* Blue border */
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f8f9fa; /* Light background to emphasize text */
                text-align: center;
            }

            .profile-preview-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
                display: block;
            }

            .profile-preview-container img:empty::before {
                content: attr(alt);
                display: block;
                font-size: 14px;
                color: #6c757d; /* Gray text */
                font-weight: bold;
                text-align: center;
                line-height: 120px; /* Match height for vertical centering */
            }


        @media (max-width: 480px) {
            .profile-container {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Edit Profile</h1>
            <p>Update your profile information</p>
        </div>
        
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <div class="profile-preview-container">
                <img id="profile-preview" src="" alt="Profile Preview">
            </div>


                <div class="file-input-wrapper">
                    <button type="button" class="btn-file-upload">
                        <i class="fas fa-upload"></i> Choose File
                    </button>
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required 
                       value="<?= htmlspecialchars($user['username']) ?>"
                       placeholder="Enter your username">
                <i class="fas fa-user icon"></i>
            </div>

            <div class="form-group">
                <label for="password">Current Password</label>
                <input type="password" name="password" id="password" 
                       placeholder="Enter current password">
                <i class="fas fa-lock icon"></i>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" 
                       placeholder="Enter new password">
                <i class="fas fa-lock icon"></i>
            </div>

            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> Update Profile
            </button>
            <div class="btn-actions">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            
        </form>
    </div>

    <script>
        // Profile picture preview
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('profile-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // File upload button interaction
        document.querySelector('.btn-file-upload').addEventListener('click', function() {
            document.getElementById('profile_picture').click();
        });
    </script>
</body>
</html>