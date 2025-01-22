<?php
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0.00;
    $stock = $_POST['stock'] ?? 0;

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../../assets/" . $image);
    }

    if ($action === "add") {
        $sql = "INSERT INTO products (name, description, price, stock, image, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image, $_SESSION['user_id']);
    } elseif ($action === "edit") {
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image, $id);
    } elseif ($action === "delete") {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    }

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Product {$action}ed successfully!',
                confirmButtonColor: '#4CAF50',
            }).then(() => {
                window.location.href = 'manage_product.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to {$action} product. Please try again.',
                confirmButtonColor: '#f44336',
            }).then(() => {
                window.location.href = 'manage_product.php';
            });
        </script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1E3A8A;
            --secondary-color: #2563EB;
            --accent-color: #10B981;
            --background-color: #F3F4F6;
            --text-color: #1F2937;
            --card-bg: #FFFFFF;
            --hover-color: #2C3E50;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-container {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.2rem;
            margin-top: 0rem;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-input {
            flex-grow: 1;
            margin-right: 1rem;
            border: 2px solid var(--background-color);
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-add {
            background-color: var(--accent-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .btn-add:hover {
            background-color: var(--hover-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid var(--background-color);
            vertical-align: middle;
            padding: 1rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background-color: #DC2626;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }

        .container {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }
    </style>
</head>
<body>
<main class="main-content"> 
    <div class="container">
    
        <h1 class="page-title">Product Management Dashboard</h1>
        
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" onkeyup="filterTable()" placeholder="🔍 Search products...">
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add New Product
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM products";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>" . substr($row['description'], 0, 50) . "...</td>
                            <td>RM " . number_format($row['price'], 2) . "</td>
                            <td>{$row['stock']}</td>
                            <td><img src='../../assets/{$row['image']}' alt='Product Image' class='product-image'></td>
                            <td>
                                <button class='btn btn-action btn-edit' data-bs-toggle='modal' data-bs-target='#productModal' onclick='openEditModal(" . json_encode($row) . ")'>Edit</button>
                                <button class='btn btn-action btn-delete' onclick='deleteProduct({$row['id']})'>Delete</button>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal (Same as previous code) -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <!-- Modal content remains the same as in the original code -->
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="manage_products.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="productId">
                        <input type="hidden" name="action" id="productAction" value="add">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts (Same as previous code) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript functions remain the same as in the original code
        function openAddModal() {
            document.getElementById('productModalLabel').textContent = "Add Product";
            document.getElementById('productAction').value = "add";
            document.getElementById('productId').value = "";
            document.getElementById('name').value = "";
            document.getElementById('description').value = "";
            document.getElementById('price').value = "";
            document.getElementById('stock').value = "";
            document.getElementById('image').value = "";
        }

        function openEditModal(product) {
            document.getElementById('productModalLabel').textContent = "Edit Product";
            document.getElementById('productAction').value = "edit";
            document.getElementById('productId').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('stock').value = product.stock;
        }

        function deleteProduct(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'manage_products.php';
                    form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function filterTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toUpperCase();
            let table = document.getElementById('productsTable');
            let tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td');
                let match = false;
                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        if (td[j].textContent.toUpperCase().indexOf(filter) > -1) {
                            match = true;
                        }
                    }
                }
                tr[i].style.display = match ? '' : 'none';
            }
        }
    </script>
    </main>
</body>
</html>
