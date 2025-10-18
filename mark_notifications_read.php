<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('./config/db.php');

$user_id = $_SESSION['user']['id'] ?? $_SESSION['customerid'] ?? null;

if ($user_id) {
    $stmt = $conn->prepare("UPDATE notifications SET `read` = 1 WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

http_response_code(200); // Return OK without redirect
?>
