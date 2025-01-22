<?php
// success.php
session_start();
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php?error=Please login first");
    exit;
}

// Validate payment_intent_id
if (!isset($_GET['payment_intent_id']) || empty($_GET['payment_intent_id'])) {
    header("Location: program.php?error=Invalid payment reference");
    exit;
}

$paymentIntentId = $_GET['payment_intent_id'];
$userId = $_SESSION['user_id'];

// Fetch payment and program details
$sql = "SELECT p.*, pr.title, pr.date, pr.time, pr.location 
        FROM payments p 
        JOIN programs pr ON p.program_id = pr.id 
        WHERE p.payment_intent_id = ? AND p.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $paymentIntentId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

// Verify payment exists and is successful
if (!$payment || $payment['status'] !== 'succeeded') {
    header("Location: program.php?error=Payment verification failed");
    exit;
}

// Check if user was already added to participants
$checkParticipant = "SELECT id FROM participants WHERE user_id = ? AND program_id = ?";
$stmt = $conn->prepare($checkParticipant);
$stmt->bind_param("ii", $userId, $payment['program_id']);
$stmt->execute();
$participantResult = $stmt->get_result();

// Add to participants if not already added
if ($participantResult->num_rows === 0) {
    $insertParticipant = "INSERT INTO participants (user_id, program_id, joined_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($insertParticipant);
    $stmt->bind_param("ii", $userId, $payment['program_id']);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-card {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .program-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card bg-white">
            <div class="text-center">
                <div class="success-icon">✓</div>
                <h1 class="text-success mb-4">Payment Successful!</h1>
                <p class="lead">Thank you for joining our program.</p>
            </div>

            <div class="program-details">
                <h3><?= htmlspecialchars($payment['title']) ?></h3>
                <p><strong>Amount Paid:</strong> RM<?= number_format($payment['amount'], 2) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($payment['date']) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($payment['time']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($payment['location']) ?></p>
                <p><strong>Payment Reference:</strong> <?= htmlspecialchars($payment['payment_intent_id']) ?></p>
            </div>

            <div class="text-center mt-4">
                <a href="program.php" class="btn btn-primary">Return to Programs</a>
                <button class="btn btn-outline-secondary ms-2" onclick="window.print()">Print Receipt</button>
            </div>
        </div>
    </div>
</body>
</html>