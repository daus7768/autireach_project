<?php
session_start();
require_once '../../db/db.php';
// include '../../includes/cnav.php';



// Authentication check (optional, can be customized)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login or show a message
    header("Location: ../../pages/login.html");
    exit;
}


// ensure the rile is defined in the session
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'community') {
        include '../../includes/cnav.php';
    } elseif ($_SESSION['role'] == 'member') {
        include '../../includes/mnav.php';
    }
    else {
        echo "User role not defined in session. Please log in again.";
    }
}

// Fetch products from the database
$sql = "SELECT id, name, description, price, stock, image FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - AutiReach</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  

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

        .shop-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            margin-bottom: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
        }

        .shop-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(180deg, transparent, rgba(248, 249, 250, 0.9));
        }

        .search-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .search-container.scrolled {
            padding: 1rem 0;
            background: rgba(255, 255, 255, 0.98);
        }

        .search-bar {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-bar input {
            height: var(--search-height);
            border-radius: calc(var(--search-height) / 2);
            border: 2px solid transparent;
            padding: 0.8rem 1.5rem 0.8rem 3.5rem;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            width: 100%;
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 4px 20px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .search-bar input::placeholder {
            color: #adb5bd;
        }

        .product-grid {
            padding-top: 2rem;
        }

        .product-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            background: white;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .product-card .product-image {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-card-body {
            padding: 20px;
        }

        .product-card .btn-view {
            background-color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
        }

        .product-card .btn-view:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .badge-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .no-results {
            padding: 3rem;
            text-align: center;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        .no-results i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        /* Cart Icon Styling */
.cart-icon {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1000;
    background-color: #f4f4f4;
    border: 2px solid #ccc;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: transform 0.3s ease, background-color 0.3s ease;
}

.cart-icon:hover {
    transform: scale(1.1);
    background-color: #eaeaea;
}

.cart-icon i {
    color: #333;
    font-size: 24px;
}

.cart-icon .cart-count {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: #ff0000;
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 50%;
    padding: 2px 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Cart Modal Styling */
.cart-modal {
    position: fixed;
    right: 20px;
    bottom: 80px;
    z-index: 999;
    background-color: blue;
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    display: none; /* Hidden by default */
    flex-direction: column;
    padding: 15px;
    animation: fadeIn 0.3s ease-in-out;
}

.cart-modal.active {
    display: flex;
}

.cart-modal h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.cart-modal .cart-total {
    font-size: 16px;
    margin-top: 15px;
    font-weight: bold;
}

.checkout-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 15px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.checkout-btn:hover {
    background-color: #0056b3;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .message-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(30, 54, 214, 0.1);
            animation: slideIn 0.9s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .message-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .message-header h3 {
            color: #1a1a1a;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .message-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .cart-items {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            min-height: 100px;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            flex-direction: column;
        }

        @media (min-width: 640px) {
            .button-group {
                flex-direction: row;
            }
        }

        .checkout-btn {
            flex: 1;
            padding: 0.8rem 1.5rem;
            border-radius: 999px;
            border: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.9s ease;
           
        }

        .primary-btn {
            background: #10b981;
            color: white;
        }

        .primary-btn:hover {
            background: #059669;
        }

        .secondary-btn {
            background: white;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        .secondary-btn:hover {
            background: #f3f4f6;
        }

        /* Success icon styles */
        .success-icon {
            width: 48px;
            height: 48px;
            background: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .success-icon svg {
            width: 24px;
            height: 24px;
            color: #10b981;
        }
    </style>
</head>
<body>
<div class="background-container">
        <img src="../../assets/img/productbackground.webp" alt="Background">
    </div>
    <div class="shop-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">AutiReach Shop</h1>
            <p class="lead mb-0">Discover Our Unique Collection</p>
        </div>
    </div>

    <div class="search-container">
        <div class="container">
            <div class="search-bar">
                <i class="bi bi-search search-icon"></i>
                <form method="GET" class="d-flex w-100">
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Search for products..." 
                        class="form-control"
                        autocomplete="off"
                    >
                </form>
            </div>
        </div>
    </div>

    <div class="container product-grid">
        <div class="row" id="product-list">
            <!-- Initially empty, will load products based on search -->
        </div>
    </div>

    <div class="container">
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="product-card position-relative">
                        <?php if ($product['stock'] <= 5): ?>
                            <span class="badge bg-warning badge-stock">Low Stock</span>
                        <?php endif; ?>
                        <img src="../../assets/<?= htmlspecialchars($product['image']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>" 
                        class="img-fluid product-image w-100">

                        <div class="product-card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted">
                                <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary mb-0">
                                    RM<?= number_format($product['price'], 2) ?>
                                </span>
                                <a href="product_details.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-view btn-sm text-white">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<!-- for display the cart icon -->

    <!-- Cart Icon -->
<div class="cart-icon" onclick="toggleCart()">
<i class="fa-solid fa-cart-shopping fa-beat-fade" style="color: #035ea7;"></i>
  
</div>
<p></p>
<!-- Cart Modal -->
<div class="cart-modal" id="cartModal">

        <div class="message-content">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="message-header">
                <h3>Ready to Checkout!</h3>
                <p>Your items are ready for checkout. Please proceed to complete your purchase.</p>
            </div>
           
            <button id="checkout-button" class="checkout-btn" onclick="startCheckout()">Let go checkout</button>
            </div>
        </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const searchInput = document.getElementById('search-input');
        const productList = document.getElementById('product-list');
        const searchContainer = document.querySelector('.search-container');

        // Sticky search bar effect
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                searchContainer.classList.add('scrolled');
            } else {
                searchContainer.classList.remove('scrolled');
            }
        });

        // Function to fetch and display products
        const fetchProducts = (search) => {
            if (!search.trim()) {
                productList.innerHTML = ''; // Clear product list if search is empty
                return;
            }

            fetch(`search_products.php?search=${search}`)
                .then(response => response.json())
                .then(products => {
                    productList.innerHTML = ''; // Clear current products

                    if (products.length > 0) {
                        products.forEach(product => {
                            const productCard = `
                                <div class="col-md-4">
                                    <div class="product-card position-relative">
                                        ${product.stock <= 5 ? '<span class="badge bg-warning badge-stock">Low Stock</span>' : ''}
                                        <img src="../../assets/${product.image}" alt="${product.name}" class="img-fluid product-image w-100">
                                        <div class="product-card-body">
                                            <h5 class="card-title">${product.name}</h5>
                                            <p class="card-text text-muted">${product.description.substr(0, 100)}...</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h5 text-primary mb-0">RM${parseFloat(product.price).toFixed(2)}</span>
                                                <a href="product_details.php?id=${product.id}" class="btn btn-view btn-sm text-white">
                                                    <i class="bi bi-eye me-1"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            productList.insertAdjacentHTML('beforeend', productCard);
                        });
                    } else {
                        productList.innerHTML = `
                            <div class="col-12">
                                <div class="no-results">
                                    <i class="bi bi-search mb-3"></i>
                                    <h3>No products found</h3>
                                    <p class="text-muted">Try adjusting your search terms or browse our full collection.</p>
                                </div>
                            </div>
                        `;
                    }
                });
        };

        // Event listener for input changes with debounce
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchProducts(e.target.value);
            }, 300); // Debounce for 300ms
        });
    </script>
<script>
    // Cart Items Array
// Toggle Cart Modal Visibility
function toggleCart() {
    const cartModal = document.getElementById('cartModal');
    cartModal.classList.toggle('active');
}

// Start Checkout Process
function startCheckout() {
    // Redirect to checkout.php
    window.location.href = "cart.php";
}
    </script>
</body>
</html>