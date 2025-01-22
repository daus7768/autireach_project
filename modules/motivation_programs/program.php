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

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'community') {
        include '../../includes/cnav.php';
    } elseif ($_SESSION['role'] == 'member') {
        include '../../includes/mnav.php';
    }
}



// Fetch programs from the database
$sql = "SELECT id, title, description, date, time, location, price, image FROM programs ORDER BY date ASC";
$result = $conn->query($sql);
$programs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - AutiReach</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/img/logoff.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/img/logoff.png">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS (optional, for responsive design) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

     <!-- Google Fonts -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    

    
    <!-- Tailwind CSS for additional styling -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    
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

        .gradient-background {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .event-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        
        .event-image {
            object-fit: cover;
            object-position: center;
            height: 250px;
            width: 100%;
            border-radius: 12px;
        }

        .page-heading-shows-events {
            background-color: #3b82f6;
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .shows-events-tabs {
            padding: 50px 0;
        }

        .heading-tabs ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .heading-tabs ul li {
            margin: 0 15px;
        }

        .heading-tabs ul li a {
            text-decoration: none;
            color: #3b82f6;
            font-weight: bold;
            padding: 10px 20px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .heading-tabs ul li a.active, .heading-tabs ul li a:hover {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        .event-item {
            background-color: white;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .event-item .left-content {
            padding: 20px;
        }

        .event-item .thumb img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .event-item .right-content {
            padding: 20px;
        }

        .right-content ul {
            list-style: none;
            padding: 0;
        }

        .right-content ul li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .right-content ul li i {
            margin-right: 10px;
            color: #3b82f6;
        }

        .main-dark-button a {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .main-dark-button a:hover {
            background-color: #2563eb;
        }
        .tab-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .tab-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: white;
        }

        /* Soft shadows and modern look */
        .soft-shadow {
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="background-container">
        <img src="../../assets/img/programbackground.webp" alt="Background">
    </div>
    <!-- Page Heading -->
    <div class="page-heading-shows-events">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Our Programs</h2>
                    <span>Check out upcoming and past programs designed for the autism community.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs Section -->
    <div class="shows-events-tabs">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="heading-tabs">
                        <ul>
                            <li><a href="#upcoming" class="active">Upcoming</a></li>
                            <li><a href="#past">Past</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Upcoming Programs -->
                <div id="upcoming" class="tab-content">
                    <div class="row">
                        <?php foreach ($programs as $program): ?>
                            <?php if (strtotime($program['date']) >= strtotime(date('Y-m-d'))): ?>
                                <div class="col-lg-12">
                                    <div class="event-item">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="left-content">
                                                    <h4><?= htmlspecialchars($program['title']) ?></h4>
                                                    <p><?= htmlspecialchars(substr($program['description'], 0, 100)) ?>...</p>
                                                    <div class="main-dark-button">
                                                        <a href="program_details.php?id=<?= $program['id'] ?>">Discover More</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="thumb">
                                                <?php if (!empty($program['image'])): ?>
                                                    <img src="../../assets/<?= htmlspecialchars($program['image']) ?>" alt="Program Image">
                                                <?php else: ?>
                                                    <img src="../../assets/images/default-program.jpg" alt="Default Program Image">
                                                <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="right-content">
                                                    <ul>
                                                        <li>
                                                            <i class="fas fa-calendar-alt mr-2"></i>
                                                            <h6><?= htmlspecialchars(date('M d l', strtotime($program['date']))) ?><br><?= htmlspecialchars($program['time']) ?></h6>
                                                        </li>
                                                        <li>
                                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                                            <span><?= htmlspecialchars($program['location']) ?></span>
                                                        </li>
                                                        <li>
                                                            <i class="fas fa-tag mr-2"></i>
                                                            <span><?= $program['price'] == 0.00 ? "Free" : "RM" . htmlspecialchars($program['price']) ?></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Past Programs -->
                <div id="past" class="tab-content" style="display:none;">
                    <div class="row">
                        <?php foreach ($programs as $program): ?>
                            <?php if (strtotime($program['date']) < strtotime(date('Y-m-d'))): ?>
                                <div class="col-lg-12">
                                    <div class="event-item">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="left-content">
                                                    <h4><?= htmlspecialchars($program['title']) ?></h4>
                                                    <p><?= htmlspecialchars(substr($program['description'], 0, 100)) ?>...</p>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="thumb">
                                                <?php if (!empty($program['image'])): ?>
                                                    <img src="../../assets/<?= htmlspecialchars($program['image']) ?>" alt="Program Image">
                                                <?php else: ?>
                                                    <img src="../../assets/images/default-program.jpg" alt="Default Program Image">
                                                <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="right-content">
                                                    <ul>
                                                        <li>
                                                            <i class="fas fa-calendar-alt mr-2"></i>
                                                            <h6><?= htmlspecialchars(date('M d l', strtotime($program['date']))) ?><br><?= htmlspecialchars($program['time']) ?></h6>
                                                        </li>
                                                        <li>
                                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                                            <span><?= htmlspecialchars($program['location']) ?></span>
                                                        </li>
                                                        <li>
                                                            <i class="fas fa-tag mr-2"></i>
                                                            <span><?= $program['price'] == 0.00 ? "Free" : "RM" . htmlspecialchars($program['price']) ?></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional: JavaScript for Tab Switching -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.heading-tabs ul li a');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Hide all tab contents
                    tabContents.forEach(content => content.style.display = 'none');

                    // Show selected tab content
                    const targetId = this.getAttribute('href').substring(1);
                    document.getElementById(targetId).style.display = 'block';
                });
            });
        });
    </script>
</body>
</html>