<style>
.dropdown-menu {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
}

.dropdown:hover .dropdown-menu,
.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-item:hover {
    background-color: #f1f1f1;
}
</style>
<head>
    <meta charset="utf-8">
    <title>AutiReach</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="../assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Handlee&family=Nunito&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Flaticon Font --> 
    <link href="../lib/flaticon/font/flaticon.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="../lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<!-- Navbar Start -->
<div class="container-fluid bg-light position-relative shadow">
        <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0 px-lg-5">
            <a href="" class="navbar-brand font-weight-bold text-secondary" style="font-size: 50px;">
                <img src="../../assets/img/logo2.png" alt="Logo" style="width:200px; height: 150px;">
                <span class="text-primary">AutiReach</span>
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                <div class="navbar-nav font-weight-bold mx-auto py-0">
                    <a href="../../pages/dashboard_community.html" class="nav-item nav-link ">Home</a>
                    <a href="../../pages/cabout.html" class="nav-item nav-link">About</a>
                    <a href="../../modules/motivation_programs/program.php" class="nav-item nav-link" >Program</a>
                    <a href="../../modules/ecommerce/shop.php" class="nav-item nav-link">Shop</a>
                    <a href="../../modules/membership/membership.php" class="nav-item nav-link">Membership</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu rounded-0 m-0">
                            <a href="../../modules/blog/blog.php" class="dropdown-item">Awareness Hub</a>
                            <a href="../../modules/donation/donate.php" class="dropdown-item">Donation</a>
                        </div>
                        </div>
                    
                    <a href="../../modules/users/profile.php" class="nav-item nav-link">Profile</a>
                </div>
                <a href="../../modules/auth/logout.php" class="btn btn-primary px-4">Logout</a>


                
            </div>
        </nav>
    </div>

   


    <script>
 document.addEventListener('DOMContentLoaded', () => {
    // Get the current page filename and full path
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop();

    // Select all navbar links (including dropdown items)
    const navLinks = document.querySelectorAll('.navbar-nav .nav-item.nav-link, .navbar-nav .dropdown-item');

    // Dropdown toggle functionality
    const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior
            
            // Toggle the dropdown menu
            const dropdownMenu = toggle.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.toggle('show');
                
                // Toggle aria attribute for accessibility
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !expanded);
            }
            
            // Close other open dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    const otherDropdownMenu = otherToggle.nextElementSibling;
                    if (otherDropdownMenu && otherDropdownMenu.classList.contains('dropdown-menu')) {
                        otherDropdownMenu.classList.remove('show');
                        otherToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        dropdownToggles.forEach(toggle => {
            const dropdownMenu = toggle.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                if (!toggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });

    // Remove active class from all links first
    navLinks.forEach(link => {
        link.classList.remove('active');
    });

    // Add active class to the link matching the current page
    navLinks.forEach(link => {
        // Get the href attribute 
        const href = link.getAttribute('href');
        
        if (href) {
            // Extract filename from href
            const linkPage = href.split('/').pop();

            // Check if current page matches link page or if link contains current path
            if (currentPage === linkPage || currentPath.includes(href)) {
                // Add active class to the link
                link.classList.add('active');

                // If the link is in a dropdown, also activate the dropdown toggle
                const dropdownParent = link.closest('.dropdown');
                if (dropdownParent) {
                    const dropdownToggle = dropdownParent.querySelector('.dropdown-toggle');
                    if (dropdownToggle) {
                        dropdownToggle.classList.add('active');
                    }
                }
            }
        }
    });

    // Responsive navbar toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('#navbarCollapse');

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', () => {
            navbarCollapse.classList.toggle('show');
            navbarToggler.classList.toggle('collapsed');
        });
    }
});
</script>

