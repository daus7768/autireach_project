<?php
require_once '../../db/db.php'; // Ensure the database connection file path is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate form inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: ../../pages/register.html?error=All fields are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../pages/register.html?error=Invalid email format");
        exit;
    }

    if ($password !== $confirm_password) {
        header("Location: ../../pages/register.html?error=Passwords do not match");
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: ../../pages/register.html?error=Password must be at least 6 characters long");
        exit;
    }

    // Check if the username or email already exists
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: ../../pages/register.html?error=Database error: " . $conn->error);
        exit;
    }
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../../pages/register.html?error=Username or email already exists");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $role = 'community'; // Default role for new users
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: ../../pages/register.html?error=Database error: " . $conn->error);
        exit;
    }
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: ../../pages/login.html?success=Account created successfully! Please login.");
        exit;
    } else {
        header("Location: ../../pages/register.html?error=Something went wrong, please try again");
        exit;
    }
} else {
    // If accessed without POST request
    header("Location: ../../pages/register.html?error=Invalid request");
    exit;
}

$conn->close();
?>
