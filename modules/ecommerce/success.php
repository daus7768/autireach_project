<?php
require_once '../../../vendor/autoload.php';
require_once '../../db/db.php';
session_start();

if (!isset($_GET['session_id']) || !isset($_SESSION['user_id'])) {
    header("Location: checkout.php?error=invalid_session");
    exit;
}

$session_id = htmlspecialchars($_GET['session_id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    // Set Stripe API key
    \Stripe\Stripe::setApiKey('sk_test_51QTi5fBZgipvrhfHqETwvQIjXoOyfjhOcsET9HwAXmRqvGw9bPzTNPjKYkmPgZ0H64ndCIGtxsBSAqrKWtcGxCg7004uZKkGZz');
    
    // Retrieve the session to get payment details
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($session->payment_status === 'paid') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Get cart items
            $cart_sql = "SELECT c.quantity, p.price, p.id AS product_id 
                        FROM cart c 
                        INNER JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("i", $user_id);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            
            // Calculate total amount
            $total_amount = 0;
            while ($item = $cart_result->fetch_assoc()) {
                $total_amount += ($item['price'] * $item['quantity']);
            }
            
            // 2. Create order record
            $order_sql = "INSERT INTO orders (user_id, total_amount, status, created_at, order_status) 
                         VALUES (?, ?, 'completed', NOW(), 'pending')";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("id", $user_id, $total_amount);
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            
            // 3. Store order items
            $cart_result->data_seek(0); // Reset result pointer
            $order_items_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                              VALUES (?, ?, ?, ?)";
            $order_items_stmt = $conn->prepare($order_items_sql);
            
            while ($item = $cart_result->fetch_assoc()) {
                $order_items_stmt->bind_param("iiid", 
                    $order_id, 
                    $item['product_id'], 
                    $item['quantity'], 
                    $item['price']
                );
                $order_items_stmt->execute();
            }
            
            // 4. Clear the user's cart
            $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
            $clear_cart_stmt = $conn->prepare($clear_cart_sql);
            $clear_cart_stmt->bind_param("i", $user_id);
            $clear_cart_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Store order ID in session for reference
            $_SESSION['last_order_id'] = $order_id;
            
        } catch (Exception $e) {
            // If error occurs, rollback changes
            $conn->rollback();
            error_log("Order processing error: " . $e->getMessage());
            header("Location: checkout.php?error=order_processing_failed");
            exit;
        }
    } else {
        header("Location: checkout.php?error=payment_incomplete");
        exit;
    }
    
} catch (Exception $e) {
    error_log("Stripe session error: " . $e->getMessage());
    header("Location: checkout.php?error=payment_verification_failed");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .order-id {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin: 15px 0;
        }
        .btn-home {
            background-color: #6a11cb;
            border: none;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background-color: #5a0fb0;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1 class="mb-4">Payment Successful!</h1>
        <p class="lead mb-3">Thank you for your purchase.</p>
        <?php if (isset($_SESSION['last_order_id'])): ?>
            <div class="order-id">
                <small>Order ID: #<?php echo $_SESSION['last_order_id']; ?></small>
            </div>
        <?php endif; ?>
        <p class="text-muted mb-4">You will receive an order confirmation email shortly.</p>
        <a href="shop.php" class="btn btn-home text-white">
            <i class="fas fa-home me-2"></i>Return to Shop
        </a>
    </div>
</body>
</html>