<?php
require_once '../../db/db.php'; // Update with your actual DB connection file

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if (isset($_GET['id'])) {
    $cartId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cartId, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
