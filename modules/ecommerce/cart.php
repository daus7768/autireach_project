<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

// Initialize cart
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Check if the product already exists in the cart
    $checkCartSql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $checkCartStmt = $conn->prepare($checkCartSql);
    $checkCartStmt->bind_param("ii", $user_id, $productId);
    $checkCartStmt->execute();
    $cartResult = $checkCartStmt->get_result();

    if ($cartResult->num_rows > 0) {
        // Update quantity if the product is already in the cart
        $updateCartSql = "UPDATE cart SET quantity = quantity + ?, added_at = NOW() WHERE user_id = ? AND product_id = ?";
        $updateCartStmt = $conn->prepare($updateCartSql);
        $updateCartStmt->bind_param("iii", $quantity, $user_id, $productId);
        $updateCartStmt->execute();
    } else {
        // Insert new item into the cart
        $insertCartSql = "INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())";
        $insertCartStmt = $conn->prepare($insertCartSql);
        $insertCartStmt->bind_param("iii", $user_id, $productId, $quantity);
        $insertCartStmt->execute();
    }
    header("Location: cart.php?success=Item added to cart");
    exit;
}

// Handle removing from cart
if (isset($_GET['remove'])) {
    $productId = intval($_GET['remove']);
    $removeCartSql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $removeCartStmt = $conn->prepare($removeCartSql);
    $removeCartStmt->bind_param("ii", $user_id, $productId);
    $removeCartStmt->execute();
    header("Location: cart.php?success=Item removed from cart");
    exit;
}

// Fetch cart items for the current user
$cartItems = [];
$total = 0;
$sql = "SELECT c.quantity, p.id as product_id, p.name, p.price FROM cart c INNER JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
         :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --background-color: rgb(253, 253, 253);
            --text-color: #333;
            --accent-color: #ff6b6b;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .background-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 50px;
        }

        .cart-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .table > :not(caption) > * > * {
            padding: 15px;
            vertical-align: middle;
        }

        .quantity-input {
            max-width: 100px;
            text-align: center;
        }

        .btn-update {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            background-color: darken(var(--secondary-color), 10%);
            transform: translateY(-2px);
        }

        .btn-remove {
            background-color: var(--accent-color);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background-color: darken(var(--accent-color), 10%);
            transform: translateY(-2px);
        }

        .btn-checkout, .btn-back {
            border: none;
            transition: all 0.3s ease;
        }

        .btn-checkout {
            background-color: #27ae60;
        }

        .btn-checkout:hover {
            background-color: #2ecc71;
            transform: translateY(-3px);
        }

        .btn-back {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-back:hover {
            background-color: darken(var(--secondary-color), 10%);
            transform: translateY(-3px);
        }

        .cart-total {
            background-color: #f1f2f6;
            border-radius: 10px;
            padding: 15px;
            text-align: right;
        }

        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="background-container">
        <img src="../../assets/img/productbackground.webp" alt="Background">
    </div>
<div class="container">
    <div class="cart-container">
        <div class="cart-header">
            <div>
                <i class="ri-shopping-cart-line"></i>
                <h1 class="mb-0 d-inline">Your Shopping Cart</h1>
            </div>
        </div>

        <?php if (count($cartItems) === 0): ?>
            <div class="alert alert-info text-center">
                <i class="ri-information-line me-2"></i>
                Your cart is empty. Start shopping now!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cartItems as $item): ?>
    <tr>
        <td><?= htmlspecialchars($item['name']) ?></td>
        <td>RM<?= number_format($item['price'], 2) ?></td>
        <td>
            <form method="POST" class="d-flex align-items-center">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control quantity-input me-2">
                <button type="submit" class="btn btn-update btn-sm">
                    <i class="ri-refresh-line"></i>
                </button>
            </form>
        </td>
        <td>RM<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        <td>
            <a href="?remove=<?= $item['product_id'] ?>" class="btn btn-remove btn-sm">
                <i class="ri-delete-bin-line"></i>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-total">
                <h3>Total: RM<?= number_format($total, 2) ?></h3>
            </div>

            <div class="cart-actions">
                <a href="shop.php" class="btn btn-back">
                    <i class="ri-arrow-left-line me-2"></i>Back to Shop
                </a>
                <a href="checkout.php" class="btn btn-checkout">
                    Proceed to Checkout <i class="ri-arrow-right-line ms-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>