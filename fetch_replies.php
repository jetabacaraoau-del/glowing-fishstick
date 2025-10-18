<?php
include('config/db.php');

if (!isset($_GET['rating_id'])) {
    echo "<p>Invalid request.</p>";
    exit;
}

$rating_id = intval($_GET['rating_id']);

// Join feedback_replies with staff table to get staff full name
$query = "
    SELECT 
        r.reply, 
        r.created_at, 
        CONCAT(s.first_name, ' ', s.last_name) AS staff_name
    FROM feedback_replies r
    LEFT JOIN staff s ON r.staff_id = s.id
    WHERE r.rating_id = $rating_id
    ORDER BY r.created_at ASC
";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($reply = mysqli_fetch_assoc($result)) {
        echo "<div class='reply'>";
        echo "<strong>" . htmlspecialchars($reply['staff_name'] ?? 'Unknown') . "</strong><br>";
        echo nl2br(htmlspecialchars($reply['reply'])) . "<br>";
        echo "<small>" . date('F j, Y g:i A', strtotime($reply['created_at'] . ' +8 hours')) . "</small>";
        echo "</div><hr>";
    }
} else {
    echo "<p>No replies yet for this feedback.</p>";
}
?>
