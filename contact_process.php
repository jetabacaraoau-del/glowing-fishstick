<?php
include('config/db.php');
session_start();

if (!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['customerid'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        // Sanitize
        $message = mysqli_real_escape_string($conn, $message);

        // Insert into DB
        $sql = "INSERT INTO contact_messages (user_id, message, created_at) 
                VALUES ('$user_id', '$message', NOW())";

        if (mysqli_query($conn, $sql)) {
            echo '<script>alert("Your message has been sent successfully."); window.location.href="index.php";</script>';
            exit;
        } else {
            echo '<script>alert("Error sending message: ' . mysqli_error($conn) . '"); window.location.href="index.php";</script>';
            exit;
        }
    } else {
        echo '<script>alert("Message field cannot be empty."); window.location.href="index.php";</script>';
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
