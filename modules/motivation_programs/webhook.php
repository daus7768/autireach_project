<?php
// webhook.php
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');

// Retrieve the webhook payload
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'your_webhook_signing_secret'; // Replace with your webhook signing secret

// Verify webhook signature
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        $endpoint_secret
    );
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

// Log webhook event for debugging
$logFile = fopen("webhook_log.txt", "a");
fwrite($logFile, date('Y-m-d H:i:s') . " - Event: " . $event->type . "\n");

// Function to update payment status
function updatePaymentStatus($paymentIntentId, $status, $conn) {
    $sql = "UPDATE payments SET status = ?, updated_at = NOW() WHERE payment_intent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status, $paymentIntentId);
    return $stmt->execute();
}

// Function to add participant
function addParticipant($paymentIntentId, $conn) {
    // First get payment details
    $sql = "SELECT user_id, program_id FROM payments WHERE payment_intent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $paymentIntentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    if ($payment) {
        // Check if already a participant
        $checkSql = "SELECT id FROM participants WHERE user_id = ? AND program_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $payment['user_id'], $payment['program_id']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            // Add to participants
            $insertSql = "INSERT INTO participants (user_id, program_id, joined_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ii", $payment['user_id'], $payment['program_id']);
            return $stmt->execute();
        }
    }
    return false;
}

// Handle different event types
try {
    switch ($event->type) {
        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            
            // Update payment status
            if (updatePaymentStatus($paymentIntent->id, 'succeeded', $conn)) {
                // Add to participants
                addParticipant($paymentIntent->id, $conn);
                fwrite($logFile, "Payment succeeded and participant added: " . $paymentIntent->id . "\n");
            }
            break;

        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            updatePaymentStatus($paymentIntent->id, 'failed', $conn);
            fwrite($logFile, "Payment failed: " . $paymentIntent->id . "\n");
            break;

        case 'payment_intent.canceled':
            $paymentIntent = $event->data->object;
            updatePaymentStatus($paymentIntent->id, 'failed', $conn);
            fwrite($logFile, "Payment canceled: " . $paymentIntent->id . "\n");
            break;

        default:
            fwrite($logFile, "Unhandled event type: " . $event->type . "\n");
    }
} catch (Exception $e) {
    fwrite($logFile, "Error: " . $e->getMessage() . "\n");
    http_response_code(500);
    exit();
}

fclose($logFile);
http_response_code(200);
?>