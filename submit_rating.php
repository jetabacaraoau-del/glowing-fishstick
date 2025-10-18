<?php
session_start();
include 'config/db.php'; // Make sure the path is correct and db.php contains the database connection

// Check if user is logged in
if (!isset($_SESSION['customerid'])) {
    die("You must be logged in to submit a rating.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Use customerid instead of user_id
    $customer_id = $_SESSION['customerid'];
    $product_id = $_POST['product_id'];
    
    // Check if 'rating' is set in the form
    if (isset($_POST['rating'])) {
        $rating = $_POST['rating'];
    } else {
        $rating = null; // Handle case where no rating is provided
    }
    
    $review = trim($_POST['review']);

    // Debugging output
    echo "Product ID: " . htmlspecialchars($product_id) . "<br>";
    echo "Rating: " . htmlspecialchars($rating) . "<br>";
    echo "Review: " . htmlspecialchars($review) . "<br>";

    if (!empty($product_id) && !empty($rating)) {
        // Ensure $conn is available here (make sure db.php contains $conn and is being included properly)
        $stmt = $conn->prepare("INSERT INTO product_ratings (product_id, user_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $product_id, $customer_id, $rating, $review);
        
        if ($stmt->execute()) {
            header("Location: index.php?success=1"); // Redirect after success
            exit();
        } else {
            echo "Error submitting rating: " . $stmt->error;
        }
    } else {
        echo "Rating and product ID are required.";
    }
} else {
    echo "Invalid request.";
}
?>
