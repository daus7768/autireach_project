<?php
session_start();
require_once '../../db/db.php';
require_once 'stats_overview.php';
require_once 'recent_activity.php';
require_once 'stats_cards.php';
require_once 'header.php';


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

// Dashboard statistics
$programsCount = $conn->query("SELECT COUNT(*) AS total FROM programs")->fetch_assoc()['total'];
$paymentsCount = $conn->query("SELECT COUNT(*) AS total FROM payments")->fetch_assoc()['total'];
$usersCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$productsCount = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];

// Membership analytics
$monthlyMembershipData = $conn->query("
    SELECT 
        DATE_FORMAT(um.start_date, '%Y-%m') as month,
        SUM(CASE WHEN mp.name = 'Basic plan' THEN mp.price ELSE 0 END) as basicAmount,
        COUNT(CASE WHEN mp.name = 'Basic plan' THEN 1 END) as basicCount,
        SUM(CASE WHEN mp.name = 'Pro Plan' THEN mp.price ELSE 0 END) as proAmount,
        COUNT(CASE WHEN mp.name = 'Pro Plan' THEN 1 END) as proCount,
        SUM(CASE WHEN mp.name = 'Premium Plan' THEN mp.price ELSE 0 END) as premiumAmount,
        COUNT(CASE WHEN mp.name = 'Premium Plan' THEN 1 END) as premiumCount
    FROM user_memberships um
    JOIN membership_plans mp ON um.plan_id = mp.id
    WHERE um.status = 'active'
    GROUP BY DATE_FORMAT(um.start_date, '%Y-%m')
    ORDER BY month
");

$membershipData = [];
while ($row = $monthlyMembershipData->fetch_assoc()) {
    $membershipData[] = $row;
}

// Plan distribution
$planDistribution = $conn->query("
    SELECT 
        mp.name,
        COUNT(*) as count,
        SUM(mp.price) as total_revenue
    FROM user_memberships um
    JOIN membership_plans mp ON um.plan_id = mp.id
    WHERE um.status = 'active'
    GROUP BY mp.name
")->fetch_all(MYSQLI_ASSOC);



//  test data TESSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSST




// fetch data from orders
function getRecentOrders($conn) {
    $query = "
        SELECT 
            o.id,
            o.created_at,
            u.username,
            SUM(oi.quantity * oi.price) as total_amount,
            COUNT(oi.id) as items_count,
            GROUP_CONCAT(p.name) as products
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AutiReach</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_programs.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Programs</span>
                </a>
                <a href="admin_payment.php">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="manage_products.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Products</span>
                </a>
                <a href="manage_blog.php">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>
                </a>
                <a href="manage_user.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="admin_memberships.php">
                <i class="fa-solid fa-address-card fa-bounce" style="color: #1c3179;"></i>
                    <span>Memberships</span>
                </a>
                <a href="participant.php">
                    <i class="fas fa-user-friends"></i>
                    <span>Participants</span>
                </a>
                <a href="order.php">
                <i class="fa-solid fa-truck-fast fa-bounce" style="color: #0b017e;"></i>
                    <span>order</span>
                </a>
                <a href="../../modules/auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>

            <div class="user-profile">
            <img src="../../<?= $user['profile_picture'] ?>" alt="Admin Profile" style="border-radius: 50%; width: 60px; height: 60px;">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="user-role">Administrator</div>
                    <a href="edit_profile.php" class="edit-profile-btn"><i class="fas fa-user-edit"></i>Edit Profile</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Welcome Back, <?= htmlspecialchars($user['username']) ?>!</h1>
                <p>Here's what's happening with your platform today.</p>
            </div>

           <!-- Statistics Cards -->
           <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 class="card-title">Total Programs</h3>
                    <p class="card-description"><?= number_format($programsCount) ?></p>
                </div>
                
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="card-title">Total Payments</h3>
                    <p class="card-description"><?= number_format($paymentsCount) ?></p>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="card-title">Total Products</h3>
                    <p class="card-description"><?= number_format($productsCount) ?></p>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Total Users</h3>
                    <p class="card-description"><?= number_format($usersCount) ?></p>
                </div>
            </div>
   
            <!-- TEST DATA ADDD TESTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT -->

            <!-- display the data at  stats Overview  -->
            <?php renderStatsOverview($conn); ?>


         


            <div class="graph-container">
                <h3>Membership Analytics</h3>
                <div class="charts-grid">
                    <div class="chart-container">
                        <canvas id="membershipRevenueChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="planDistributionChart"></canvas>
                    </div>
                </div>
                   

            </div>

             <!-- display the donation analytics -->
            <div class="graph-container">
                <h3>Donation Analytics</h3>
                <div class="charts-grid">
                    <div class="chart-container">
                       <?php renderStatsCards($stats); ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="donationChart"></canvas>
                    </div>
            </div>
          </div>
             <!-- display the function for the display recend order  -->
             <div class="graph-container">
               
                <div class="charts-grid">
                  <?php renderRecentActivity($conn); ?>
               </div>
            </div>


            <footer class="footer">
                <p>&copy; <?= date('Y') ?> AutiReach. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script>
      

        // Membership Revenue Chart
        const membershipChart = new Chart(
            document.getElementById('membershipRevenueChart').getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_column($membershipData, 'month')) ?>,
                    datasets: [
                        {
                            label: 'Basic Plan',
                            data: <?= json_encode(array_column($membershipData, 'basicAmount')) ?>,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Pro Plan',
                            data: <?= json_encode(array_column($membershipData, 'proAmount')) ?>,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Premium Plan',
                            data: <?= json_encode(array_column($membershipData, 'premiumAmount')) ?>,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'MYR ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            }
        );

        // Plan Distribution Chart
        const distributionChart = new Chart(
            document.getElementById('planDistributionChart').getContext('2d'),
            {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($planDistribution, 'name')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($planDistribution, 'count')) ?>,
                        backgroundColor: [
                            '#4f46e5',
                            '#10b981',
                            '#f59e0b'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            }
        );

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







