<?php
session_start();

// Include database connection
require_once '../../db/db.php';
require_once '../../config/config.php';



// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'community') {
        include '../../includes/cnav.php';
    } elseif ($_SESSION['role'] == 'member') {
        include '../../includes/mnav.php';
    }
}


$userId = $_SESSION['user_id'];




// call membership status at database
$query = "SELECT status AS membership_status, start_date AS membership_start, end_date AS membership_end 
          FROM user_memberships 
          WHERE user_id = ? AND status = 'active' 
          ORDER BY start_date DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$userProfile = [];
if ($row = $result->fetch_assoc()) {
    $userProfile['membership_status'] = $row['membership_status'];
    $userProfile['membership_start'] = $row['membership_start'];
    $userProfile['membership_end'] = $row['membership_end'];
} else {
    $userProfile['membership_status'] = null;
}

// Fetch user details
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $userId);
$query->execute();
$user = $query->get_result()->fetch_assoc();



// For MySQLi, modify the query to use ? instead of :user_id
$ordersQuery = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.total_amount,
        o.status,
        o.created_at,
        GROUP_CONCAT(
            CONCAT(p.name, ' (', oi.quantity, ' x $', 
            FORMAT(oi.price, 2), ')') 
            SEPARATOR '<br>'
        ) AS order_items
    FROM 
        orders o
    LEFT JOIN 
        order_items oi ON o.id = oi.order_id
    LEFT JOIN 
        products p ON oi.product_id = p.id
    WHERE 
        o.user_id = ?
        AND o.status = 'completed'
    GROUP BY 
        o.id, o.total_amount, o.status, o.created_at
    ORDER BY 
        o.created_at DESC
");

// Bind the parameter using bind_param
$ordersQuery->bind_param("i", $userId);
$ordersQuery->execute();
$result = $ordersQuery->get_result();

// Fetch the results
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
 





// Fetch enrolled programs
$query = "SELECT p.id AS participant_id, pr.title AS program_title, pr.description, pr.date, pr.time, pr.location, p.joined_at 
          FROM participants p 
          JOIN programs pr ON p.program_id = pr.id 
          WHERE p.user_id = ?";




$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$enrolledPrograms = [];
while ($row = $result->fetch_assoc()) {
    $enrolledPrograms[] = $row;
}



// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback'])) {
    $userId = $_SESSION['user_id'];
    $feedback = trim($_POST['feedback']);

    if (empty($feedback)) {
        $error = "Feedback cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $userId, $feedback);

        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }

        $stmt->close();
    }
}

$query = "SELECT o.id, o.total_amount, o.created_at, o.order_status, 
       o.tracking_number, o.estimated_delivery_date,
       sa.full_name, sa.address_line1, sa.city, sa.state, sa.postal_code
