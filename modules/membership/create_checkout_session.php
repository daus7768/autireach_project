<?php
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';
session_start();

// Stripe API keys
\Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $planId = intval($input['plan_id']);
    $planName = $input['plan_name'];
    $planPrice = floatval($input['plan_price']) * 100; // Convert price to cents for Stripe

    try {
        // Create Stripe Checkout Session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'myr',
                    'product_data' => [
                        'name' => $planName,
                    ],
                    'unit_amount' => $planPrice,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost/AutiReach/AutiReach/modules/membership/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/AutiReach/AutiReach/modules/membership/membership.php?error=cancelled',
            'metadata' => [
                'user_id' => $userId,
                'plan_id' => $planId,
            ],
        ]);

        echo json_encode(['id' => $session->id]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}