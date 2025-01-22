<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: /AutiReach/AutiReach/pages/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard | AutiReach</title>
</head>
<body>
    <h1>Welcome, Member</h1>
    <p>Your membership allows you to join events for free!</p>
    <nav>
        <ul>
            <li><a href="/AutiReach/AutiReach/modules/user/view_programs.php">View Programs</a></li>
            <li><a href="/AutiReach/AutiReach/modules/user/view_membership.php">Membership Details</a></li>
            <li><a href="/AutiReach/AutiReach/modules/auth/logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
