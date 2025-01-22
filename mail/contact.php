
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//check if the request is valid
if(empty($_POST['name']) || empty($_POST['subject']) || empty($_POST['message']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  http_response_code(500);
  exit();
}

//database connection

require_once '../db/db.php'; 

// Get form data

$name = strip_tags(htmlspecialchars($_POST['name']));
$email = strip_tags(htmlspecialchars($_POST['email']));
$m_subject = strip_tags(htmlspecialchars($_POST['subject']));
$message = strip_tags(htmlspecialchars($_POST['message']));

//insert data into database

try {
  $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $email, $m_subject, $message);

  if (!$stmt->execute()) {
      throw new Exception("Database insertion failed");
  }

  $stmt->close();
} catch (Exception $e) {
  error_log("Error: " . $e->getMessage());
  http_response_code(500);
  exit();
}


// Email settings

$to = "mfirdaus.rosli@s.unikl.edu.my."; // Change this email to your //
$subject = "$m_subject:  $name";
$body = "You have received a new message from your website contact form.\n\n"."Here are the details:\n\nName: $name\n\n\nEmail: $email\n\nSubject: $m_subject\n\nMessage: $message";
$header = "From: $email\r\n";
$header .= "Reply-To: $email\r\n";	


// Send email
if(!mail($to, $subject, $body, $header)){
  http_response_code(500);
  exit();
}
http_response_code(200);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #4facfe, #00f2fe);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .contact-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            width: 100%;
        }

        .contact-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #4facfe;
        }

        .contact-container label {
            font-size: 14px;
            color: #555;
        }

        .contact-container input, 
        .contact-container textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .contact-container textarea {
            resize: vertical;
            height: 100px;
        }

        .contact-container button {
            background: #4facfe;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease;
        }

        .contact-container button:hover {
            background: #00a8f3;
        }

        .contact-container .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        @media (max-width: 600px) {
            .contact-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h1>Contact Us</h1>
        <form id="contactForm" action="path/to/your/php/script.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Your name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Your email" required>

            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" placeholder="Subject" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" placeholder="Your message" required></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>

