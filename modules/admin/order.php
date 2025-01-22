<?php
session_start();
require_once '../../db/db.php'; 
require_once  '../../includes/adminnav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

if (!isset($conn)) {
    die("Database connection failed. Check db.php configuration.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

        if (!$order_id || !$new_status) {
            throw new Exception("Invalid input data.");
        }

        $update_sql = "UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $new_status, $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $_SESSION['success_message'] = "Order status updated successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        $_SESSION['error_message'] = "Error updating order. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

try {
    $query = "
        SELECT 
            o.id AS order_id,
            o.created_at,
            o.order_status,
            o.shipping_address_id,
            o.tracking_number,
            o.estimated_delivery_date,
            u.username,
            sa.full_name,
            sa.address_line1,
            sa.city,
            sa.state,
            sa.postal_code,
            SUM(oi.quantity * oi.price) AS total_amount,
            COUNT(oi.id) AS items_count,
            GROUP_CONCAT(p.name SEPARATOR ', ') AS products
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN shipping_addresses sa ON o.shipping_address_id = sa.id
        WHERE o.shipping_address_id IS NOT NULL
        GROUP BY 
            o.id, o.created_at, o.order_status, o.shipping_address_id, 
            o.tracking_number, o.estimated_delivery_date, u.username,
            sa.full_name, sa.address_line1, sa.city, sa.state, sa.postal_code
        ORDER BY o.created_at DESC";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed.");
    }
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    die("An error occurred while fetching orders. Please try again later.");
}

