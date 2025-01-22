<?php
session_start();
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.html?error=Please login first");
    exit;
}

// HTML Page Rendering
function renderPage($message, $type, $programId) {
    $backgroundColor = $type === 'success' ? '#3b82f6' : '#f44336';
    $icon = $type === 'success' ? '✅' : '❌';
    $buttonColor = $type === 'success' ? '#2563eb' : '#d32f2f';
    $headerMessage = $type === 'success' ? 'Success!' : 'Error!';
    $redirectUrl = "program_details.php?id=" . htmlspecialchars($programId);
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$headerMessage}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
        }
        .message-container {
            max-width: 600px;
            padding: 20px;
            text-align: center;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .message-header {
            color: white;
            background-color: {$backgroundColor};
            padding: 15px;
            border-radius: 15px 15px 0 0;
        }
        .message-header h1 {
            margin: 0;
            font-size: 2em;
        }
        .message-body {
            padding: 20px;
        }
        .message-body p {
            font-size: 1.2em;
            color: #333;
        }
        .back-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1em;
            color: white;
            background-color: {$buttonColor};
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: darken({$buttonColor}, 10%);
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="message-header">
            <h1>{$icon} {$headerMessage}</h1>
        </div>
        <div class="message-body">
            <p>{$message}</p>
            
            <a href="program.php" class="back-button">Discover other programs</a>
        </div>
    </div>
</body>
</html>
HTML;
    exit;
}

// Function to render membership success page
function renderMembershipSuccess() {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Benefit - Free Program Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.6s ease-out forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .membership-icon {
            width: 80px;
            height: 80px;
            background: #f0f9ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        .membership-icon svg {
            width: 40px;
            height: 40px;
            color: #3b82f6;
        }

        .success-title {
            color: #1a1a1a;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .success-message {
            color: #4b5563;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .membership-badge {
            background: #ecfdf5;
            color: #059669;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            display: inline-block;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .back-btn {
            background: #3b82f6;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="membership-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="success-title">Welcome to the Program!</h1>
        <p class="success-message">You've successfully joined this program using your membership benefits.</p>
        <div class="membership-badge">
            ✨ Premium Member Benefit - Free Access
        </div>
        <br><a href="program.php" class="back-btn">
            Explore More Programs
        </a>
    </div>
</body>
</html>
HTML;
    exit;
}

// Main logic starts here
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    $userId = $_SESSION['user_id'];
    $programId = intval($_POST['program_id']);

    // Check if user has active membership
    $membershipQuery = "
        SELECT * 
        FROM user_memberships 
        WHERE user_id = ? AND status = 'active' AND end_date > NOW()
    ";
    $stmt = $conn->prepare($membershipQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $membership = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if the user has already joined the program
    $checkSql = "SELECT * FROM participants WHERE user_id = ? AND program_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $userId, $programId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        renderPage('You have already joined this program.', 'error', $programId);
        exit;
    }
    $checkStmt->close();

    // If user has membership, add them to participants
    if ($membership) {
        $sql = "INSERT INTO participants (user_id, program_id, joined_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $programId);
        
        if ($stmt->execute()) {
            $stmt->close();
            renderMembershipSuccess();
            exit;
        }
        $stmt->close();
    }

    // Add the user to the participants table
    $sql = "INSERT INTO participants (user_id, program_id, joined_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $programId);

    if ($stmt->execute()) {
        $stmt->close();
        renderPage('You have successfully joined the program!', 'success', $programId);
    } else {
        $stmt->close();
        renderPage('Failed to join the program. Please try again.', 'error', $programId);
    }

    $conn->close();
} else {
    header("Location: program.php");
    exit;
}
?>