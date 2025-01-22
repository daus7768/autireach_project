<?php
require_once '../../../vendor/autoload.php';
require_once '../../db/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Handle form submission to add a new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $full_name = $_POST['full_name'];
    $address_line1 = $_POST['address_line1'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];

    $insert_sql = "INSERT INTO shipping_addresses (user_id, full_name, address_line1, city, state, postal_code) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("isssss", $user_id, $full_name, $address_line1, $city, $state, $postal_code);
    $stmt->execute();
    header("Location: checkout.php");
    exit;
}

// Fetch user's addresses
$addresses_sql = "SELECT * FROM shipping_addresses WHERE user_id = ?";
$addresses_stmt = $conn->prepare($addresses_sql);
$addresses_stmt->bind_param("i", $user_id);
$addresses_stmt->execute();
$addresses_result = $addresses_stmt->get_result();
$addresses = $addresses_result->fetch_all(MYSQLI_ASSOC);

// Fetch cart items
$sql = "SELECT c.quantity, p.name, p.price, p.id AS product_id 
        FROM cart c 
        INNER JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$lineItems = [];
$totalAmount = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $lineItems[] = [
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'product_id' => $row['product_id']
        ];
        $totalAmount += $row['price'] * $row['quantity'];
    }
} else {
    header("Location: cart.php?error=Your cart is empty");
    exit;
}

// Free delivery logic
$delivery_fee = 0;
$totalAmount += $delivery_fee; // Delivery is free

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
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

        
        .checkout-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .address-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1.5px solid #e0e0e0;
        }
        .address-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        .form-control {
            border-radius: 8px;
            padding: 10px;
        }
        .order-summary {
            background-color: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
        }
        .checkout-button {
            background-color: #28a745;
            border: none;
            transition: all 0.3s ease;
        }
        .checkout-button:hover {
            background-color: #218838;
            transform: scale(1.02);
        }
        .list-group-item {
            border-color: #e9ecef;
            padding: 12px 15px;
        }
        .back-button {
            background-color: #6c757d;
            color: white;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background-color: #555f66;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
<div class="background-container">
        <img src="../../assets/img/productbackground.webp" alt="Background">
    </div>
    <div class="container checkout-container">
        <!-- Header with Back Button -->
        <div class="row mb-4 align-items-center">
            <div class="col-auto">
                <a href="cart.php" class="btn back-button">
                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                </a>
            </div>
            <div class="col">
                <h2 class="text-center mb-0"><i class="fas fa-shipping-fast me-2 text-primary"></i>Checkout</h2>
            </div>
        </div>

        <div class="row">
            <!-- Address Section -->
            <div class="col-md-8 pe-md-4">
                <div class="mb-4">
                    <h4 class="mb-3"><i class="fas fa-map-marker-alt me-2 text-secondary"></i>Shipping Address</h4>

                    <!-- Existing Addresses -->
                    <?php foreach ($addresses as $address): ?>
                        <div class="card address-card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user me-2"></i><?= htmlspecialchars($address['full_name']) ?></h5>
                                <p class="card-text">
                                    <i class="fas fa-home me-2"></i><?= htmlspecialchars($address['address_line1']) ?><br>
                                    <i class="fas fa-city me-2"></i><?= htmlspecialchars($address['city']) ?>, 
                                    <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['postal_code']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Add New Address Form -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-2 text-success"></i>Add a New Address</h5>
                            <form method="POST" action="checkout.php">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <input class="form-control" type="text" name="full_name" placeholder="Full Name" required>
                                    </div>
                                    <div class="col-12">
                                        <input class="form-control" type="text" name="address_line1" placeholder="Address Line" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input class="form-control" type="text" name="city" placeholder="City" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input class="form-control" type="text" name="state" placeholder="State" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input class="form-control" type="text" name="postal_code" placeholder="Postal Code" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="add_address" class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-2"></i>Add Address
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary Section -->
            <div class="col-md-4">
                <div class="order-summary">
                    <h4 class="mb-4"><i class="fas fa-shopping-cart me-2 text-primary"></i>Order Summary</h4>
                    <div class="list-group">
                        <?php foreach ($lineItems as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <span class="badge bg-primary rounded-pill">RM<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Delivery Fee</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong class="text-primary">RM<?= number_format($totalAmount, 2) ?></strong>
                        </div>
                    </div>
                    <button id="checkout-button" class="btn btn-success checkout-button w-100 mt-4">
                        <i class="fas fa-credit-card me-2"></i>Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe Payment Script remains the same -->
    <script>
        const stripe = Stripe("pk_test_51QTi5fBZgipvrhfHuA561cMkaLAziDdPcsKamaCTMYSpGaUgIu8gVqVIQWaArz4MMkLiMoVaFZEnojuIGdGlEQ0y00D3Zvypta");

        document.getElementById("checkout-button").addEventListener("click", async () => {
            try {
                const response = await fetch("process_payment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ amount: <?= $totalAmount * 100 ?> }) // Amount in cents
                });

                const session = await response.json();
                if (session.id) {
                    stripe.redirectToCheckout({ sessionId: session.id });
                } else {
                    alert("Error: " + session.error);
                }
            } catch (error) {
                console.error("Error:", error);
            }
        });
    </script>
</body>
</html>