<?php
session_start();
require_once '../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    
    
    }

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store user session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] === 'member') {
                header("Location: ../../pages/dashboard_member.html");
            } else {
                header("Location: ../../pages/dashboard_community.html");
            }
            exit;
        } else {
            header("Location: ../../pages/login.html?error=Invalid password");
        }
    } else {
        header("Location: ../../pages/login.html?error=Invalid username or email");
    }
    $stmt->close();
}
$conn->close();
?>
