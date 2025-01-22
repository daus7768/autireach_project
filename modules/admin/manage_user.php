<?php

session_start();

require_once '../../db/db.php';
require_once '../../includes/adminnav.php';

// Rest of the code follows...
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

// Handle delete user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userId = intval($_POST['delete_user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $message = "User deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete user.";
        $messageType = "error";
    }
    $stmt->close();
}

// Handle update user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $userId = intval($_POST['edit_user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    if (!in_array($role, ['admin', 'member', 'community'])) {
        $message = "Invalid role selected.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $userId);
        if ($stmt->execute()) {
            $message = "User updated successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to update user.";
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Fetch all users
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            max-width: 1200px;
            width: 150%;
            margin: 40px auto;
            padding: 0 100px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .alert {
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: #dcfce7;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            padding: 30px;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 20px;
        }

        .users-table thead {
            background-color: var(--background-color);
        }

        .users-table th, .users-table td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .users-table th {
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .users-table tr {
            background-color: var(--card-bg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .users-table tr:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            gap: 8px;
            text-transform: uppercase;
        }

        .btn-edit {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 5px 10px rgba(16, 185, 129, 0.3);
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
            box-shadow: 0 5px 10px rgba(239, 68, 68, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 20px;
            width: 500px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: scaleUp 0.5s ease;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 16px;
            margin-top: 10px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.4);
        }

        @keyframes scaleUp {
            0% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        .popup-message {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--secondary-color);
            color: white;
            padding: 18px 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            z-index: 2000;
        }

        .popup-message.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="main-content">
    <div class="container">
    <div class="admin-header">
    <h2>User Management</h2>
    <p>Total Users: <strong><?= $totalUsers; ?></strong></p>
</div>


        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id']; ?></td>
                                <td><?= htmlspecialchars($user['username']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td><?= ucfirst($user['role']); ?></td>
                                <td><?= $user['created_at']; ?></td>
                                <td>
                                    <button class="btn btn-edit" onclick="openEditModal(<?= $user['id']; ?>, '<?= htmlspecialchars($user['username']); ?>', '<?= htmlspecialchars($user['email']); ?>', '<?= $user['role']; ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_user_id" value="<?= $user['id']; ?>">
                                        <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No users found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </div>
            <h2>Edit User</h2>
            <form action="" method="POST" onsubmit="showPopupMessage('User updated successfully!'); return true;">
                <input type="hidden" name="edit_user_id" id="edit_user_id">
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_role">Role</label>
                    <select name="role" id="edit_role" class="form-control" required>
                        <option value="admin">Admin</option>
                        <option value="member">Member</option>
                        <option value="community">Community</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-submit">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="popup-message" id="popupMessage">User updated successfully!</div>

    <script>
        function openEditModal(id, username, email, role) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });

        function showPopupMessage(message) {
            const popup = document.getElementById('popupMessage');
            popup.textContent = message;
            popup.classList.add('show');

            setTimeout(() => {
                popup.classList.remove('show');
            }, 10000);
        }
    </script>
    </main>
</body>
</html>