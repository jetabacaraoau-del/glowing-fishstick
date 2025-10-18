<?php
session_start();
include('config/db.php');

$user_id = $_SESSION['customerid'] ?? 0;
$message = $_POST['message'] ?? '';

if ($user_id && $message) {
    $stmt = $conn->prepare("UPDATE notifications SET `read` = 1 WHERE user_id = ? AND message = ?");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false]);
