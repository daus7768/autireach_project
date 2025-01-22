<?php
require_once '../db/db.php';

// Fetch feedback with user details
$sql = "SELECT f.*, u.username, u.role, u.profile_picture 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id 
        ORDER BY f.created_at DESC 
        LIMIT 5";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>AutiReach</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoff.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/logoff.png">
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
<!-- 
    langflow that handle chat bot  -->
    <script src="https://cdn.jsdelivr.net/gh/logspace-ai/langflow-embedded-chat@v1.0.6/dist/build/static/js/bundle.min.js"></script>
</head>


<body>
    <!-- Navbar Start -->
    <div class="container-fluid bg-light position-relative shadow">
        <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0 px-lg-5">
            <a href="" class="navbar-brand font-weight-bold text-secondary" style="font-size: 50px;">
                <img src="../assets/img/logo2.png" alt="Logo" style="width:200px; height: 150px;">
                <span class="text-primary">AutiReach</span>
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                <div class="navbar-nav font-weight-bold mx-auto py-0">
                    <a href="index.php" class="nav-item nav-link active">Home</a>
                    <a href="about.html" class="nav-item nav-link">About</a>
                    <a href="program.html" class="nav-item nav-link">Program</a>
                    <a href="shop.html" class="nav-item nav-link">Shop</a>
                    <a href="gallery.html" class="nav-item nav-link">Gallery</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu rounded-0 m-0">
                            <a href="blog.html" class="dropdown-item">Blog Grid</a>
                            <a href="single.html" class="dropdown-item">Blog Detail</a>
                        </div>
                    </div>
                    <a href="contact.html" class="nav-item nav-link">Contact</a>
                </div>
                <a href="login.html" class="btn btn-primary px-4">Login</a>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

 <!-- langflow chat bot -->
 <div id="chat-container" class="chat-wrapper">
    <div class="chat-toggle" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chat-box" id="chatBox">
        <langflow-chat
            window_title="AutiRena AI"
            flow_id="0a35335b-2c84-4f3c-828a-a3be53636bf2"
            host_url="http://localhost:7860"
        ></langflow-chat>
    </div>
</div>

<style>
.chat-wrapper {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1000;
}

