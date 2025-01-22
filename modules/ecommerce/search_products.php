<?php
require_once '../../db/db.php';

$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

$sql = "SELECT id, name, description, price, stock, image FROM products 
        WHERE name LIKE '%$search%' OR description LIKE '%$search%' ORDER BY created_at DESC";

$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($products);
