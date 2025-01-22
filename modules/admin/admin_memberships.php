<?php
ob_start(); // Start output buffering to prevent headers already sent errors
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

// Fetch dashboard statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalMemberships = $conn->query("SELECT COUNT(*) as count FROM user_memberships")->fetch_assoc()['count'];
$activeMemberships = $conn->query("SELECT COUNT(*) as count FROM user_memberships WHERE status = 'active'")->fetch_assoc()['count'];
$inactiveMemberships = $totalMemberships - $activeMemberships;

// Fetch latest memberships
$latestMembershipsQuery = "SELECT um.id, u.username, mp.name AS plan_name, um.start_date, um.end_date, um.status
                           FROM user_memberships um
                           JOIN users u ON um.user_id = u.id
                           JOIN membership_plans mp ON um.plan_id = mp.id
                           ORDER BY um.created_at DESC LIMIT 5";
$latestMemberships = $conn->query($latestMembershipsQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch all memberships
$membershipsQuery = "SELECT um.id, u.username, mp.name AS plan_name, um.plan_id, um.start_date, um.end_date, um.status 
                     FROM user_memberships um
                     JOIN users u ON um.user_id = u.id
                     JOIN membership_plans mp ON um.plan_id = mp.id
                     ORDER BY um.created_at DESC";
$memberships = $conn->query($membershipsQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch all users
$usersQuery = "SELECT id, username FROM users";
$users = $conn->query($usersQuery)->fetch_all(MYSQLI_ASSOC);

// Fetch all membership plans
$plansQuery = "SELECT id, name FROM membership_plans";
$plans = $conn->query($plansQuery)->fetch_all(MYSQLI_ASSOC);

// Handle delete membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $deleteQuery = "DELETE FROM user_memberships WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        header("Location: admin_memberships.php?success=Membership deleted successfully");
    } else {
        header("Location: admin_memberships.php?error=Error deleting membership");
    }
    exit;
}

// Handle edit membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $editId = $_POST['edit_id'];
    $planId = $_POST['plan_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE user_memberships SET plan_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("isssi", $planId, $startDate, $endDate, $status, $editId);
    if ($stmt->execute()) {
        header("Location: admin_memberships.php?success=Membership updated successfully");
    } else {
        header("Location: admin_memberships.php?error=Error updating membership");
    }
    exit;
}

// Handle add membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_membership'])) {
    $userId = $_POST['user_id'];
    $planId = $_POST['plan_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO user_memberships (user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iisss", $userId, $planId, $startDate, $endDate, $status);
    if ($stmt->execute()) {
        header("Location: admin_memberships.php?success=Membership added successfully");
    } else {
        header("Location: admin_memberships.php?error=Error adding membership");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Memberships</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;

        }

        body {
            background-color: var(--light-bg);
            font-family: 'Arial', sans-serif;
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #5A5AFF);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .table-responsive {
            margin-top: 1rem;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.05);
            transition: background-color 0.3s ease;
        }

        .btn-action {
            margin: 0 0.25rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-action i {
            margin-right: 0.3rem;
        }

        .badge {
            padding: 0.4rem 0.6rem;
            font-weight: 500;
        }

        .modal-content {
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .btn-action {
                margin-bottom: 0.5rem;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
    <div class="page-header text-center">
        <h1>Manage User Memberships</h1>
        <p>View, edit, add, and delete user memberships</p>
    </div>

    <div class="container">
    <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text display-4"><?= $totalUsers ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Active Memberships</h5>
                        <p class="card-text display-4"><?= $activeMemberships ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Inactive Memberships</h5>
                        <p class="card-text display-4"><?= $inactiveMemberships ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Total Memberships</h5>
                        <p class="card-text display-4"><?= $totalMemberships ?></p>
                    </div>
                </div>
            </div>
        </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-2"></i>Add Membership
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($memberships): ?>
                        <?php foreach ($memberships as $membership): ?>
                            <tr>
                                <td><?= $membership['id'] ?></td>
                                <td><?= htmlspecialchars($membership['username']) ?></td>
                                <td><?= htmlspecialchars($membership['plan_name']) ?></td>
                                <td><?= htmlspecialchars($membership['start_date']) ?></td>
                                <td><?= htmlspecialchars($membership['end_date']) ?></td>
                                <td>
                                    <span class="badge <?= $membership['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars($membership['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-secondary btn-action" data-bs-toggle="modal" data-bs-target="#editModal<?= $membership['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteMembership(<?= $membership['id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $membership['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel">Edit Membership</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="edit_id" value="<?= $membership['id'] ?>">
                                                <div class="mb-3">
                                                    <label for="plan_id" class="form-label">Plan</label>
                                                    <select name="plan_id" class="form-control" required>
                                                        <?php foreach ($plans as $plan): ?>
                                                            <option value="<?= $plan['id'] ?>" <?= $plan['id'] == $membership['plan_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($plan['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="start_date" class="form-label">Start Date</label>
                                                    <input type="date" name="start_date" class="form-control" value="<?= $membership['start_date'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="end_date" class="form-label">End Date</label>
                                                    <input type="date" name="end_date" class="form-control" value="<?= $membership['end_date'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select name="status" class="form-control" required>
                                                        <option value="active" <?= $membership['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                        <option value="inactive" <?= $membership['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No memberships found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Add Membership</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">User</label>
                                <select name="user_id" class="form-control" required>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>">
                                            <?= htmlspecialchars($user['username']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Plan</label>
                                <select name="plan_id" class="form-control" required>
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan['id'] ?>">
                                            <?= htmlspecialchars($plan['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_membership" class="btn btn-success">Add Membership</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteMembership(id) {
            if (confirm("Are you sure you want to delete this membership?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "admin_memberships.php";

                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "delete_id";
                input.value = id;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    </main>
</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer and end the output
?>