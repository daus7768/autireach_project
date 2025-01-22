<?php

require_once '../../db/db.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html?error=Unauthorized access");
    exit;
}

// Verify admin status
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Root Variables */
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --background-light: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --border-radius: 16px;
            --transition-speed: 0.3s;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            
        }

        body {
            background: var(--background-light);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            transition: transform var(--transition-speed) ease;
            z-index: 50;
        }

        .sidebar-logo {
            padding: 0rem 0;
            margin-bottom: 0rem;
            text-align: center;
        }

        .sidebar-logo img {
            max-width: 130px;
            height: auto;
            transition: transform var(--transition-speed) ease;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            margin: 0.5rem 0;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all var(--transition-speed) ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
            transform: translateX(0.5rem);
        }

        .sidebar-menu a i {
            width: 1.5rem;
            margin-right: 1rem;
            font-size: 1.25rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            max-width: calc(100% - 280px);
        }

        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        /* Cards Grid */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
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
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--primary-light));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform var(--transition-speed) ease;
        }

        .card:hover {
            transform: translateY(-0.5rem);
            box-shadow: var(--shadow-lg);
        }

        .card:hover::before {
            transform: scaleX(1);
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: transform var(--transition-speed) ease;
        }

        .card:hover .card-icon {
            transform: scale(1.1);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Charts Section */
        .graph-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .graph-container h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .dashboard-cards > * {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .graph-container {
            animation: slideIn 0.6s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                max-width: 100%;
            }

            .dashboard-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .graph-container {
                padding: 1rem;
            }
        }

        /* User Profile Styles */
        .user-profile {
            margin-top: auto;
            padding: 1rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-profile img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            background: white;
            margin-top: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .edit-profile-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: linear-gradient(to right, var(--primary-color), var(--primary-light));
    color: white;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
    margin-top: 8px;
}

.edit-profile-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
    background: linear-gradient(to right, var(--primary-light), var(--primary-dark));
    color: white;
}

.edit-profile-btn i {
    margin-right: 6px;
    font-size: 0.8rem;
}


    </style>

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../../assets/img/logo2.png" alt="AutiReach">
    </div>
    
    <nav class="sidebar-menu">
        <?php
               // Get the current page's file name
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>

                <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_programs.php" class="<?= ($current_page == 'manage_programs.php') ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Programs</span>
                </a>
                <a href="admin_payment.php" class="<?= ($current_page == 'admin_payment.php') ? 'active' : '' ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="manage_products.php" class="<?= ($current_page == 'manage_products.php') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Products</span>
                </a>
                <a href="manage_blog.php" class="<?= ($current_page == 'manage_blog.php') ? 'active' : '' ?>">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>
                </a>
                <a href="manage_user.php" class="<?= ($current_page == 'manage_user.php') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="admin_memberships.php" class="<?= ($current_page == 'admin_memberships.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-address-card fa-bounce" style="color: #1c3179;"></i>
                    <span>Memberships</span>
                </a>
                <a href="participant.php" class="<?= ($current_page == 'participant.php') ? 'active' : '' ?>">
                    <i class="fas fa-user-friends"></i>
                    <span>Participants</span>
                </a>
                <a href="order.php">
                <i class="fa-solid fa-truck-fast fa-bounce" style="color: #0b017e;"></i>
                    <span>order</span>
                <a href="../../modules/auth/logout.php" class="<?= ($current_page == 'logout.php') ? 'active' : '' ?>">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>

            <div class="user-profile">
            <img src="../../<?= $user['profile_picture'] ?>" alt="Admin Profile" style="border-radius: 50%; width: 60px; height: 60px;">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="user-role">Administrator</div>
                    <a href="edit_profile.php"  class="edit-profile-btn"><i class="fas fa-user-edit"></i>Edit Profile</a>
                </div>
            </div>
        </aside>
        <script>
        // Responsive sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.createElement('button');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.classList.add('menu-toggle');
            menuToggle.style.cssText = `
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 100;
                background: white;
                border: none;
                padding: 0.5rem;
                border-radius: 0.5rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                display: none;
                cursor: pointer;
            `;

            document.body.appendChild(menuToggle);

            menuToggle.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            function handleResize() {
                if (window.innerWidth <= 1024) {
                    menuToggle.style.display = 'block';
                } else {
                    menuToggle.style.display = 'none';
                    document.querySelector('.sidebar').classList.remove('active');
                }
            }

            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>

       
</body>
</html>