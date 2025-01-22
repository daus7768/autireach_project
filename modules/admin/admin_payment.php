<?php
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html?error=Unauthorized access");
    exit;
}

// Notification variables
$showToast = false;
$toastMessage = '';
$toastType = '';

// Handle actions (approve, reject, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'], $_POST['action'])) {
    $paymentId = intval($_POST['payment_id']);
    $action = $_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'succeeded' : 'failed';
        $stmt = $conn->prepare("UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $paymentId);
        $stmt->execute();
        $stmt->close();

        $showToast = true;
        $toastMessage = $action === 'approve' ? "Payment approved successfully!" : "Payment rejected successfully!";
        $toastType = $action === 'approve' ? 'success' : 'danger';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $stmt->close();

        $showToast = true;
        $toastMessage = "Payment deleted successfully!";
        $toastType = 'success';
    }
}

// Fetch all payments
$sql = "SELECT p.id, u.username, p.program_id, pr.title AS program_title, p.payment_intent_id, p.amount, p.status, p.created_at, p.updated_at 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN programs pr ON p.program_id = pr.id
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);

$payments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payments - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #22d3ee;
            --light-bg: #f3f4f6;
            --dark-bg: #1f2937;
            --text-color: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        .gradient-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .gradient-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .table-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background-color: var(--dark-bg);
            color: white;
        }

        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }

        .btn-group .btn {
            margin: 0 0.25rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-approve {
            background-color: #10b981;
            border-color: #10b981;
        }

        .btn-approve:hover {
            background-color: #059669;
            border-color: #059669;
        }

        .btn-reject {
            background-color: #ef4444;
            border-color: #ef4444;
        }

        .btn-reject:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        .btn-delete {
            background-color: #6b7280;
            border-color: #6b7280;
        }

        .btn-delete:hover {
            background-color: #4b5563;
            border-color: #4b5563;
        }

        .badge {
            font-weight: 500;
            padding: 0.4rem 0.6rem;
            border-radius: 0.5rem;
        }

        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1100;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }

            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<main class="main-content"> 
<div class="gradient-header">
    <h1>Payment Management</h1>
</div>

<!-- Toast Notification -->
<div class="toast-container">
    <div id="actionToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="container-fluid px-4 px-md-5">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Program</th>
                        <th>Payment Intent ID</th>
                        <th>Amount (MYR)</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment['id']) ?></td>
                                <td><?= htmlspecialchars($payment['username']) ?></td>
                                <td><?= htmlspecialchars($payment['program_title']) ?></td>
                                <td><?= htmlspecialchars($payment['payment_intent_id']) ?></td>
                                <td>RM<?= number_format($payment['amount'], 2) ?></td>
                                <td>
                                    <?php if ($payment['status'] === 'succeeded'): ?>
                                        <span class="badge bg-success">Succeeded</span>
                                    <?php elseif ($payment['status'] === 'failed'): ?>
                                        <span class="badge bg-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($payment['created_at']) ?></td>
                                <td><?= htmlspecialchars($payment['updated_at']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-approve btn-sm">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-reject btn-sm">Reject</button>
                                            <button type="submit" name="action" value="delete" class="btn btn-delete btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No payments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    <?php if ($showToast): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('actionToast');
            const toastMessage = document.getElementById('toastMessage');
            const toast = new bootstrap.Toast(toastEl);

            toastEl.classList.remove('bg-success', 'bg-danger');
            toastEl.classList.add('bg-<?= $toastType ?>');
            toastMessage.textContent = "<?= $toastMessage ?>";

            toast.show();
        });
    <?php endif; ?>
</script>

</body>
</html>