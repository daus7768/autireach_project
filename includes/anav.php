<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutiReach Horizontal Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --background-color: #ffffff;
            --text-color: #2c3e50;
            --hover-color: #f1f3f4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--background-color);
            padding: 15px 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            margin-right: 30px;
        }

        .navbar-logo img {
            max-height: 40px;
            margin-right: 10px;
        }

        .navbar-logo span {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
        }

        .navbar-menu a {
            text-decoration: none;
            color: var(--text-color);
            margin: 0 15px;
            position: relative;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
        }

        .navbar-menu a i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .navbar-menu a:hover {
            background-color: var(--hover-color);
            color: var(--primary-color);
        }

        .navbar-menu a.active {
            background-color: var(--primary-color);
            color: white;
        }

        .navbar-menu a.active i {
            color: white;
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .navbar-right a {
            text-decoration: none;
            color: var(--text-color);
            display: flex;
            align-items: center;
            margin-left: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .navbar-right a i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .navbar-right a:hover {
            background-color: var(--hover-color);
        }

        .navbar-toggle {
            display: none;
        }

        @media screen and (max-width: 1024px) {
            .navbar {
                flex-direction: column;
                padding: 15px;
            }

            .navbar-left {
                width: 100%;
                justify-content: space-between;
                align-items: center;
            }

            .navbar-menu {
                flex-direction: column;
                width: 100%;
                display: none;
            }

            .navbar-menu.show {
                display: flex;
            }

            .navbar-menu a {
                margin: 10px 0;
                width: 100%;
                text-align: center;
            }

            .navbar-right {
                flex-direction: column;
                width: 100%;
            }

            .navbar-right a {
                margin: 10px 0;
                width: 100%;
                text-align: center;
            }

            .navbar-toggle {
                display: block;
                cursor: pointer;
            }

            .navbar-toggle-icon {
                display: block;
                width: 25px;
                height: 3px;
                background-color: var(--text-color);
                position: relative;
                transition: background-color 0.3s ease;
            }

            .navbar-toggle-icon::before,
            .navbar-toggle-icon::after {
                content: '';
                position: absolute;
                width: 25px;
                height: 3px;
                background-color: var(--text-color);
                transition: all 0.3s ease;
            }

            .navbar-toggle-icon::before {
                top: -7px;
            }

            .navbar-toggle-icon::after {
                top: 7px;
            }

            /* Hamburger menu animation */
            .navbar-toggle.open .navbar-toggle-icon {
                background-color: transparent;
            }

            .navbar-toggle.open .navbar-toggle-icon::before {
                transform: rotate(45deg);
                top: 0;
            }

            .navbar-toggle.open .navbar-toggle-icon::after {
                transform: rotate(-45deg);
                top: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <div class="navbar-logo">
                <img src="../../assets/img/logo2.png" alt="AutiReach">
                <span>AutiReach</span>
            </div>

            <div class="navbar-toggle">
                <div class="navbar-toggle-icon"></div>
            </div>

            <div class="navbar-menu">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_programs.php">
                    <i class="fas fa-chalkboard-teacher"></i> Programs
                </a>
                <a href="admin_payment.php">
                    <i class="fas fa-check-circle"></i> Payment Management
                </a>
                <a href="manage_products.php">
                    <i class="fas fa-shopping-cart"></i> Products
                </a>
                <a href="manage_blog.php">
                    <i class="fas fa-blog"></i> Blog
                </a>
                <a href="admin_memberships.php">
                    <i class="fas fa-users"></i> memberships management
                </a>
                <a href="manage_user.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </div>
        </div>

        <div class="navbar-right">
            <a href="../../modules/auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get the current page filename
            const currentPage = window.location.pathname.split('/').pop();

            // Select all navbar menu links
            const navLinks = document.querySelectorAll('.navbar-menu a');

            // Remove active class from all links first
            navLinks.forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to the link matching the current page
            navLinks.forEach(link => {
                // Get the href attribute and extract the filename
                const linkPage = link.getAttribute('href').split('/').pop();

                // Compare the current page with the link's page
                if (currentPage === linkPage) {
                    link.classList.add('active');
                }
            });

            // Navbar toggle functionality
            const navbarToggle = document.querySelector('.navbar-toggle');
            const navbarMenu = document.querySelector('.navbar-menu');

            navbarToggle.addEventListener('click', () => {
                navbarMenu.classList.toggle('show');
                
                // Toggle hamburger menu icon animation
                navbarToggle.classList.toggle('open');
            });
        });
    </script>
</body>
</html>