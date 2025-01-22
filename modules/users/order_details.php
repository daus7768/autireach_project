<?php
require_once '../../db/db.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = intval($_GET['order_id']);
$userId = $_SESSION['user_id'];

try {
    // Verify that this order belongs to the current user
    $checkOrderQuery = $conn->prepare("
        SELECT 
            o.id AS order_id,
            o.created_at,
            o.status,
            o.payment_method,
            o.shipping_method,
            o.tracking_number,
            o.total_amount,
            u.username
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $checkOrderQuery->bind_param("ii", $orderId, $userId);
    $checkOrderQuery->execute();
    $orderResult = $checkOrderQuery->get_result();
    $order = $orderResult->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found or access denied.");
    }

    // Fetch order items
    $itemsQuery = $conn->prepare("
        SELECT 
            oi.id AS item_id,
            oi.product_id,
            oi.quantity,
            oi.price,
            (oi.price * oi.quantity) AS subtotal,
            p.name AS product_name,
            p.sku,
            p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemsQuery->bind_param("i", $orderId);
    $itemsQuery->execute();
    $orderItems = $itemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch shipping and billing addresses
    $addressQuery = $conn->prepare("
        SELECT 
            sa.full_name AS shipping_full_name,
            sa.address_line1 AS shipping_address_line1,
            sa.address_line2 AS shipping_address_line2,
            sa.city AS shipping_city,
            sa.state AS shipping_state,
            sa.postal_code AS shipping_postal_code,
            ba.full_name AS billing_full_name,
            ba.address_line1 AS billing_address_line1,
            ba.address_line2 AS billing_address_line2,
            ba.city AS billing_city,
            ba.state AS billing_state,
            ba.postal_code AS billing_postal_code
        FROM orders o
        LEFT JOIN shipping_addresses sa ON o.shipping_address_id = sa.id
        LEFT JOIN billing_addresses ba ON o.billing_address_id = ba.id
        WHERE o.id = ?
    ");
    $addressQuery->bind_param("i", $orderId);
    $addressQuery->execute();
    $addresses = $addressQuery->get_result()->fetch_assoc();

} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: orders.php?error=An error occurred while fetching order details');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?= htmlspecialchars($orderId) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color:rgb(47, 147, 247);
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .order-status {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Order Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Order Date:</strong></td>
                            <td><?= date('F d, Y H:i:s', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Shipping Method:</strong></td>
                            <td><?= htmlspecialchars($order['shipping_method'] ?? 'N/A') ?></td>
                        </tr>
                        <?php if (!empty($order['tracking_number'])): ?>
                        <tr>
                            <td><strong>Tracking Number:</strong></td>
                            <td><?= htmlspecialchars($order['tracking_number']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h5>Shipping Address</h5>
                        <address class="mb-0">
                            <?= htmlspecialchars($addresses['shipping_full_name']) ?><br>
                            <?= htmlspecialchars($addresses['shipping_address_line1']) ?><br>
                            <?= htmlspecialchars($addresses['shipping_city']) . ', ' . htmlspecialchars($addresses['shipping_state']) . ' ' . htmlspecialchars($addresses['shipping_postal_code']) ?><br>
                            Phone: <?= htmlspecialchars($addresses['shipping_phone']) ?>
                        </address>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h5>Order Items</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['sku']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>RM <?= number_format($item['price'], 2) ?></td>
                                <td>RM <?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>