<?php
session_start();
require_once '../../../vendor/autoload.php';
require_once '../../db/db.php';


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

// Stripe Public Key
$stripePublicKey = 'pk_test_51QTi5fBZgipvrhfHuA561cMkaLAziDdPcsKamaCTMYSpGaUgIu8gVqVIQWaArz4MMkLiMoVaFZEnojuIGdGlEQ0y00D3Zvypta';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - AutiReach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    </head>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('https://images.pexels.com/photos/8386182/pexels-photo-8386182.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'); /* Replace with your background image URL */
            background-size: cover;
            background-position: center;
            color: #333;
        }
        .donation-form {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9); /* Semi-transparent white background */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .donation-form h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #007bff; /* Bootstrap primary color */
        }
        #card-errors {
            color: red;
            font-size: 0.9rem;
        }
        .btn-primary {
            background-color: #007bff; /* Bootstrap primary color */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker shade on hover */
            border-color: #0056b3;
        }
        /* Updated CSS with Poppins font */
.donation-form {
    font-family: 'Poppins', sans-serif;
}

.donation-form h2 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.donation-description {
    font-size: 1.1em;
    line-height: 1.5;
    color: #4a4a4a;
    margin: 1rem 0;
    max-width: 600px;
    font-weight: 400;
}
    </style>
</head>
<body>
    <div class="donation-form">
        <h2>Make a Donation</h2>
        <p class="donation-description">
        Donations will be organized by AutiReach to help the community through our programs.</p>
        <form id="donation-form" method="POST" action="donate_process.php">
            <div class="mb-3">
                <label for="donor_name" class="form-label">Full Name</label>
                <input type="text" id="donor_name" name="donor_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (RM)</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="1" required>
            </div>
            <div id="card-element" class="mb-3"></div>
            <div id="card-errors" role="alert"></div>
            <button class="btn btn-primary w-100" type="submit"> <i class="fa-solid fa-circle-dollar-to-slot fa-bounce" style="color:rgb(251, 255, 0);"></i>   Donate</button>
          
        </form>
    </div>

    <script>
        const stripe = Stripe('<?= $stripePublicKey ?>');
        const elements = stripe.elements();
        const card = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Poppins", sans-serif',
                    fontSize: '16px',
                    '::placeholder': { color: '#aab7c4' },
                },
                invalid: { color: '#fa755a' },
            },
        });

        card.mount('#card-element');

        const form = document.getElementById('donation-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const { error, paymentMethod } = await stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: {
                    name: document.getElementById('donor_name').value,
                    email: document.getElementById('email').value,
                },
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message;
            } else {
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_method_id');
                hiddenInput.setAttribute('value', paymentMethod.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    </script>
</body>
</html>
