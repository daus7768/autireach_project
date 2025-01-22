<?php
session_start();
require_once '../../db/db.php';
// include '../../includes/cnav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'community') {
        include '../../includes/cnav.php';
    } elseif ($_SESSION['role'] == 'member') {
        include '../../includes/mnav.php';
    }
}


$userId = $_SESSION['user_id'];

// Fetch active membership for the user
$membershipQuery = "SELECT * FROM user_memberships WHERE user_id = ? AND status = 'active' AND end_date > NOW() LIMIT 1";
$stmt = $conn->prepare($membershipQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$activeMembership = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch available membership plans
$plansQuery = "SELECT * FROM membership_plans";
$plansResult = $conn->query($plansQuery);
$plans = [];
while ($row = $plansResult->fetch_assoc()) {
    $plans[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Plans - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1A73E8;
            --secondary-color: #34A853;
            --text-color: #202124;
            --background-color: #F5F5F5;
            --card-background: #FFFFFF;
            --border-color: #E0E0E0;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .professional-header {
            background-color: var(--primary-color);
            color: white;
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .professional-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(26, 115, 232, 0.9), rgba(52, 168, 83, 0.9));
            transform: skewY(-6deg);
            transform-origin: top left;
            z-index: 1;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header-content h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .header-content p {
            font-size: 1.125rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .membership-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }

        .active-membership {
            background-color: #E8F5E9;
            border-left: 4px solid var(--secondary-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .membership-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--card-background);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .membership-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }

        .membership-card .card-body {
            padding: 2rem;
            text-align: center;
        }

        .membership-card .card-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .membership-card .price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .btn-subscribe {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .btn-subscribe:hover {
            background-color: #1557B0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .header-content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header class="professional-header">
        <div class="header-content container">
            <h1>Membership Plans</h1>
            <p>Unlock membership to transform paid programs into exclusive free benefits</p>
            <p>experience premium access like never before!</p>
        </div>
    </header>

    <div class="membership-container">
        
        <div class="container">
            <?php if ($activeMembership): ?>
                <div class="active-membership">
                    <h4 class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Active Membership</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Plan:</strong> <?= htmlspecialchars($activeMembership['plan_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Expiration:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($activeMembership['end_date']))) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <?php foreach ($plans as $plan): ?>
                    <div class="col-md-4">
                        <div class="membership-card">
                            <div class="card-body d-flex flex-column">
                                <h4 class="card-title"><?= htmlspecialchars($plan['name']) ?> <i class="fa-solid fa-gift fa-beat-fade" style="color: #63E6BE;"></i></h4>
                                <div class="price mb-3">
                                    RM<?= number_format($plan['price'], 2) ?>
                                </div>
                                <p class="text-muted mb-4">
                                    Access for <?= htmlspecialchars($plan['duration']) ?> days
                                </p>
                                <?php if (!$activeMembership): ?>
                                    <button 
                                        class="btn btn-subscribe" 
                                        onclick="subscribePlan(
                                            <?= $plan['id'] ?>, 
                                            '<?= htmlspecialchars($plan['name']) ?>', 
                                            <?= $plan['price'] ?>
                                        )"
                                    >
                                        Subscribe Now !
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                      membership active
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripePublicKey = "pk_test_51QTi5fBZgipvrhfHuA561cMkaLAziDdPcsKamaCTMYSpGaUgIu8gVqVIQWaArz4MMkLiMoVaFZEnojuIGdGlEQ0y00D3Zvypta";

        function subscribePlan(planId, planName, planPrice) {
            fetch("create_checkout_session.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    plan_id: planId,
                    plan_name: planName,
                    plan_price: planPrice,
                }),
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.id) {
                    const stripe = Stripe(stripePublicKey);
                    stripe.redirectToCheckout({ sessionId: data.id });
                } else {
                    alert("Error creating Stripe Checkout session.");
                }
            });
        }
    </script>
</body>
</html>