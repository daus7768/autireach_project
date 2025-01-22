<?php
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';


\Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz'); // Replace with your SECRET KEY


$data = json_decode(file_get_contents("php://input"), true);
$amount = $data['amount'];

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => ['name' => 'AutiReach Purchase'],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/AutiReach/AutiReach/modules/ecommerce/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/AutiReach/AutiReach/modules/ecommerce/checkout.php?error=cancelled',
    ]);

    echo json_encode(['id' => $session->id]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