FROM orders o
JOIN shipping_addresses sa ON o.shipping_address_id = sa.id
WHERE o.user_id = <your_user_id>
ORDER BY o.created_at DESC;"



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | <?= htmlspecialchars($userProfile['username']) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/img/favicon.png">
    
    <!-- Modern CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Custom Styling -->
    <style>
        :root {
            --primary-color:rgb(120, 224, 250);
            --secondary-color:rgb(3, 4, 7);
            --background-light: #f8fafc;
        }
        body {
            background-color: var(--background-light);
            font-family: 'Inter', sans-serif;
            background-image: url('https://images.pexels.com/photos/8709087/pexels-photo-8709087.jpeg');
            background-size: cover;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color),rgb(124, 177, 247));
            color: white;
            padding: 2rem;
            border-radius: 10px;
        }
        .nav-tabs .nav-link {
            color: var(--secondary-color);
        }
        .nav-tabs .nav-link.active {
            background-color: transparent;
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .user-profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
        }
        .border-4 {
    border-width: 4px !important;
        }

        .border-2 {
            border-width: 2px !important;
        }

        .transition {
            transition: all 0.3s ease;
        }

        .hover\:shadow-lg:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .hover\:scale:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }

        .card {
            background: linear-gradient(to bottom,rgb(255, 255, 255),rgb(138, 218, 250));
        }

        .badge {
            font-weight: 500;
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
                <div class="container my-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm hover:shadow-lg transition border-0 rounded-lg">
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <div class="position-relative d-inline-block mb-3">
                                            <img src="../../<?= $user['profile_picture'] ?>" 
                                                alt="user image" 
                                                class="rounded-circle border-4 border-white shadow-sm hover:scale"
                                                style="width: 150px; height: 150px; object-fit: cover;">
                                            <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1 border-2 border-white">
                                                <div class="bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                                            </div>
                                        </div>
                                        
                                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['username']); ?></h4>
                                        
                                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                            <span class="badge bg-light text-muted px-3 py-2 rounded-pill">
                                                <i class="fas fa-user me-1"></i> User
                                            </span>
                                        </div>

                                        <a href="edit_profile.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 py-2 transition">
                                            <i class="fas fa-user-edit me-2"></i>
                                            Edit Profile
                                        </a>
                                    </div>
                                </div>
                    
                
                


                    </div>
                     <br>
                    <!-- Membership Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Membership</h5>
                        </div>
                        <div class="card-body">
                        <?php if ($userProfile['membership_status']): ?>
                            <p><strong>Status:</strong> <?= htmlspecialchars($userProfile['membership_status']) ?></p>
                            <p><strong>Start:</strong> <?= htmlspecialchars($userProfile['membership_start']) ?></p>
                            <p><strong>Expires:</strong> <?= htmlspecialchars($userProfile['membership_end']) ?></p>
                            <?php else: ?>
                            <div class="p-4 text-center border rounded shadow-sm bg-light">
                                <p class="text-muted mb-3 fs-5">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    No active membership
                                </p>
                                <a href="../membership/membership.php" class="btn btn-primary btn-lg px-4 shadow-sm hover:shadow">
                                    <i class="fas fa-tags me-2"></i>
                                    Check Membership
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#orders" data-bs-toggle="tab">Order History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#cart" data-bs-toggle="tab">My Cart</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#programs" data-bs-toggle="tab">Programs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#feedback" data-bs-toggle="tab">Feedback</a>
                                </li>
                            </ul>
                        </div>

                    <!-- Order History Tab -->
                        <div class="card-body tab-content">
                          
                            <div class="tab-pane fade show active" id="orders">
                                <h4>Recent Orders</h4>
                                <?php if (!empty($orders)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col"><i class="fas fa-hashtag"></i> Order ID </th>
                                                    <th scope="col"><i class="fas fa-calendar-alt"></i> Date</th>
                                                    <th scope="col"><i class="fas fa-box"></i> Items</th>
                                                    <th scope="col"><i class="fas fa-list"></i> Total</th>
                                                    <th scope="col"><i class="fas fa-info-circle"></i> Status</th>
                                                    <th scope="col"><i class="fas fa-tools"></i> Actions</th>
                                                </tr>
                                                </thead>
                                            <tbody>
                                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($order['created_at']))) ?></td>
                                    <td><?= $order['order_items'] ?></td>
                                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $order['status'] === 'completed' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                    <a href="order_details.php?id=<?= $order['order_id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">No orders found.</div>
                                <?php endif; ?>
                            </div>

                           <!-- Cart Tab -->
                            <div class="tab-pane fade" id="cart">
                                <h4>Your Cart</h4>
                                <div id="cart-container">
                                    <!-- Cart items will be dynamically loaded here -->
                                </div>
                            </div>

                              <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteConfirmationLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this item from your cart?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                </div>
                                </div>
                            </div>
                            </div>

                            <!-- Toast for Success/Error Messages -->
                            <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
                            <div id="toastMessage" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="toast-header">
                                <strong class="me-auto" id="toastTitle">Message</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                                <div class="toast-body" id="toastBody"></div>
                            </div>
                            </div>



                            <!-- Programs Tab -->
                            <div class="tab-pane fade" id="programs">
                            <h4>Enrolled Programs</h4>
                            <?php if (empty($enrolledPrograms)): ?>
                                <div class="alert alert-info">No programs enrolled yet.</div>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($enrolledPrograms as $program): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5><?= htmlspecialchars($program['program_title']) ?></h5>
                                                <p><?= htmlspecialchars($program['description']) ?></p>
                                                <p>
                                                    <strong>Date:</strong> <?= htmlspecialchars($program['date']) ?> 
                                                    <strong>Time:</strong> <?= htmlspecialchars($program['time']) ?>
                                                </p>
                                                <p><strong>Location:</strong> <?= htmlspecialchars($program['location']) ?></p>
                                                <small>Joined on: <?= htmlspecialchars($program['joined_at']) ?></small>
                                            </div>
                                            <form method="POST" action="cancel_program.php" class="mb-0">
                                                <input type="hidden" name="participant_id" value="<?= htmlspecialchars($program['participant_id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>



                            <!-- Feedback Tab -->
                            <div class="tab-pane fade" id="feedback">
                            <h4>Submit Feedback</h4>
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"> <?php echo $error; ?> </div>
                                <?php endif; ?>

                                <?php if (isset($success)): ?>
                                    <div class="alert alert-success"> <?php echo $success; ?> </div>
                                <?php endif; ?>

                                <form id="feedbackForm" action="profile.php#feedback" method="POST">

                                    <div class="mb-3">
                                        <textarea class="form-control" name="feedback" rows="4" placeholder="Share your thoughts..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Send Feedback</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Fetch cart items when the page loads
        fetchCartItems();

        // Function to fetch cart items
        function fetchCartItems() {
            fetch("fetch_cart.php")
                .then(response => response.json())
                .then(data => {
                    const cartContainer = document.getElementById("cart-container");

                    // If there's an error or no data
                    if (data.error) {
                        cartContainer.innerHTML = `<div class="alert alert-warning">${data.error}</div>`;
                        return;
                    }

                    if (data.length > 0) {
                        let cartHTML = '<table class="table table-bordered">';
                        cartHTML += `
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                        `;

                        data.forEach(item => {
                            const totalPrice = (item.quantity * item.price).toFixed(2);
                            cartHTML += `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.quantity}</td>
                                    <td>RM ${item.price}</td>
                                    <td>RM ${totalPrice}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="deleteCartItem(${item.id})">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });

                        cartHTML += '</tbody></table>';
                        cartContainer.innerHTML = cartHTML;
                    } else {
                        cartContainer.innerHTML = '<div class="alert alert-info">Your cart is empty!</div>';
                    }
                })
                .catch(error => {
                    console.error("Error fetching cart items:", error);
                });
        }

        // Function to delete a cart item
        window.deleteCartItem = function (cartId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const toastElement = new bootstrap.Toast(document.getElementById('toastMessage'));

    // Show the delete confirmation modal
    deleteModal.show();

    // Set up the confirm delete action
    confirmDeleteBtn.onclick = function () {
        fetch(`delete_cart_item.php?id=${cartId}`, { method: "GET" })
            .then(response => response.json())
            .then(result => {
                deleteModal.hide(); // Hide the modal
                if (result.success) {
                    document.getElementById('toastTitle').textContent = "Success";
                    document.getElementById('toastBody').textContent = "Item deleted successfully!";
                    document.getElementById('toastMessage').classList.add("bg-success", "text-white");
                    fetchCartItems(); // Refresh the cart
                } else {
                    document.getElementById('toastTitle').textContent = "Error";
                    document.getElementById('toastBody').textContent = "Failed to delete the item.";
                    document.getElementById('toastMessage').classList.add("bg-danger", "text-white");
                }
                toastElement.show(); // Show the toast message
            })
            .catch(error => {
                deleteModal.hide(); // Hide the modal
                console.error("Error deleting cart item:", error);
                document.getElementById('toastTitle').textContent = "Error";
                document.getElementById('toastBody').textContent = "An error occurred while deleting the item.";
                document.getElementById('toastMessage').classList.add("bg-danger", "text-white");
                toastElement.show(); // Show the toast message
            });
    };
};

    });
</script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const url = window.location.href;
        if (url.includes("#feedback")) {
            const feedbackTab = document.querySelector('a[href="#feedback"]');
            if (feedbackTab) {
                feedbackTab.click();
            }
        }
    });
</script>

</body>
</html>