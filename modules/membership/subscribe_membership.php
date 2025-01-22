<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $planId = intval($_POST['plan_id']);

    // Fetch the selected plan
    $planQuery = "SELECT * FROM membership_plans WHERE id = ?";
    $stmt = $conn->prepare($planQuery);
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plan) {
        echo "Invalid membership plan.";
        exit;
    }

    // Calculate membership dates
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime("+{$plan['duration']} days"));

    // Insert membership record
    $insertQuery = "INSERT INTO user_memberships (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iiss", $userId, $planId, $startDate, $endDate);
    $stmt->execute();
    $stmt->close();

    // Update user's membership status
    $updateUserQuery = "UPDATE users SET membership_status = 'member' WHERE id = ?";
    $stmt = $conn->prepare($updateUserQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    echo "<script>
        alert('You have successfully subscribed to the membership plan!');
        window.location.href = 'membership.php';
    </script>";
    exit;
} else {
    echo "Invalid request.";
}
