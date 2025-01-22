<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in and role is 'member'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../../pages/login.html?error=Access denied");
    exit;
}

$userId = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .navbar {
            background-color: #007bff;
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        .navbar-nav .nav-link:hover {
            color: #d4e2f4 !important;
        }
        .dashboard-header {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: white;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }
        .card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">AutiReach</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-home"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-info-circle"></i> About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i> Programs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-shopping-cart"></i> Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user-plus"></i> Membership</a></li>
                    <li class="nav-item"><a class="nav-link" href="../blog/blog.php"><i class="fas fa-user-plus"></i> blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link logout-btn" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="dashboard-header">
        <h1>Welcome to Your Dashboard, Member!</h1>
        <p>Explore exclusive features and benefits tailored for you.</p>
    </header>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calendar-check fa-2x"></i></h5>
                        <p class="card-text">View and manage your programs.</p>
                        <a href="#" class="btn btn-primary">View Programs</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-shopping-bag fa-2x"></i></h5>
                        <p class="card-text">Shop for exclusive items.</p>
                        <a href="#" class="btn btn-primary">Visit Shop</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users fa-2x"></i></h5>
                        <p class="card-text">Manage your membership.</p>
                        <a href="membership_page.php" class="btn btn-primary">Manage Membership</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user-circle fa-2x"></i></h5>
                        <p class="card-text">Update your profile information.</p>
                        <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-envelope fa-2x"></i></h5>
                        <p class="card-text">Contact support for assistance.</p>
                        <a href="#" class="btn btn-primary">Contact Support</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-sign-out-alt fa-2x"></i></h5>
                        <p class="card-text">Logout from your account.</p>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
