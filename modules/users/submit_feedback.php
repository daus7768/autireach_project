<?php
session_start();

// Include database connection
require_once '../../db/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$userId = $_SESSION['user_id'];

// Handle feedback form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = trim($_POST['feedback']);

    if (empty($feedback)) {
        $error = "Feedback cannot be empty.";
    } else {
        // Insert feedback into the database
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $userId, $feedback);

        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>Submit Feedback</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"> <?php echo $error; ?> </div>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"> <?php echo $success; ?> </div>
                        <?php endif; ?>

                        <form action="submit_feedback.php" method="POST">
                            <div class="mb-3">
                                <label for="feedback" class="form-label">Your Feedback</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="5" placeholder="Write your feedback here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