function getOrderStatusDetails($status) {
    $statusDetails = [
        'pending' => ['description' => 'Order received and awaiting processing'],
        'processing' => ['description' => 'Order is being prepared'],
        'shipped' => ['description' => 'Order has been shipped'],
        'delivered' => ['description' => 'Order has been delivered'],
        'cancelled' => ['description' => 'Order has been cancelled']
    ];
    return $statusDetails[$status] ?? ['description' => 'Status unknown'];
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --background-light: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --border-radius: 16px;
            --transition-speed: 0.3s;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            
        }

        body {
            background: var(--background-light);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            transition: transform var(--transition-speed) ease;
            z-index: 50;
        }

        .container {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }
        table {
            font-size: 0.9rem;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .modal-content {
            border-radius: 10px;
        }
        .btn-primary, .btn-danger, .btn-warning {
            font-size: 0.85rem;
        }
    </style>
    <!-- Your existing CSS styles here -->
</head>
<body>
   
 
    <main class="main-content"> 
        <div class="dashboard-header">
            <div class="container">
                <h1 class="display-4 mb-0">Order Management</h1>
                <p class="lead">Monitor and manage all orders in one place</p>
            </div>
        </div>

        <div class="container">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase text-muted mb-2">Total Orders</h6>
                                    <h2 class="mb-0"><?= count($orders) ?></h2>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-circle bg-primary text-white">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $pending_count = count(array_filter($orders, fn($o) => $o['order_status'] === 'pending'));
                $processing_count = count(array_filter($orders, fn($o) => $o['order_status'] === 'processing'));
                $completed_count = count(array_filter($orders, fn($o) => $o['order_status'] === 'delivered'));
                ?>

                <!-- Other stat cards... -->
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="orderSearch" class="form-control form-control-lg" placeholder="Search orders by ID, customer name, or status...">
            </div>
            <br>
            <!-- Orders List -->
           
                
           <br>

            <!-- Orders List -->
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No orders found.
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-box me-2 text-primary"></i>
                                Order #<?= htmlspecialchars($order['order_id']) ?>
                            </h5>
                         
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="fas fa-user me-2"></i>Customer Information
                                    </h6>
                                    <p class="mb-4">
                                        <strong>Username:</strong> <?= htmlspecialchars($order['username']) ?><br>
                                        <strong>Full Name:</strong> <?= htmlspecialchars($order['full_name']) ?><br>
                                        <strong>Address:</strong> <?= htmlspecialchars($order['address_line1']) ?><br>
                                        <strong>City:</strong> <?= htmlspecialchars($order['city']) ?><br>
                                        <strong>State:</strong> <?= htmlspecialchars($order['state']) ?><br>
                                        <strong>Postal Code:</strong> <?= htmlspecialchars($order['postal_code']) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">
                                        <i class="fas fa-info-circle me-2"></i>Order Details
                                    </h6>
                                    <p>
                                        <strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?><br>
                                        <strong>Total Amount:</strong> 
                                        <span class="text-success">RM<?= number_format($order['total_amount'], 2) ?></span><br>
                                        <strong>Items:</strong> <?= htmlspecialchars($order['products']) ?><br>
                                        <?php if ($order['tracking_number']): ?>
                                            <strong>Tracking:</strong> <?= htmlspecialchars($order['tracking_number']) ?><br>
                                        <?php endif; ?>
                                        <?php if ($order['estimated_delivery_date']): ?>
                                            <strong>Est. Delivery:</strong> 
                                            <?= date('F j, Y', strtotime($order['estimated_delivery_date'])) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                           

                          
                        </div>
                    </div>
                    <br>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('orderSearch').addEventListener('keyup', function(e) {
            const searchText = e.target.value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                const orderContent = card.textContent.toLowerCase();
                if (orderContent.includes(searchText)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
</script>
<script>
    // Add loading state to buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Find the submit button in this form
            const btn = this.querySelector('button[type="submit"]');
            
            // If button exists, handle the loading state
            if (btn) {
                // Store the original button text
                const originalText = btn.innerHTML;
                
                // Disable button and show loading state
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Updating...`;
                
                // Reset button state after timeout
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 2000);
            }
        });
    });

    // Enable search functionality
    document.getElementById('orderSearch').addEventListener('keyup', function(e) {
        const searchText = e.target.value.toLowerCase();
        const orderCards = document.querySelectorAll('.order-card');
        
        orderCards.forEach(card => {
            const orderContent = card.textContent.toLowerCase();
            if (orderContent.includes(searchText)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Initialize tooltips if using Bootstrap
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>

<script>

        // Add tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Status badge color updates
        document.querySelectorAll('select[name="new_status"]').forEach(select => {
            select.addEventListener('change', function() {
                const card = this.closest('.order-card');
                const statusBadge = card.querySelector('.status-badge');
                
                // Remove all existing status classes
                statusBadge.classList.remove('pending', 'processing', 'shipped', 'delivered', 'cancelled');
                
                // Add new status class
                statusBadge.classList.add(this.value);
                statusBadge.textContent = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            });
        });

        // Timeline animation
        function animateTimeline() {
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, index * 200);
            });
        }

        // Animate on page load
        document.addEventListener('DOMContentLoaded', animateTimeline);

        // Order card hover effects
        document.querySelectorAll('.order-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 0.5rem 2rem rgba(0, 0, 0, 0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 0.15rem 1.75rem rgba(0, 0, 0, 0.1)';
            });
        });

        // Show success/error messages with animation
        function showMessage(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.style.zIndex = '1050';
            alertDiv.innerHTML = `
                <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Add real-time order status updates
        function initializeOrderUpdates() {
            const evtSource = new EventSource('order-updates.php');
            
            evtSource.onmessage = function(event) {
                const update = JSON.parse(event.data);
                const orderCard = document.querySelector(`[data-order-id="${update.orderId}"]`);
                
                if (orderCard) {
                    const statusBadge = orderCard.querySelector('.status-badge');
                    statusBadge.className = `status-badge ${update.status}`;
                    statusBadge.textContent = update.status.charAt(0).toUpperCase() + update.status.slice(1);
                    
                    showMessage('success', `Order #${update.orderId} status updated to ${update.status}`);
                }
            };

            evtSource.onerror = function() {
                evtSource.close();
            };
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('orderSearch').focus();
            }
        });

        // Export orders to CSV
        document.getElementById('exportOrders')?.addEventListener('click', function() {
            const orderData = [];
            document.querySelectorAll('.order-card').forEach(card => {
                const order = {
                    id: card.querySelector('h5').textContent.trim().replace('Order #', ''),
                    status: card.querySelector('.status-badge').textContent.trim(),
                    customer: card.querySelector('strong').textContent.trim(),
                    amount: card.querySelector('.text-success').textContent.trim()
                };
                orderData.push(order);
            });

            const csv = convertToCSV(orderData);
            downloadCSV(csv, 'orders_export.csv');
        });

        function convertToCSV(objArray) {
            const array = typeof objArray !== 'object' ? JSON.parse(objArray) : objArray;
            let str = `${Object.keys(array[0]).join(',')}\r\n`;

            for (let i = 0; i < array.length; i++) {
                let line = '';
                for (let index in array[i]) {
                    if (line != '') line += ',';
                    line += array[i][index];
                }
                str += line + '\r\n';
            }
            return str;
        }

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename);
            } else {
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    

        // Initialize all features
        document.addEventListener('DOMContentLoaded', function() {
            animateTimeline();
            initializeOrderUpdates();
        });
    </script>
    <script>function getOrderStatusDetails($status) {
    $statusDetailsMap = [
        'pending' => [
            'description' => 'Your order is being processed and will be updated soon.',
        ],
        'confirmed' => [
            'description' => 'Your order has been confirmed. Preparing for shipment.',
        ],
        'shipped' => [
            'description' => 'Your order has been shipped and is on its way.',
        ],
        'delivered' => [
            'description' => 'Your order has been delivered. Thank you for shopping with us!',
        ],
        'canceled' => [
            'description' => 'Your order has been canceled. Please contact support for more details.',
        ],
        'refunded' => [
            'description' => 'Your order has been refunded. The amount should reflect in your account soon.',
        ],
    ];

    // Return the details for the given status or a default message if the status is not found.
    return $statusDetailsMap[$status] ?? [
        'description' => 'No details available for this status.',
    ];
}

</script>
   
</body>
   
</html>