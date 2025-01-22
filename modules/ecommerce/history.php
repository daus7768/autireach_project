<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to get order status details
function getOrderStatusDetails($status) {
    $statusDetails = [
        'pending' => [
            'color' => 'text-warning',
            'icon' => 'clock',
            'description' => 'Order is being processed'
        ],
        'processing' => [
            'color' => 'text-info',
            'icon' => 'sync',
            'description' => 'Order is being prepared'
        ],
        'shipped' => [
            'color' => 'text-primary',
            'icon' => 'truck',
            'description' => 'Order is on its way'
        ],
        'delivered' => [
            'color' => 'text-success',
            'icon' => 'check-circle',
            'description' => 'Order has been delivered'
        ],
        'cancelled' => [
            'color' => 'text-danger',
            'icon' => 'x-circle',
            'description' => 'Order has been cancelled'
        ]
    ];

    return $statusDetails[$status] ?? [
        'color' => 'text-secondary',
        'icon' => 'question-circle',
        'description' => 'Unknown status'
    ];
}

// Fetch user's orders
$sql = "SELECT o.id, o.total_amount, o.created_at, o.order_status, 
               o.tracking_number, o.estimated_delivery_date,
               sa.full_name, sa.address_line1, sa.city, sa.state, sa.postal_code
        FROM orders o
        JOIN shipping_addresses sa ON o.shipping_address_id = sa.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-timeline {
            position: relative;
            padding-left: 50px;
        }
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e0e0e0;
        }
        .status-item {
            position: relative;
            padding-bottom: 20px;
        }
        .status-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #e0e0e0;
        }
        .status-item.active::before {
            background-color: #28a745;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">My Orders</h1>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You have no orders yet. Start shopping!
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card order-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<?= $order['id'] ?></h5>
                    <span class="badge <?= getOrderStatusDetails($order['order_status'])['color'] ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Shipping Details</h6>
                            <p>
                                <?= htmlspecialchars($order['full_name']) ?><br>
                                <?= htmlspecialchars($order['address_line1']) ?><br>
                                <?= htmlspecialchars($order['city']) ?>, 
                                <?= htmlspecialchars($order['state']) ?> 
                                <?= htmlspecialchars($order['postal_code']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p>
                                <strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?><br>
                                <strong>Total Amount:</strong> RM<?= number_format($order['total_amount'], 2) ?><br>
                                <strong>Tracking Number:</strong> <?= $order['tracking_number'] ?><br>
                                <strong>Estimated Delivery:</strong> 
                                <?= date('F j, Y', strtotime($order['estimated_delivery_date'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="status-timeline mt-4">
                        <?php 
                        $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                        $currentStatusIndex = array_search($order['order_status'], $statuses);
                        
                        foreach ($statuses as $index => $status): 
                            $statusDetails = getOrderStatusDetails($status);
                        ?>
                            <div class="status-item <?= $index <= $currentStatusIndex ? 'active' : '' ?>">
                                <i class="bi bi-<?= $statusDetails['icon'] ?> me-2 <?= $statusDetails['color'] ?>"></i>
                                <?= ucfirst($status) ?> - <?= $statusDetails['description'] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>