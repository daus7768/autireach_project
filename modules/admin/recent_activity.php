<?php
function renderRecentActivity($conn) {
    $recentOrders = getRecentOrders($conn);
    ?>
   
    <div class="recent-activity">
        <h3>Recent Orders</h3>
        <div class="activity-list">
            <?php foreach ($recentOrders as $order): ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="activity-details">
                    <p class="activity-title">
                        <strong><?= htmlspecialchars($order['username']) ?></strong> placed order #<?= $order['id'] ?>
                    </p>
                    <p class="activity-meta">
                        MYR <?= number_format($order['total_amount'], 2) ?> • 
                        <?= $order['items_count'] ?> items • 
                        <?= date('M d, h:i A', strtotime($order['created_at'])) ?>
                    </p>
                    <p class="activity-products">
                        <?= htmlspecialchars($order['products']) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Your existing CSS styles here */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .chart-container {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .activity-list {
            margin-top: 1rem;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4f46e5;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-meta {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .activity-products {
            font-size: 0.875rem;
            color: #4b5563;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
