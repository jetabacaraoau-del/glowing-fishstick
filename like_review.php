<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['customerid'])) {
    echo "login_required";
    exit;
}

$user_id = $_SESSION['customerid'];
$rating_id = intval($_POST['rating_id']);

// Check if already liked
$check = $conn->prepare("SELECT id FROM feedback_likes WHERE rating_id = ? AND user_id = ?");
$check->bind_param("ii", $rating_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Unlike (remove)
    $delete = $conn->prepare("DELETE FROM feedback_likes WHERE rating_id = ? AND user_id = ?");
    $delete->bind_param("ii", $rating_id, $user_id);
    $delete->execute();
} else {
    // Like (add)
    $insert = $conn->prepare("INSERT INTO feedback_likes (rating_id, user_id) VALUES (?, ?)");
    $insert->bind_param("ii", $rating_id, $user_id);
    $insert->execute();
}

// Return updated like count
$count_query = $conn->prepare("SELECT COUNT(*) FROM feedback_likes WHERE rating_id = ?");
$count_query->bind_param("i", $rating_id);
$count_query->execute();
$count_query->bind_result($like_count);
$count_query->fetch();

echo $like_count;
?>
