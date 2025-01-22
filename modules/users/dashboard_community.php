<?php
session_start();

// Check if the user is logged in and their role is community
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'community') {
    header("Location: ../../pages/login.html"); // Redirect to login page if not logged in or not a community user
    exit;
}

// Include the database connection
require_once '../../db/db.php';

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Dashboard - AutiReach</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Add your dashboard styles here -->
</head>
<body>
    <header>
        <h1>Welcome to the Community Dashboard, <?= htmlspecialchars($user['username']); ?>!</h1>
    </header>

    <nav>
        <ul>
            <li><a href=".../../pages/program.html">Explore Programs</a></li>
            <li><a href="../../pages/shop.html">Visit Shop</a></li>
            <li><a href="../../modules/user/profile.php">View Profile</a></li>
            <li><a href="../../modules/auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Upcoming Events</h2>
            <p>Check out the events available for the community. Membership subscribers get free access!</p>
            <a href="../../pages/program.html">View All Events</a>
        </section>

        <section>
            <h2>Shop Products</h2>
            <p>Discover products curated for the autism community.</p>
            <a href="../../pages/shop.html">Visit Shop</a>
        </section>

        <section>
            <h2>Blog and Articles</h2>
            <p>Read articles and blogs to stay informed and motivated.</p>
            <a href="../../pages/blog.html">Go to Blog</a>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y'); ?> AutiReach. All Rights Reserved.</p>
    </footer>
</body>
</html>
