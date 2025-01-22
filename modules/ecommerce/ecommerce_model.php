<?php
require_once '../../db/db.php';

function getAllProducts() {
    global $conn;
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
