<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;


}

$error = '';
$success = '';
$addresses = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $address_line1 = $conn->real_escape_string($_POST['address_line1']);
    $address_line2 = $conn->real_escape_string($_POST['address_line2'] ?? '');
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $country = $conn->real_escape_string($_POST['country']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    
    // Assuming user_id is stored in session
    $user_id = $_SESSION['user_id'] ?? 0;

    // Check if address already exists for this user
    $check_sql = "SELECT id FROM shipping_addresses WHERE user_id = ?
                  AND full_name = ?
                  AND address_line1 = ?
                  AND city = ?
                  AND state = ?
                  AND postal_code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("isssss", $user_id, $full_name, $address_line1, $city, $state, $postal_code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "This address already exists!";
    } else {
        // Insert new shipping address
        $sql = "INSERT INTO shipping_addresses (
            user_id, full_name, address_line1, address_line2,
            city, state, postal_code, country, phone_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssss",
            $user_id, $full_name, $address_line1, $address_line2,
            $city, $state, $postal_code, $country, $phone_number
        );

        if ($stmt->execute()) {
            $success = "Address added successfully!";
        } else {
            $error = "Error adding address: " . $conn->error;
        }
    }
}

// Fetch existing addresses
$user_id = $_SESSION['user_id'] ?? 0;
$fetch_sql = "SELECT * FROM shipping_addresses WHERE user_id = ?";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();
$addresses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shipping Address</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .address-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .address-card:hover {
            border-color: #0d6efd;
        }
        .selected-address {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Shipping Address</h2>
                    <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Existing Addresses -->
                <?php if (!empty($addresses)): ?>
                    <div class="mb-4">
                        <h4>Select an existing address:</h4>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card">
                                <h5><?php echo htmlspecialchars($address['full_name']); ?></h5>
                                <p class="mb-1"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                <?php if ($address['address_line2']): ?>
                                    <p class="mb-1"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                <?php endif; ?>
                                <p class="mb-1">
                                    <?php echo htmlspecialchars($address['city']) . ', ' . 
                                             htmlspecialchars($address['state']) . ' ' . 
                                             htmlspecialchars($address['postal_code']); ?>
                                </p>
                                <p class="mb-1"><?php echo htmlspecialchars($address['country']); ?></p>
                                <p class="mb-0"><?php echo htmlspecialchars($address['phone_number']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add New Address Form -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Add New Address</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                            </div>
                            <div class="mb-3">
                                <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Address</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add click handler for address selection
        document.querySelectorAll('.address-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.address-card').forEach(c => {
                    c.classList.remove('selected-address');
                });
                this.classList.add('selected-address');
            });
        });
    </script>
</body>
</html>