.chat-toggle {
    background: #17a2b8;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: yellow;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.chat-box {
    display: none;
    position: absolute;
    bottom: 60px;
    right: 0;
    width: 450px;
    height: 650px;
    background-image: url('../assets/img/AutiRena4.png');
    background-size: cover;
    background-position: center;
    border-radius: 150px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.chat-box.active {
    display: block;
}

langflow-chat {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    display: block;
}

@media (max-width: 480px) {
    .chat-box {
        width: 300px;
        height: 500px;
    }
}


</style>

<script>
function toggleChat() {
    document.getElementById('chatBox').classList.toggle('active');
}
</script>




    <!-- Header Start -->
    <div class="container-fluid bg-primary px-0 px-md-5 mb-5">
        <div class="row align-items-center px-3">
            <div class="col-lg-6 text-center text-lg-left">
                <h4 class="text-white mb-4 mt-5 mt-lg-0">Bridging for Autism</h4>
                <h1 class="display-3 font-weight-bold text-white">Building a Bridge of Understanding and Care."</h1>
                <p class="text-white mb-4">AutiReach serves as a platform that connects the autism community with the broader society. Its purpose is to foster understanding, provide meaningful support, and promote inclusivity. By building strong connections, AutiReach aims to create a caring environment where individuals with autism can thrive and where communities can actively participate in making a positive difference.</p>
                <a href="https://www.youtube.com/watch?v=TJuwhCIQQTs"  target="_blank" class="btn btn-secondary mt-1 py-3 px-5">Watch Video</a>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <img class="img-fluid mt-5" src="../assets/img/autireach.png" alt="" > <!-- style="width: 9500px; height: 900px;">  if want adjust the image --> 
        
            </div>

           
        </div>
        </div>

        </div>
    </div>
    <!-- Header End -->


    <!-- Facilities Start -->
    <div class="container-fluid pt-5">
        <div class="container pb-3">
            <div class="row">
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-050-puzzle-piece h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Inclusivity Program</h4>
                            <p class="m-0">Bringing communities together to foster understanding, care, and support for individuals with autism.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-022-drum h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Entertainment toys</h4>
                            <p class="m-0">Provide joyfull for autism children</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-030-crayons h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Comunity know about autism</h4>
                            <p class="m-0">Provide resources for community to learn and able guide autism people</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-017-toy-car h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Explore the autism behavior </h4>
                            <p class="m-0">Provide interesting activities for autism children </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-025-sandwich h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Care-giver guide </h4>
                            <p class="m-0">Provide information for care-giver by article at informtion hub </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 pb-1">
                    <div class="d-flex bg-light shadow-sm border-top rounded mb-4" style="padding: 30px;">
                        <i class="flaticon-047-backpack h1 font-weight-normal text-primary mb-3"></i>
                        <div class="pl-4">
                            <h4>Improve community among autism people</h4>
                            <p class="m-0">Autism able to learn the environment by joining the event ad hoc</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Facilities Start -->

    

    <!-- About Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <img class="img-fluid rounded mb-5 mb-lg-0" src="../assets/img/animation8.webp" alt="">
                </div>
                <div class="col-lg-7">
                    <p class="section-title pr-5"><span class="pr-2">Learn About Us</span></p>
                    <h1 class="mb-4">Explore Our Autism Program</h1>
                    <p>"Join our autism programs, featuring community events and specialist-led motivation sessions to foster support, growth, and connection."</p>
                    <div class="row pt-2 pb-4">
                        <div class="col-6 col-md-4">
                            <img class="img-fluid rounded" src="../assets/img/animation3.webp" alt="">
                        </div>
                        <div class="col-6 col-md-8">
                            <ul class="list-inline m-0">
                                <li class="py-2 border-top border-bottom"><i class="fa fa-check text-primary mr-3"></i>MAKNA: Supports autism community events and resources.</li>
                                <li class="py-2 border-bottom"><i class="fa fa-check text-primary mr-3"></i>Datin Rozita: Main sponsor promoting autism awareness.</li>
                                <li class="py-2 border-bottom"><i class="fa fa-check text-primary mr-3"></i>Dr. Norzila Zakaria: Leads motivation and specialist sessions.</li>
                            </ul>
                        </div>
                    </div>
                    <a href="program.html" class="btn btn-primary mt-2 py-2 px-4">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->


    <!-- class Start -->
    <div class="container-fluid pt-5">
        <div class="container">
            <div class="text-center pb-2">
                <p class="section-title px-5"><span class="px-2">Our Objectives</span></p>
                <h1 class="mb-4">Connect Community with Autism</h1>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <div class="card border-0 bg-light shadow-sm pb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation4.webp" alt="">
                        <div class="card-body text-center">
                            <h4 class="card-title">Resources about autism</h4>
                            <p class="card-text">"Access comprehensive autism resources, including articles, videos, and personal stories, to foster understanding and connection."</p>
                        </div>
                        <div class="card-footer bg-transparent py-4 px-5">
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Resource Type</strong></div>
                                <div class="col-6 py-1">Articles</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Resource Type</strong></div>
                                <div class="col-6 py-1">Videos</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Resource Type</strong></div>
                                <div class="col-6 py-1">Shared Experiences</div>
                            </div>
                            <div class="row">
                                <div class="col-6 py-1 text-right border-right"><strong>Updated</strong></div>
                                <div class="col-6 py-1">November 2023</div>
                            </div>
                        </div>
                        <a href="" class="btn btn-primary px-4 mx-auto mb-4">Explore now</a>
                    </div>
                </div>
                <div class="col-lg-4 mb-5">
                    <div class="card border-0 bg-light shadow-sm pb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation5.webp" alt="">
                        <div class="card-body text-center">
                            <h4 class="card-title">Program and event</h4>
                            <p class="card-text">"Join engaging community activities and motivational talks by specialists, fostering support and connection."</p>
                        </div>
                        <div class="card-footer bg-transparent py-4 px-5">
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Event Type</strong></div>
                                <div class="col-6 py-1">Community Engagement</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Specialist Speaker</strong></div>
                                <div class="col-6 py-1">Dr. Norzila Zakaria</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Audience</strong></div>
                                <div class="col-6 py-1">Individuals with Autism & Families</div>
                            </div>
                            <div class="row">
                                <div class="col-6 py-1 text-right border-right"><strong> Ad-hoc fee</strong></div>
                                <div class="col-6 py-1">Free for Members</div>
                            </div>
                        </div>
                        <a href="" class="btn btn-primary px-4 mx-auto mb-4">Join Now</a>
                    </div>
                </div>
                <div class="col-lg-4 mb-5">
                    <div class="card border-0 bg-light shadow-sm pb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation7.webp" alt="">
                        <div class="card-body text-center">
                            <h4 class="card-title">Autism Support Products</h4>
                            <p class="card-text">"Providing life-enhancing autism products, connecting communities with essential support."</p>
                        </div>
                        <div class="card-footer bg-transparent py-4 px-5">
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Product type</strong></div>
                                <div class="col-6 py-1">Sensory tools</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Product type</strong></div>
                                <div class="col-6 py-1">Learning tools</div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-6 py-1 text-right border-right"><strong>Product type</strong></div>
                                <div class="col-6 py-1">Communication Boards</div>
                            </div>
                            <div class="row">
                                <div class="col-6 py-1 text-right border-right"><strong>Product type</strong></div>
                                <div class="col-6 py-1">Therapy Swings</div>
                            </div>
                        </div>
                        <a href="" class="btn btn-primary px-4 mx-auto mb-4">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Class End -->


    <!-- Registration/ rate us Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-5 mb-lg-0">
                    <p class="section-title pr-5"><span class="pr-2">collaboration</span></p>
                    <h1 class="mb-4">Apply to be our partner</h1>
                    <p>"Collaborate with us to bridge the gap between autism and the community, fostering understanding and inclusion."</p>
                    <ul class="list-inline m-0">
                        <li class="py-2"><i class="fa fa-check text-success mr-3"></i>Resources: Access autism-focused tools and education.
                        </li>
                        <li class="py-2"><i class="fa fa-check text-success mr-3"></i>Programs: Engage in inclusive events and sessions.</li>
                        <li class="py-2"><i class="fa fa-check text-success mr-3"></i>Products: Discover tailored autism-supportive items.</li>
                    </ul>
                    <a href="single.html" class="btn btn-primary mt-4 py-2 px-4">Apply now</a>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0">
                        <div class="card-header bg-secondary text-center p-4">
                            <h1 class="text-white m-0">Rate us</h1>
                        </div>
                        <div class="card-body rounded-bottom bg-primary p-5">
                            <form>
                                <div class="form-group">
                                    <input type="text" class="form-control border-0 p-4" placeholder="Your Name" required="required" />
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control border-0 p-4" placeholder="Your Email" required="required" />
                                </div>
                                <div class="form-group">
                                    <select class="custom-select border-0 px-4" style="height: 47px;">
                                        <option selected>Rate</option>
                                        <option value="1">Hightest</option>
                                        <option value="2">Medium</option>
                                        <option value="3">Lowest</option>
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-secondary btn-block border-0 py-3" type="submit">Submit Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Registration / rate us End -->


    <!-- Team Start -->
    <div class="container-fluid pt-5">
        <div class="container">
            <div class="text-center pb-2">
                <p class="section-title px-5"><span class="px-2">Meet and Greet</span></p>
                <h1 class="mb-4">Meet Our sponsor and motivator</h1>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 text-center team mb-5">
                    <div class="position-relative overflow-hidden mb-4" style="border-radius: 100%;">
                        <img class="img-fluid w-100" src="../assets/img/datin.jpg" alt="" >
                        <div
                            class="team-social d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light text-center px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Datin Rozita</h4>
                    <i>In charge in MAKNA organization | Motivator | Sponsor |Expert in Autism behavior</i>
                </div>
                <div class="col-md-6 col-lg-3 text-center team mb-5">
                    <div class="position-relative overflow-hidden mb-4" style="border-radius: 100%;">
                        <img class="img-fluid w-100" src="../assets/img/motivator.jpg" alt="" >
                        <div
                            class="team-social d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light text-center px-0" style="width: 38px; height: 38px;"
                                href="https://www.linkedin.com/in/dr-norzila-zakaria-64977b267/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Dr Norzila Zakaria</h4>
                    <i>Psychiatrist | HRDCorp Certified Trainer | Author | Expert in Child Development | Empowering Families through Mental Health | Helping Parents Unlock Children’s Potential | Financial Literacy & Holistic Family Growth</i>
                </div>
                <div class="col-md-6 col-lg-3 text-center team mb-5">
                    <div class="position-relative overflow-hidden mb-4" style="border-radius: 100%;">
                        <img class="img-fluid w-100" src="../assets/img/prof.jpg" alt="" >
                        <div
                            class="team-social d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light text-center px-0" style="width: 38px; height: 38px;"
                                href="https://www.linkedin.com/in/munaisyah-abdullah-a01797114/"  target="_blank" > <i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Assoc. Prof. Ts. Dr. Munaisyah Abdullah</h4>
                    <i>CIDEX - Centre of Innovative Digital Education & Emerging Tech. | Sponsor | Bridging the Gap between Education & Technology</i>
                </div>
                <div class="col-md-6 col-lg-3 text-center team mb-5">
                    <div class="position-relative overflow-hidden mb-4" style="border-radius: 100%;">
                        <img class="img-fluid w-100" src="../assets/img/firdaus.jpg" alt="" >
                        <div
                            class="team-social d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light text-center mr-2 px-0" style="width: 38px; height: 38px;"
                                href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light text-center px-0" style="width: 38px; height: 38px;"
                                href="https://www.linkedin.com/in/muhammad-firdaus-3b8105270/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <h4>Mr. Firdaus</h4>
                    <i>Motivator | Barchelor of Software Engineering in information technology</i>
                </div>
            </div>
        </div>
    </div>
    <!-- Team End -->


 <!-- Feedback Start -->
<div class="container-fluid py-5">
    <div class="container p-0">
        <div class="text-center pb-2">
            <p class="section-title px-5"><span class="px-2">Feedback</span></p>
            <h1 class="mb-4">What Community Say!</h1>
        </div>
        <div class="owl-carousel testimonial-carousel">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="testimonial-item px-3">
                    <div class="bg-light shadow-sm rounded mb-4 p-4">
                        <h3 class="fas fa-quote-left text-primary mr-3"></h3>
                        <?php echo htmlspecialchars($row['feedback']); ?>
                    </div>
                    <div class="d-flex align-items-center">
                   
                <img class="rounded-circle" 
                src="<?php 
                if (!empty($row['profile_picture'])) {
                    echo '../' . htmlspecialchars($row['profile_picture']);
                } else {
                    echo '../assets/images/profiles/default-avatar.jpg'; // Fallback image
                }
                ?>" 
                style="width: 70px; height: 70px;" 
                alt="User Image">
                        <div class="pl-3">
                            <h5><?php echo htmlspecialchars($row['username']); ?></h5>
                            <i><?php echo htmlspecialchars($row['role']); ?></i>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<!-- Feedback End -->


    <!-- Article Start -->
    <div class="container-fluid pt-5">
        <div class="container">
            <div class="text-center pb-2">
                <p class="section-title px-5"><span class="px-2">Resources hub</span></p>
                <h1 class="mb-4">Latest Articles about Autism</h1>
            </div>
            <div class="row pb-3">
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm mb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation8.webp" alt="">
                        <div class="card-body bg-light text-center p-4">
                            <h4 class="">Assessing Parents' Understanding ASD</h4>
                            <div class="d-flex justify-content-center mb-3">
                                <small class="mr-3"><i class="fa fa-user text-primary"></i> UKM</small>
                                <small class="mr-3"><i class="fa fa-folder text-primary"></i> e-Bangi</small>
                                <small class="mr-3"><i class="fa fa-comments text-primary"></i> V.21, Issue 3</small>
                            </div>
                            <p>"Addressing Autism Spectrum Disorder requires early diagnosis and a comprehensive understanding of its diverse symptoms and behaviors."</p>
                            <a href="http://journalarticle.ukm.my/24294/1/88_96%20712052552101PB.pdf" target="_blank" class="btn btn-primary px-4 mx-auto my-2">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm mb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation9.webp" alt="">
                        <div class="card-body bg-light text-center p-4">
                            <h4 class="">Technical Report ASD
                                Research in Malaysia</h4>
                            <div class="d-flex justify-content-center mb-3">
                                <small class="mr-3"><i class="fa fa-user text-primary"></i>GOV</small>
                                <small class="mr-3"><i class="fa fa-folder text-primary"></i> iku</small>
                                <small class="mr-3"><i class="fa fa-comments text-primary"></i> MOH/S/IKU/40.15</small>
                            </div>
                            <p>"Autism awareness is rising in Malaysia, but a lack of compiled local research hinders effective assessment and support development for ASD."</p>
                            <a href="https://iku.nih.gov.my/images/teknikal-report/tr-asdr-in-malaysia.pdf" target="_blank" class="btn btn-primary px-4 mx-auto my-2">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm mb-2">
                        <img class="card-img-top mb-2" src="../assets/img/animation10.webp" alt="">
                        <div class="card-body bg-light text-center p-4">
                            <h4 class="">Management of ASD in Children and Adolescents</h4>
                            <div class="d-flex justify-content-center mb-3">
                                <small class="mr-3"><i class="fa fa-user text-primary"></i>MaHTAS</small>
                                <small class="mr-3"><i class="fa fa-folder text-primary"></i> PAK</small>
                                <small class="mr-3"><i class="fa fa-comments text-primary"></i> MOH/PAK/279.14</small>
                            </div>
                            <p>"ASD prevalence in Malaysia is estimated at 1.6 per 1,000, highlighting a need for further local epidemiological studies."</p>
                            <a href="https://www.moh.gov.my/moh/attachments/CPG%202014/CPG%20Management%20of%20Autism%20Spectrum%20Disofer%20in%20Children%20and%20Adolescents.pdf" target="_blank" class="btn btn-primary px-4 mx-auto my-2">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Article End -->

 

 

    <!-- Footer Start -->
    <div class="container-fluid bg-secondary text-white mt-5 py-5 px-sm-3 px-md-5">
        <div class="row pt-5">
            <div class="col-lg-3 col-md-6 mb-5">
                <a href="" class="navbar-brand font-weight-bold text-primary m-0 mb-4 p-0" style="font-size: 40px; line-height: 40px;">
                    <img src="../assets/img/logo.png" alt="Logo" style="width:150px; height: 150px;">
                    <span class="text-white">AutiReach</span>
                </a>
                <p>"AutiReach is a dedicated platform bridging the gap between the autism community and society, providing resources, programs, and products to empower and support individuals with autism."</p>
                <div class="d-flex justify-content-start mt-4">
                    <a class="btn btn-outline-primary rounded-circle text-center mr-2 px-0"
                        style="width: 38px; height: 38px;" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-outline-primary rounded-circle text-center mr-2 px-0"
                        style="width: 38px; height: 38px;" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-outline-primary rounded-circle text-center mr-2 px-0"
                        style="width: 38px; height: 38px;" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="btn btn-outline-primary rounded-circle text-center mr-2 px-0"
                        style="width: 38px; height: 38px;" href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h3 class="text-primary mb-4">Get In Touch</h3>
                <div class="d-flex">
                    <h4 class="fa fa-map-marker-alt text-primary"></h4>
                    <div class="pl-3">
                        <h5 class="text-white">Address</h5>
                        <p>UNIKL MIIT</p>
                    </div>
                </div>
                <div class="d-flex">
                    <h4 class="fa fa-envelope text-primary"></h4>
                    <div class="pl-3">
                        <h5 class="text-white">Email</h5>
                        <p>mfirdaus.rosli@s.unikl.edu.com</p>
                    </div>
                </div>
                <div class="d-flex">
                    <h4 class="fa fa-phone-alt text-primary"></h4>
                    <div class="pl-3">
                        <h5 class="text-white">Phone</h5>
                        <p>+60-166061049</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h3 class="text-primary mb-4">Quick Links</h3>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-white mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Home</a>
                    <a class="text-white mb-2" href="about.html"><i class="fa fa-angle-right mr-2"></i>About</a>
                    <a class="text-white mb-2" href="program.html"><i class="fa fa-angle-right mr-2"></i>Program</a>
                    <a class="text-white mb-2" href="shop.html"><i class="fa fa-angle-right mr-2"></i>Shop</a>
                    <a class="text-white mb-2" href="gallery.html"><i class="fa fa-angle-right mr-2"></i>Gallery</a>
                    <a class="text-white" href="contact.html"><i class="fa fa-angle-right mr-2"></i>Contact</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h3 class="text-primary mb-4">Get Our Notification</h3>
                <form action="">
                    <div class="form-group">
                        <input type="text" class="form-control border-0 py-4" placeholder="Your Name" required="required" />
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control border-0 py-4" placeholder="Your Email"
                            required="required" />
                    </div>
                    <div>
                        <button class="btn btn-primary btn-block border-0 py-3" type="submit">Submit Now</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="container-fluid pt-5" style="border-top: 1px solid rgba(23, 162, 184, .2);;">
            <p class="m-0 text-center text-white">
                &copy; <a class="text-primary font-weight-bold" href="#">AutiReach</a>. All Rights Reserved and Updated
                by
                <a class="text-primary font-weight-bold" target="_blank" href="https://www.linkedin.com/in/muhammad-firdaus-3b8105270/">Muhd Firdaus b. Rosli</a>
            </p>
        </div>
    </div>



    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary p-3 back-to-top"><i class="fa fa-angle-double-up"></i></a>





    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="../lib/isotope/isotope.pkgd.min.js"></script>
    <script src="../lib/lightbox/js/lightbox.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="../mail/jqBootstrapValidation.min.js"></script>
    <script src="../mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="../assets/js/main.js"></script>
      

    <!-- chatbot ai  -->
    <script>
    async function sendMessage(event) {
    if (event.key === "Enter") {
      const userMessage = document.getElementById("user-message").value;
      document.getElementById("user-message").value = "";

      // Display user message
      const display = document.getElementById("chat-display");
      display.innerHTML += `<p><strong>User:</strong> ${userMessage}</p>`;

      // Send message to backend
      const response = await fetch("chatbot_backend.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ user_message: userMessage }),
      });

      const result = await response.json();
      display.innerHTML += `<p><strong>AutiRena:</strong> ${result.reply}</p>`;
    }
  }
</script>





</body>

</html>