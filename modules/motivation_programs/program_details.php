<?php
session_start();
require_once '../../db/db.php';
require_once '../../../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php?error=Please login first");
    exit;
}



// Check if program ID is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: program.php?error=Program not found");
    exit;
}


$programId = intval($_GET['id']);
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];


// Fetch program details
$programId = intval($_GET['id']);
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM participants WHERE program_id = p.id) as participant_count,
        (SELECT COUNT(*) FROM participants WHERE program_id = p.id AND user_id = ?) as is_participant
        FROM programs p WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $programId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: program.php?error=Program not found");
    exit;
}

$program = $result->fetch_assoc();
$stmt->close();


// If user is member, skip payment check and allow direct join
if ($userRole === 'member' && $program['is_participant'] == 0) {
    if (isset($_POST['join'])) {
        header("Location: join_program.php?id=" . $programId);
        exit;
    }
} else if ($program['is_participant'] == 0) {
    // Non-members need to pay
    $checkPayment = "SELECT status FROM payments 
                     WHERE user_id = ? AND program_id = ? 
                     AND status = 'pending'
                     ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($checkPayment);
    $stmt->bind_param("ii", $userId, $programId);
    $stmt->execute();
    $paymentResult = $stmt->get_result();
}

// Check if there's a pending payment
$checkPayment = "SELECT status FROM payments 
                 WHERE user_id = ? AND program_id = ? 
                 AND status = 'pending'
                 ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($checkPayment);
$stmt->bind_param("ii", $_SESSION['user_id'], $programId);
$stmt->execute();
$paymentResult = $stmt->get_result();
$hasPendingPayment = $paymentResult->num_rows > 0;


// Geolocation Setup
function getCoordinates($address, $apiKey) {
    $address = urlencode($address);
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK') {
        $latitude = $data['results'][0]['geometry']['location']['lat'];
        $longitude = $data['results'][0]['geometry']['location']['lng'];
        return ['lat' => $latitude, 'lng' => $longitude];
    }
    return false;
}

$apiKey = 'AIzaSyAH3mt6fAoEg57j2x59It5tVtgtFaIYW6M';
$coordinates = getCoordinates($program['location'], $apiKey);

if (!$coordinates) {
    $mapError = "Unable to fetch map for this location.";
} else {
    $latitude = $coordinates['lat'];
    $longitude = $coordinates['lng'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Details - <?= htmlspecialchars($program['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .program-details {
            margin-top: 50px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .program-header {
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        .payment-form {
            display: none;
            margin-top: 20px;
        }
        #card-element {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #card-errors {
            color: #dc3545;
            margin-top: 10px;
        }
        .loading-spinner {
            display: none;
        }
        .map-container iframe {
            width: 100%;
            height: 300px;
            border: none;
            border-radius: 10px;
        }
        .alert {
    margin-top: 15px;
    padding: 10px;
    border-radius: 4px;
}
    </style>
</head>
<body>
<div class="container">
    <div class="program-details">
        <div class="program-header">
            <h2><?= htmlspecialchars($program['title']) ?></h2>
            <p><strong>Date:</strong> <?= htmlspecialchars($program['date']) ?> at <?= htmlspecialchars($program['time']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($program['location']) ?></p>
            <p><strong>Participants:</strong> <?= $program['participant_count'] ?></p>
        </div>
        
        <div class="program-body p-4">
            <p><?= nl2br(htmlspecialchars($program['description'])) ?></p>
            
            <div class="action-buttons text-center">
                <?php if ($program['is_participant']): ?>
                    <div class="alert alert-success">
                        You are already registered for this program!
                    </div>
                <?php elseif ($userRole === 'member'): ?>
                    <form action="join_program.php" method="POST">
                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                        <button type="submit" name="join" class="btn btn-primary">
                            Join Program (Free for Members)
                        </button>
                    </form>
                <?php elseif ($hasPendingPayment): ?>
                    <div class="alert alert-warning">
                        You have a pending payment for this program. Please complete your payment.
                    </div>
                    
                <?php elseif ($program['price'] > 0): ?>
                    <button id="payButton" class="btn btn-primary btn-lg">
                        Pay RM<?= number_format($program['price'], 2) ?> to Join
                    </button>
                    <div id="payment-form" class="payment-form">
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                        <button id="submit-payment" class="btn btn-success mt-3">
                            <span class="spinner-border spinner-border-sm loading-spinner" role="status"></span>
                            Complete Payment
                        </button>
                    </div>
                    
                <?php else: ?>
                    <form action="join_program.php" method="POST">
                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                        <button type="submit" class="btn btn-success btn-lg">Join Free Program</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Map Section -->
        <?php if (isset($mapError)): ?>
            <div class="alert alert-danger mt-4"><?= $mapError ?></div>
        <?php else: ?>
            <div class="map-container mt-4">
                <iframe src="https://www.google.com/maps/embed/v1/place?key=<?= $apiKey ?>&q=<?= $latitude ?>,<?= $longitude ?>"></iframe>
            </div>
        <?php endif; ?>
    </div>

    </div>

    <div class="text-center mt-4">
    <?php if ($_SESSION['role'] === 'member'): ?>
        <a href="mprogram.php" class="btn btn-secondary">&laquo; Back to Programs</a>
    <?php else: ?>
        <a href="program.php" class="btn btn-secondary">&laquo; Back to Programs</a>
    <?php endif; ?>
</div>

<script>
const stripe = Stripe('pk_test_51QTi5fBZgipvrhfHuA561cMkaLAziDdPcsKamaCTMYSpGaUgIu8gVqVIQWaArz4MMkLiMoVaFZEnojuIGdGlEQ0y00D3Zvypta');
const elements = stripe.elements();
const card = elements.create('card');
const payButton = document.getElementById('payButton');
const paymentForm = document.getElementById('payment-form');
const submitButton = document.getElementById('submit-payment');
const spinner = document.querySelector('.loading-spinner');

// Mount the card element
card.mount('#card-element');

// Handle validation errors
card.addEventListener('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

// Show payment form when Pay button is clicked
payButton.addEventListener('click', function() {
    payButton.style.display = 'none';
    paymentForm.style.display = 'block';
});

// Handle form submission
submitButton.addEventListener('click', async function(event) {
    event.preventDefault();
    submitButton.disabled = true;
    spinner.style.display = 'inline-block';

    try {
        // Create payment intent
        const response = await fetch('process_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                program_id: <?= json_encode($program['id']) ?>,
                price: <?= json_encode($program['price']) ?>
            })
        });

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        // Confirm card payment
        const result = await stripe.confirmCardPayment(data.clientSecret, {
            payment_method: {
                card: card,
            }
        });

        if (result.error) {
            throw new Error(result.error.message);
        }

        // Payment successful
        window.location.href = 'success.php?payment_intent_id=' + result.paymentIntent.id;

    } catch (error) {
        const errorElement = document.getElementById('card-errors');
        errorElement.textContent = error.message;
        submitButton.disabled = false;
        spinner.style.display = 'none';
    }
});
</script>

</body>
</html>