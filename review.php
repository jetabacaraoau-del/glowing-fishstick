<?php

include('inc/header.php');  
include('config/db.php'); 

$message = ''; // Initialize the message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['customerid'])) {
        $c_id = $_SESSION['customerid'];
        $review = trim($_POST['review']);
        $review = htmlspecialchars($review);
        $rating = $_POST['rating']; 

        // Insert into feedbacks table instead of reviews
        $insertFeedback = "INSERT INTO feedbacks (uid, review, rating) VALUES (?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);

        if (mysqli_stmt_prepare($stmt, $insertFeedback)) {
            mysqli_stmt_bind_param($stmt, "isi", $c_id, $review, $rating);

            if (mysqli_stmt_execute($stmt)) {
                $message = 'Feedback Submitted';
                header('Location: review.php'); 
                exit();
            } else {
                $message = 'Error submitting feedback: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = 'Error preparing statement: ' . mysqli_error($conn);
        }
    } else {
        $message = 'Please login to add feedback.';
    }
}

// Fetch feedbacks joined with user data (adjust if table names or columns differ)
$sql_allFeedbacks = "SELECT * FROM feedbacks JOIN user_data ON user_data.userid = feedbacks.uid ORDER BY feedbacks.timestamp DESC";
$result_allFeedbacks = mysqli_query($conn, $sql_allFeedbacks);

?>

<?php include('inc/nav.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Product Feedbacks</title>
<!-- Your existing CSS here -->
</head>
<body>

<div class="container">
    <h1>Feedbacks</h1>
    <div class="row">
        <!-- Add Feedback Form -->
        <div class="add-review">
            <div class="review-form">
                <?php if (!isset($_SESSION['customerid'])) : ?>
                    <p>Please login to add feedback.</p>
                <?php else : ?>
                    <h2>Submit Feedback</h2>
                    <form id="form" method="post">
                        <div class="form-group">
                            <label for="review">Your Feedback</label>
                            <textarea name="review" id="review" rows="6" maxlength="400" placeholder="Write your feedback here..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select name="rating" id="rating" required>
                                <option value="" disabled selected>Select rating</option>
                                <option value="1">1 - Poor</option>
                                <option value="2">2 - Fair</option>
                                <option value="3">3 - Good</option>
                                <option value="4">4 - Very Good</option>
                                <option value="5">5 - Excellent</option>
                            </select>
                        </div>
                        <button type="submit" name="submit" class="btn-submit">Submit Feedback</button>
                    </form>
                    <p class="message"><?php echo $message; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Feedbacks List -->
        <div class="reviews">
            <?php while ($row = mysqli_fetch_assoc($result_allFeedbacks)) : ?>
                <div class="review-card">
                    <div class="review-item">
                        <h5><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($row['review'])); ?></p>
                        <p class="rating stars">
                            <?php
                                $stars = intval($row['rating']);
                                echo str_repeat('★', $stars);
                                echo str_repeat('☆', 5 - $stars);
                            ?>
                        </p>
                        <small><?php echo date("g:i A, M j, Y", strtotime($row['timestamp'])); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

</body>
</html>

<?php include('inc/footer.php'); ?>

<style>
    /* Reset some basic styles */
    body, h1, h2, h5, p, small, textarea, select, button {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: #f5f7fa;
        color: #333;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }

    h1 {
        text-align: right;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 30px;
        font-size: 2.5rem;
        letter-spacing: 1px;
    }

    .row {
        display: flex;
        gap: 30px;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    /* Review Form */
    .add-review {
        flex: 1 1 350px;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }
    .add-review:hover {
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .review-form h2 {
        margin-bottom: 20px;
        color: #34495e;
        font-weight: 600;
        font-size: 1.8rem;
        border-bottom: 2px solid #3498db;
        padding-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
    }

    textarea {
        resize: vertical;
        min-height: 120px;
        padding: 12px 15px;
        font-size: 1rem;
        border: 1.5px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.3s ease;
        font-family: inherit;
    }
    textarea:focus {
        border-color: #3498db;
        outline: none;
    }

    select {
        padding: 10px 12px;
        font-size: 1rem;
        border-radius: 8px;
        border: 1.5px solid #ccc;
        transition: border-color 0.3s ease;
        font-family: inherit;
        width: 150px;
    }
    select:focus {
        border-color: #3498db;
        outline: none;
    }

    label {
        margin-bottom: 6px;
        font-weight: 600;
        color: #2c3e50;
    }

    .btn-submit {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        transition: background-color 0.3s ease, transform 0.2s ease;
        align-self: flex-start;
        box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4);
    }
    .btn-submit:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(41, 128, 185, 0.5);
    }
    .btn-submit:active {
        transform: translateY(0);
        box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
    }

    .message {
        margin-top: 15px;
        font-weight: 600;
        color: #27ae60;
        min-height: 24px;
        letter-spacing: 0.3px;
    }

    /* Reviews Section */
    .reviews {
        flex: 2 1 650px;
        max-height: 80vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 12px;
        padding: 25px 30px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        scroll-behavior: smooth;
    }

    /* Custom scrollbar for WebKit browsers */
    .reviews::-webkit-scrollbar {
        width: 10px;
    }
    .reviews::-webkit-scrollbar-track {
        background: #ecf0f1;
        border-radius: 12px;
    }
    .reviews::-webkit-scrollbar-thumb {
        background: #3498db;
        border-radius: 12px;
    }

    .review-card {
        border-bottom: 1px solid #e0e0e0;
        padding: 18px 0;
        transition: background-color 0.2s ease;
    }
    .review-card:last-child {
        border-bottom: none;
    }
    .review-card:hover {
        background-color: #f0f8ff;
    }

    .review-item h5 {
        font-size: 1.25rem;
        color: #2c3e50;
        margin-bottom: 8px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .review-item p {
        font-size: 1rem;
        margin-bottom: 8px;
        color: #444;
        line-height: 1.4;
        white-space: pre-wrap;
    }

    .review-item p.rating {
        font-size: 1.1rem;
        font-weight: 700;
        color: #f39c12; /* Gold color for stars */
    }

    .review-item small {
        color: #888;
        font-style: italic;
        font-size: 0.85rem;
    }

    /* Star styling */
    .stars {
        color: #f39c12;
        font-size: 1.2rem;
        letter-spacing: 2px;
        user-select: none;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .row {
            flex-direction: column;
        }
        .add-review, .reviews {
            flex: 1 1 100%;
        }
        h1 {
            text-align: center;
            font-size: 2rem;
        }
    }
</style>