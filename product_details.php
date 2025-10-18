<?php
ob_start();
session_start();
include('config/db.php');

// Redirect to login if no valid customer session
if (!isset($_SESSION['customer']) || empty($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}
if (!isset($_SESSION['customerid']) || empty($_SESSION['customerid'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}

include('inc/nav.php');

$c_id = $_SESSION['customerid'];
$user_name = $_SESSION['customer'];

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $query = "SELECT * FROM products WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
    if (!$product) {
        echo "Product not found.";
        exit;
    }
} else {
    echo "No product ID provided.";
    exit;
}

// Check if user has ordered this product before allowing review
$can_review = false;
$order_check_query = "
    SELECT oi.productid
    FROM ordersitems oi
    JOIN orders o ON oi.orderid = o.id
    WHERE o.userid = $c_id AND oi.productid = $product_id
    LIMIT 1
";
$order_check_result = mysqli_query($conn, $order_check_query);
if ($order_check_result && mysqli_num_rows($order_check_result) > 0) {
    $can_review = true;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$can_review) {
        echo "<p style='color:red; font-weight:bold;'>You can only review this product after you have ordered it.</p>";
        exit;
    }

    $user_id = $_SESSION['customerid'];
    $rating = intval($_POST['rating']);
    $review_text = mysqli_real_escape_string($conn, trim($_POST['review']));
    $product_id_post = intval($_POST['product_id']);

    if ($user_id && $rating >= 1 && $rating <= 5 && $review_text && $product_id_post === $product_id) {
        $insert_query = "INSERT INTO product_ratings (product_id, user_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iiis", $product_id_post, $user_id, $rating, $review_text);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        echo "<p style='color:red; font-weight:bold;'>Please fill all fields correctly.</p>";
    }
}

// Fetch product reviews
$reviews = [];
$review_query = "
    SELECT 
        pr.*, 
        u.email AS user_name,
        -- count feedback likes per review
        (SELECT COUNT(*) FROM feedback_likes fl WHERE fl.rating_id = pr.id) AS feedback_likes,
        -- count feedback replies per review
        (SELECT COUNT(*) FROM feedback_replies fr WHERE fr.rating_id = pr.id) AS feedback_replies
    FROM product_ratings pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.product_id = $product_id
    ORDER BY pr.created_at DESC
";
$review_result = mysqli_query($conn, $review_query);
if ($review_result && mysqli_num_rows($review_result) > 0) {
    while ($row = mysqli_fetch_assoc($review_result)) {
        $reviews[] = $row;
    }
}

// Recommended products
$recommendations = [];
$recommend_query = "
    SELECT product_id, product_name, price, thumb 
    FROM products 
    WHERE product_id != $product_id 
    ORDER BY RAND() 
    LIMIT 10
";

