<?php
// process_payment.php
session_start();
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';

// Stripe Setup
\Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to continue']);
    exit;
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['program_id']) || !isset($input['price'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$programId = intval($input['program_id']);
$price = floatval($input['price']);
$userId = $_SESSION['user_id'];

// Check if user is already a participant
$stmt = $conn->prepare("SELECT id FROM participants WHERE user_id = ? AND program_id = ?");
$stmt->bind_param("ii", $userId, $programId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'You are already registered for this program']);
    exit;
}

// Verify program and price
$stmt = $conn->prepare("SELECT title, price FROM programs WHERE id = ?");
$stmt->bind_param("i", $programId);
$stmt->execute();
$program = $stmt->get_result()->fetch_assoc();

if (!$program) {
    http_response_code(404);
    echo json_encode(['error' => 'Program not found']);
    exit;
}

if ($program['price'] != $price) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid price']);
    exit;
}

try {
    // Create PaymentIntent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => intval($price * 100),
        'currency' => 'myr',
        'metadata' => [
            'program_id' => $programId,
            'user_id' => $userId
        ]
    ]);

    // Record payment attempt
    $stmt = $conn->prepare("INSERT INTO payments (user_id, program_id, payment_intent_id, amount, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iids", $userId, $programId, $paymentIntent->id, $price);
    $stmt->execute();

    echo json_encode(['clientSecret' => $paymentIntent->client_secret]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>