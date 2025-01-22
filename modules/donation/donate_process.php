<?php
require_once '../../../vendor/autoload.php';
require_once '../../db/db.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;

// Set your secret key
Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorName = $_POST['donor_name'];
    $email = $_POST['email'];
    $amount = $_POST['amount'];

    try {
        // Create a PaymentIntent
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount * 100, // Amount in cents
            'currency' => 'myr',
            'receipt_email' => $email,
            'description' => "Donation from $donorName",
        ]);

        // Save the donation record in the database
        $sql = "INSERT INTO donations (donor_name, email, amount, stripe_payment_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $donorName, $email, $amount, $paymentIntent->id);
        $stmt->execute();

        // Display a beautiful receipt
        echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Donation Receipt</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #74ebd5, #acb6e5);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 600px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            padding: 30px;
            overflow: hidden;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        .receipt-header h2 {
            margin: 0;
            color: #343a40;
            font-weight: 600;
        }
        .receipt-header p {
            color: #6c757d;
        }
        .receipt-body {
            font-size: 1rem;
            color: #495057;
        }
        .receipt-body p {
            margin: 8px 0;
        }
        .receipt-body p strong {
            color: #343a40;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
            border-top: 2px solid #e9ecef;
            padding-top: 15px;
        }
        .btn-donate {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #ffffff;
            background: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn-donate:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class='receipt'>
        <div class='receipt-header'>
            <h2>Thank You for Your Donation!</h2>
            <p class='text-muted'>Here are your donation details</p>
        </div>
        <div class='receipt-body'>
            <p><strong>Donor Name:</strong> $donorName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Amount:</strong> RM $amount</p>
            <p><strong>Transaction ID:</strong> {$paymentIntent->id}</p>
        </div>
        <div class='receipt-footer'>
            <p>AutiReach appreciates your generosity.</p>
            <p>Receipt generated on " . date('d-m-Y H:i:s') . "</p>
            <a href='donate.php' class='btn-donate'>Donate Again</a>
        </div>
    </div>
</body>
</html>
";

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
