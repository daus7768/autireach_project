<?php
require_once '../../db/db.php'; // Update with your actual DB connection file

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id']; // Assuming the user ID is stored in the session

$sql = "SELECT c.id AS cart_id, c.quantity, p.name, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = [
        'id' => $row['cart_id'],
        'quantity' => $row['quantity'],
        'name' => $row['name'],
        'price' => $row['price']
    ];
}

echo json_encode($cartItems);
?>
