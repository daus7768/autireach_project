<?php
session_start();
require_once '../../db/db.php';

// Authentication check (optional, can be customized)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login or show a message
    header("Location: ../../pages/login.html");
    exit;
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'community') {
        include '../../includes/cnav.php';
    } elseif ($_SESSION['role'] == 'member') {
        include '../../includes/mnav.php';
    }
}

// Check if product ID is passed
if (!isset($_GET['id'])) {
    header("Location: shop.php?error=Product not found");
    exit;
}

$productId = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: shop.php?error=Product not found");
    exit;
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
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

        .product-container {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            color: white;
            overflow: hidden;
            margin-top: 50px;
            transition: transform 0.3s ease;
        }

        .product-container:hover {
            transform: scale(1.02);
        }

        .product-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            filter: brightness(0.9);
            transition: filter 0.3s ease;
        }

        .product-image:hover {
            filter: brightness(1);
        }

        .product-details {
            padding: 30px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }

        .product-title {
            color: white;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .product-description {
            color: rgba(255,255,255,0.8);
            line-height: 1.6;
        }

        .product-price {
            color: var(--accent-color);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .btn-add-cart {
            background-color: var(--accent-color);
            border: none;
            color: white;
            transition: all 0.3s ease;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-add-cart:hover {
            background-color: darken(var(--accent-color), 10%);
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .btn-back {
            background-color: rgba(255,255,255,0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .quantity-input {
            max-width: 100px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
<div class="background-container">
        <img src="../../assets/img/productbackground.webp" alt="Background">
    </div>
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="product-container">
                <div class="row g-0">
                    <div class="col-md-6">
                    <img src="../../assets/<?= htmlspecialchars($product['image']) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>" 
                    class="img-fluid product-image w-100">

                    </div>
                    <div class="col-md-6 product-details">
                        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="product-price">RM<?= number_format($product['price'], 2) ?></div>
                        
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <div class="mb-3">
                                <label for="quantity" class="form-label text-white">
                                    <i class="ri-shopping-basket-line"></i> Quantity
                                </label>
                                <input type="number" 
                                       name="quantity" 
                                       id="quantity" 
                                       value="1" 
                                       min="1" 
                                       class="form-control quantity-input" 
                                       required>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-add-cart">
                                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart
                                </button>
                                <a href="shop.php" class="btn btn-back">
                                    <i class="ri-arrow-left-line me-2"></i>Back to Shop
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>