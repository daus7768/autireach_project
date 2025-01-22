<?php
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';
session_start();

// Stripe API keys
\Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');

if (!isset($_GET['session_id'])) {
    header("Location: membership.php?error=invalid_request");
    exit;
}

$sessionId = $_GET['session_id'];

try {
    // Retrieve Stripe Checkout Session
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    $userId = $session->metadata->user_id;
    $planId = $session->metadata->plan_id;
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime("+30 days"));

    // Insert subscription into the database
    $insertQuery = "INSERT INTO user_memberships (user_id, plan_id, start_date, end_date, stripe_subscription_id, status)
                    VALUES (?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iisss", $userId, $planId, $startDate, $endDate, $sessionId);
    $stmt->execute();

    // Update user role to 'member'
    $updateUserQuery = "UPDATE users SET role = 'member' WHERE id = ?";
    $stmt = $conn->prepare($updateUserQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Display success message
    $message = 'Subscription successful! Welcome to the membership.';
    $messageType = 'success';
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle errors
    $message = "Error processing payment: {$e->getMessage()}";
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subscription Status</h1>
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <a href="membership.php" class="button">Go to Membership Page</a>
    </div>
</body>
</html>