$recommend_result = mysqli_query($conn, $recommend_query);
if ($recommend_result && mysqli_num_rows($recommend_result) > 0) {
    while ($rec = mysqli_fetch_assoc($recommend_result)) {
        $recommendations[] = $rec;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($product['product_name']); ?> - Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background-color: #fff;
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            padding-top: 0px; /* fixes cut-off product image */
        }
        
        /* --- Removed container box --- */
        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            box-sizing: border-box;
        }
        
        /* --- Product Image --- */
        .image-section {
          flex: 1;
          display: flex;
          justify-content: center;
          align-items: center;
          height: 400px; /* fixed container height for alignment */
          margin-left: 60px;
        }
        
        .image-section img {
          width: 100%;
          max-width: 400px; /* consistent max size */
          height: 100%;
          object-fit: contain; /* keeps full image visible */
          border-radius: 8px;
          border: 1px solid #ddd;
          background-color: #fff; /* keeps layout clean for transparent images */
          padding: 5px; /* optional: small spacing around image */
        }
        
        
        /* --- Product Details --- */
        .details-section {
            flex: 2;
            display: flex;
            flex-direction: column;
        }
        .details-section h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .product-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 20px;
        }
        .rating-stars {
            color: #ffc107;
        }
        .price {
            color: #222; /* light black */
            font-size: 24px;
            font-weight: bold;
        }
        
        /* --- Description Box --- */
        .description {
            flex: 1 1 300px;
            font-size: 18px;
            line-height: 1.6;
            background: #f7f7f7;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            align-self: flex-start;
            margin-right: auto;
            margin-left: -10px;
        }
        
        /* --- Add to Cart Button --- */
        .add-to-cart-btn {
            padding: 12px 25px;
            font-size: 16px;
            background-color: #FFA500; /* Yellow-orange */
            border: none;
            color: #000; /* black text for contrast */
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            width: fit-content;
        }
        .add-to-cart-btn:hover {
            background-color: #E69500;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.5);
        }
        
        /* --- Quantity Input --- */
        .quantity-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        #quantity {
            width: 60px;
            padding: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        label[for="quantity"] {
            font-weight: 600;
        }
        .reviews {
          width: 150%; /* makes the section wider than the container */
          background: #fff;
          border-top: 3px solid #388645; /* thicker green line */
          padding: 40px 60px;
          box-sizing: border-box;
          margin: 60px auto 0 -460px; /* aligns with recommendations section */
          overflow-x: hidden;
        }
        
        .reviews h2 {
          margin-bottom: 20px;
          color: #FFA500;
          font-weight: 700;
          font-size: 26px;
          text-align: left;
        }
        
        /* Individual Reviews */
        .review {
          border-top: 1px solid #ddd;
          padding: 15px 0;
        }
        
        .review:first-child {
          border-top: none;
        }
        
        .review strong {
          font-size: 16px;
          color: #333;
        }
        
        .review .stars {
          color: #ffc107;
          margin-left: 10px;
        }
        
        .review .review-text {
          margin-top: 5px;
        }
        
        .review-date {
          font-size: 12px;
          color: #888;
        }
        
        /* --- Submit Review Section --- */
        .submit-review {
          width: 100%;
          max-width: 1200px;        
          background: #fff;
          padding: 40px 60px;
          box-sizing: border-box;
          margin: 30px auto 0 -460px;  /* same left shift as .reviews */
          overflow-x: hidden;
        }
        
        .submit-review h3 {
          font-size: 22px;
          margin-bottom: 15px;
          color: #333;
        }
        
        .form-group {
          margin-bottom: 15px;
        }
        
        .form-group textarea {
          width: 100%;
          padding: 10px;
          font-size: 16px;
        }
        
        
        .recommendations {
            width: 150%;
            background: #fdfdfd;
            border-top: 2px solid rgb(56, 134, 73);
            padding: 40px 60px;
            box-sizing: border-box;
            margin-top: 220px; /* spacing to move it down */
            margin-left: -460px; /* move slightly to the left */
            overflow-x: hidden; /* prevents horizontal scroll */
        }
        
        .recommendations h2 {
            color: #FFA500;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .recommendations > div {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }
        
        /* Recommendations card */
        .recommendations > div > div {
            position: relative;
            flex: 1 1 calc(20% - 25px);
            min-width: 220px;
            max-width: 280px;
            background: #ffffff;  /* card stays white */
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* animate lift */
            overflow: hidden;
        }
        
        /* "View Details" overlay text */
        .recommendations > div > div::after {
            content: "View Details";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* center text */
            padding: 6px 12px;
            font-weight: bold;
            font-size: 18px;
            color: #000; /* text color */
            background-color: rgba(255, 255, 255, 0.7); /* semi-transparent white */
            border-radius: 6px;
            opacity: 0; /* hidden by default */
            pointer-events: none; /* card still clickable */
            transition: opacity 0.3s ease;
            white-space: nowrap; /* keep text on one line */
        }
        
        /* Show overlay text only on hover */
        .recommendations > div > div:hover::after {
            opacity: 1;
        }
        
        /* Lift the card on hover */
        .recommendations > div > div:hover {
            transform: translateY(-6px); /* moves the entire card up */
            box-shadow: 0 6px 12px rgba(0,0,0,0.1); /* subtle shadow */
        }
        
        
        /* Optional: subtle shadow on hover */
        .recommendations > div > div:hover {
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .recommendations img {
            max-width: 100%;
            height: 180px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        
        .recommendations h3 {
            margin: 10px 0 6px;
            font-size: 18px;
            color: #333;
        }
        
        .recommendations div div div {
            color: #555;
            font-weight: bold;
            font-size: 16px;
        }
        
        .recommendations a {
            text-decoration: none; /* removes underline */
            color: inherit;         /* keeps the text color same as normal */
        }
        
        .recommendations a:hover {
            color: #fff; /* optional: change color on hover */
        }
        
        /* --- Modal Styles --- */
        .modal {
          display: none; 
          position: fixed; 
          z-index: 9999; 
          left: 0; 
          top: 0; 
          width: 100%; 
          height: 100%; 
          overflow: auto; 
          background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
          background-color: #fff;
          margin: 10% auto;
          padding: 20px 30px;
          border-radius: 8px;
          width: 90%;
          max-width: 600px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .close-btn {
          float: right;
          font-size: 24px;
          font-weight: bold;
          cursor: pointer;
          color: #555;
        }
        
        .close-btn:hover {
          color: #000;
        }
        
        #repliesContainer {
          margin-top: 15px;
          max-height: 400px;
          overflow-y: auto;
          border-top: 1px solid #ddd;
          padding-top: 10px;
        }
        
        .reply {
          border-bottom: 1px solid #eee;
          padding: 10px 0;
        }
        
        .reply strong {
          color: #333;
        }
        
        .reply small {
          display: block;
          color: #888;
          font-size: 12px;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['cart_success'])): ?>
   <div style="background-color: #2E8B57; color: #2E8B57; padding: 10px 20px; margin: 20px; border: 1px solid #c3e6cb; border-radius: 5px;">
        <?= $_SESSION['cart_success']; ?>
    </div>
    <?php unset($_SESSION['cart_success']); ?>
<?php endif; ?>

<div class="container">
    <div class="image-section">
        <img src="admin/<?= htmlspecialchars($product['thumb']); ?>" alt="<?= htmlspecialchars($product['product_name']); ?>" />
    </div>

    <div class="details-section">
        <div style="display: flex; align-items: flex-start; gap: 30px;">
            <div>
                <h1><?= htmlspecialchars($product['product_name']); ?></h1>
                <div class="product-meta">
                    <div class="price">₱<?= number_format((float)$product['price'], 2); ?></div>
                    <div class="rating-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <i class="far fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="description">
                <?= nl2br(htmlspecialchars($product['product_description'] ?: 'No description available.')); ?>
            </div>
        </div>

        <form action="addToCart.php" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>" />
            <div class="quantity-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required />
            </div>
            
            <div id="total-price" style="margin-bottom: 10px; font-weight:bold; font-size:16px;">
                Total: ₱<?= number_format((float)$product['price'], 2); ?>
            </div>
            <button type="submit" class="add-to-cart-btn">
                <i class="fas fa-cart-plus"></i> Add to Cart
            </button>
        </form>

<!-- Recommendations -->
<div class="recommendations">
    <h2>You Might Also Like</h2>
    <div>
        <?php foreach ($recommendations as $rec): ?>
            <div>
                <a href="product_details.php?product_id=<?= $rec['product_id']; ?>">
                    <img src="admin/<?= htmlspecialchars($rec['thumb']); ?>" alt="<?= htmlspecialchars($rec['product_name']); ?>" />
                    <h3><?= htmlspecialchars($rec['product_name']); ?></h3>
                    <div>₱<?= number_format((float)$rec['price'], 2); ?></div>
                </a>
                <!-- Add to Cart Button -->
                <form action="addToCart.php" method="POST" style="margin-top:10px;">
                    <input type="hidden" name="product_id" value="<?= $rec['product_id']; ?>" />
                    <input type="hidden" name="quantity" value="1" />
                    <button type="submit" class="add-to-cart-btn">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

        <!-- Reviews -->
        <div class="reviews">
            <h2>Customer Reviews</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <strong><?= htmlspecialchars($review['user_name']); ?></strong>
                        <span class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </span>
                        <div class="review-text"><?= nl2br(htmlspecialchars($review['review'])); ?></div>
                        <div class="review-date">
                            <?= date('F j, Y', strtotime($review['created_at'])); ?> |
                            <a href="#" class="like-review" data-review-id="<?= $review['id']; ?>">
                              👍 <span id="like-count-<?= $review['id']; ?>"><?= (int)$review['feedback_likes']; ?></span> Likes
                            </a> |
                            <a href="#" class="view-replies" data-review-id="<?= $review['id']; ?>">
                              💬 <?= (int)$review['feedback_replies']; ?> Replies
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet for this product.</p>
            <?php endif; ?>
        </div>
        
        <!-- Replies Modal -->
        <div id="repliesModal" class="modal">
          <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3>Review Replies</h3>
            <div id="repliesContainer">Loading replies...</div>
          </div>
        </div>

        <!-- Submit Review -->
        <div class="submit-review">
            <h3>Submit Your Review</h3>
            <?php if ($can_review): ?>
                <form action="" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>" />
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <select id="rating" name="rating" required>
                            <option value="">Select rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="review">Review:</label>
                        <textarea id="review" name="review" rows="4" required maxlength="500"></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="add-to-cart-btn">Submit Review</button>
                </form>
            <?php else: ?>
                <p style="color:#e64a19; font-weight:bold;">You can only review this product after you have ordered it.</p>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>
<script>
const quantityInput = document.getElementById('quantity');
const totalPriceDiv = document.getElementById('total-price');
const pricePerUnit = <?= (float)$product['price']; ?>;

function updateTotal() {
    const qty = parseInt(quantityInput.value) || 1;
    const total = qty * pricePerUnit;
    totalPriceDiv.textContent = 'Total: ₱' + total.toFixed(2);
}

quantityInput.addEventListener('input', updateTotal);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('repliesModal');
  const closeBtn = document.querySelector('.close-btn');
  const repliesContainer = document.getElementById('repliesContainer');

  document.querySelectorAll('.view-replies').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const reviewId = this.getAttribute('data-review-id');

      // Show modal
      modal.style.display = 'block';
      repliesContainer.innerHTML = 'Loading replies...';

      // Fetch replies dynamically
      fetch(`fetch_replies.php?rating_id=${reviewId}`)
        .then(res => res.text())
        .then(data => {
          repliesContainer.innerHTML = data.trim() || '<p>No replies yet.</p>';
        })
        .catch(() => repliesContainer.innerHTML = '<p>Error loading replies.</p>');
    });
  });

  closeBtn.addEventListener('click', () => modal.style.display = 'none');
  window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
  });
});
</script>

<script>
    document.querySelectorAll('.like-review').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const reviewId = this.getAttribute('data-review-id');

    fetch('like_review.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `rating_id=${reviewId}`
    })
    .then(res => res.text())
    .then(data => {
      if (data === "login_required") {
        alert("Please log in to like reviews.");
        return;
      }
      document.getElementById(`like-count-${reviewId}`).textContent = data;
    })
    .catch(() => alert("Error liking review."));
  });
});

</script>
