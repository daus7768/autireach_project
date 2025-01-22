<?php

require_once '../../db/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

$sql = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header("Location: ../../pages/login.html?error=Unauthorized access");
    exit;
}
$user = $result->fetch_assoc();
$stmt->close();

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AutiReach</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #10b981;
            --accent-color: #6366f1;
            --background-light: #f3f4f6;
            --background-dark: #111827;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-radius: 12px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--background-light);
        }

        *::-webkit-scrollbar {
            width: 8px;
        }

        *::-webkit-scrollbar-track {
            background: var(--background-light);
        }

        *::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 20px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, var(--background-light) 0%, #ffffff 100%);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 300px;
            background: linear-gradient(195deg, white 0%, #f9fafb 100%);
            border-right: 1px solid #e5e7eb;
            padding: 25px;
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 15px -5px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 10;
            transition: width var(--transition-speed) ease;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            position: relative;
        }

        .sidebar-logo img {
            max-width: 180px;
            transition: transform var(--transition-speed) ease;
        }

        .sidebar-logo::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 10px;
            text-decoration: none;
            color: var(--text-secondary);
            border-radius: var(--border-radius);
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar-menu a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            opacity: 0.1;
            transition: width var(--transition-speed) ease;
        }

        .sidebar-menu a:hover::before {
            width: 100%;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: var(--primary-color);
            transform: translateX(10px);
        }

        .sidebar-menu a i {
            margin-right: 15px;
            font-size: 1.3rem;
            color: var(--text-secondary);
            transition: color var(--transition-speed) ease;
        }

        .sidebar-menu a:hover i, .sidebar-menu a.active i {
            color: var(--primary-color);
        }

        .main-content {
            flex-grow: 1;
            padding: 40px;
            background: transparent;
            overflow-y: auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .dashboard-header h1 {
            color: var(--text-primary);
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            opacity: 0.8;
            transition: all var(--transition-speed) ease;
        }

        .card:hover .card-icon {
            transform: scale(1.1);
            opacity: 1;
        }

        .card-title {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 15px;
            position: relative;
        }

        .card-description {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .user-profile img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 20px;
            border: 3px solid var(--primary-color);
            object-fit: cover;
        }

        .footer {
            text-align: center;
            padding: 25px;
            background: linear-gradient(to right, var(--background-light), #ffffff);
            border-top: 1px solid #e5e7eb;
            color: var(--text-secondary);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
        }

        .modal-content input[type="text"],
        .modal-content input[type="password"],
        .modal-content input[type="file"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal-content button {
            margin-top: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: var(--primary-color);
            color: white;
            cursor: pointer;
        }

        .modal-content button.cancel {
            background: #dc3545;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="..\..\assets\img\logo2.png" alt="AutiReach">
            </div>
            
            <div class="sidebar-menu">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_programs.php">
                    <i class="fas fa-chalkboard-teacher"></i> Manage Programs
                </a>
                <a href="admin_payment.php">
                    <i class="fas fa-check-circle"></i> Payment Management
                </a>
                <a href="manage_products.php">
                    <i class="fas fa-shopping-cart"></i> Manage Products
                </a>
                <a href="manage_blog.php">
                    <i class="fas fa-blog"></i> Manage Blog
                </a>
                <a href="manage_user.php">
                    <i class="fas fa-users"></i> Menage user
                </a>
                <a href="admin_memberships.php">
                    <i class="fas fa-users"></i> Memberships management
                </a>
                <a href="../../modules/auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <div class="user-profile">
                <img src="../../<?= $user['profile_picture'] ?>" alt="Admin Profile" style="border-radius: 50%; width: 60px; height: 60px;">
                <div>
                    <div style="font-weight: 600;"><?= htmlspecialchars($user['username']); ?></div>
                    <div style="color: #6b7280; font-size: 0.8rem;">Administrator</div>
                    <a href="edit_profile.php" style="color: #3b82f6; font-size: 0.8rem;">Edit Profile</a>
                </div>
            </div>
        </aside>

          <!-- Profile Modal -->
   <div class="modal" id="profileModal">
        <div class="modal-content">
            <h2>Update Profile</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username']) ?>" required>
                <input type="file" name="profile_picture" accept="image/*">
                <input type="password" name="current_password" placeholder="Current Password">
                <input type="password" name="new_password" placeholder="New Password">
                <button type="submit">Update</button>
                <button type="button" class="cancel" onclick="hideProfileModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showProfileModal() {
            document.getElementById('profileModal').classList.add('active');
        }

        function hideProfileModal() {
            document.getElementById('profileModal').classList.remove('active');
        }
    </script>
</body>
</html>