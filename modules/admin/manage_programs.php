<?php
session_start();
require_once '../../db/db.php';
include '../../includes/adminnav.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? 0.00;

    $image = null;
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../../assets/" . $image);
    }

    if ($action === "add") {
        $sql = "INSERT INTO programs (title, description, date, time, location, price, image, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdsd", $title, $description, $date, $time, $location, $price, $image, $_SESSION['user_id']);
    } elseif ($action === "edit") {
        if ($image) {
            $sql = "UPDATE programs SET title = ?, description = ?, date = ?, time = ?, location = ?, price = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssdsd", $title, $description, $date, $time, $location, $price, $image, $id);
        } else {
            $sql = "UPDATE programs SET title = ?, description = ?, date = ?, time = ?, location = ?, price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssd", $title, $description, $date, $time, $location, $price, $id);
        }
    } elseif ($action === "delete") {
        $sql = "DELETE FROM programs WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    }

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Program {$action}ed successfully!',
                confirmButtonColor: '#4CAF50',
            }).then(() => {
                window.location.href = 'manage_programs.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to {$action} program. Please try again.',
                confirmButtonColor: '#f44336',
            }).then(() => {
                window.location.href = 'manage_programs.php';
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
    <title>Manage Programs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
   
    <style>
       :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --background-light: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --border-radius: 16px;
            --transition-speed: 0.3s;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            
        }

        body {
            background: var(--background-light);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            transition: transform var(--transition-speed) ease;
            z-index: 50;
        }

        .container {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }
        table {
            font-size: 0.9rem;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .modal-content {
            border-radius: 10px;
        }
        .btn-primary, .btn-danger, .btn-warning {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<main class="main-content"> 
         
<div class="container mt-4" >

    <h1 class="text-center mb-4">Manage Programs</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#programModal" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add New Program
    </button>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Price</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM programs";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['time']}</td>
                    <td>{$row['location']}</td>
                    <td>RM {$row['price']}</td>
                    <td>";
                if ($row['image']) {
                    echo "<img src='../../assets/{$row['image']}' alt='Program Image' width='80' class='rounded'>";
                } else {
                    echo "<span class='text-muted'>No Image</span>";
                }
                echo "</td>
                    <td>
                        <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#programModal' onclick='openEditModal(" . json_encode($row) . ")'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class='btn btn-danger btn-sm' onclick='deleteProgram({$row['id']})'>
                            <i class='fas fa-trash'></i>
                        </button>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalLabel">Add Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="programId">
                    <input type="hidden" name="action" id="programAction" value="add">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
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


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    function openAddModal() {
        document.getElementById('programModalLabel').textContent = "Add Program";
        document.getElementById('programAction').value = "add";
        document.getElementById('programId').value = "";
        document.getElementById('title').value = "";
        document.getElementById('description').value = "";
        document.getElementById('date').value = "";
        document.getElementById('time').value = "";
        document.getElementById('location').value = "";
        document.getElementById('price').value = "";
        document.getElementById('image').value = "";
    }

    function openEditModal(program) {
        document.getElementById('programModalLabel').textContent = "Edit Program";
        document.getElementById('programAction').value = "edit";
        document.getElementById('programId').value = program.id;
        document.getElementById('title').value = program.title;
        document.getElementById('description').value = program.description;
        document.getElementById('date').value = program.date;
        document.getElementById('time').value = program.time;
        document.getElementById('location').value = program.location;
        document.getElementById('price').value = program.price;
    }

    function deleteProgram(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'manage_programs.php';

                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'delete';

                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;

                form.appendChild(inputAction);
                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

</div>
</main>
</body>
</html>
