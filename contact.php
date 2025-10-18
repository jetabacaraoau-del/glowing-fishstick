<?php
session_start();
include('config/db.php');

// Redirect to login if not logged in
if (!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit;
}

// Initialize message
$successMsg = '';
$messageContent = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    $customerId = $_SESSION['customerid'];

    if (!empty($message)) {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        // Insert into messages table
        $stmt = $conn->prepare("INSERT INTO messages (customer_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $customerId, $safeMessage);

        if ($stmt->execute()) {
            $successMsg = "<p class='success'>✅ Message sent successfully!</p>";
            $messageContent = ''; // Clear the textarea
        } else {
            $successMsg = "<p class='error'>❌ Something went wrong. Please try again.</p>";
            $messageContent = $message;
        }

        $stmt->close();
    } else {
        $successMsg = "<p class='error'>⚠️ Message cannot be empty.</p>";
        $messageContent = $message;
    }
}

include('inc/header.php');
include('inc/nav.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #b20000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .success {
            text-align: center;
            color: green;
            margin-top: 15px;
        }
        .error {
            text-align: center;
            color: red;
            margin-top: 15px;
        }
    </style>
</head>

</html>

<?php include('inc/footer.php'); ?>