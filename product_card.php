<!-- Product List Section -->
<ul class="product-list">
  <li class="product-item">
    <div class="product-image-container">
      <img src="admin/<?php echo $row['thumb']; ?>" alt="<?= htmlspecialchars($row['product_name']); ?>" class="product-image">
      <a href="product_details.php?product_id=<?= $row['product_id']; ?>" class="view-details-overlay">View Details</a>
    </div>

    <div class="product-info">
      <h4 class="product-name"><?= htmlspecialchars($row['product_name']); ?></h4>
      <p class="product-description"><?= !empty($row['product_description']) ? htmlspecialchars($row['product_description']) : 'No description available'; ?></p>

      <!-- ⭐ Star Rating -->
      <div class="product-rating mb-2" id="ratingStars-<?= $row['product_id']; ?>">
        <?php
        $ratingsQuery = "
          SELECT 
              COUNT(*) AS total_ratings, 
              SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS one_star,
              SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS two_stars,
              SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS three_stars,
              SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS four_stars,
              SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS five_stars
          FROM product_ratings
          WHERE product_id = {$row['product_id']}
        ";
        $ratingsResult = mysqli_query($conn, $ratingsQuery);
        $ratingsData = mysqli_fetch_assoc($ratingsResult);
        $totalRatings = $ratingsData['total_ratings'] > 0 ? $ratingsData['total_ratings'] : 1;
        $oneStarPercentage = ($ratingsData['one_star'] / $totalRatings) * 100;
        $twoStarsPercentage = ($ratingsData['two_stars'] / $totalRatings) * 100;
        $threeStarsPercentage = ($ratingsData['three_stars'] / $totalRatings) * 100;
        $fourStarsPercentage = ($ratingsData['four_stars'] / $totalRatings) * 100;
        $fiveStarsPercentage = ($ratingsData['five_stars'] / $totalRatings) * 100;
        $overallRating = ($oneStarPercentage * 1 + $twoStarsPercentage * 2 + $threeStarsPercentage * 3 + $fourStarsPercentage * 4 + $fiveStarsPercentage * 5) / 100;

        for ($i = 1; $i <= 5; $i++) {
          if ($i <= floor($overallRating)) {
            echo '<i class="fas fa-star text-warning"></i>';
          } elseif ($i - 0.5 <= $overallRating) {
            echo '<i class="fas fa-star-half-alt text-warning"></i>';
          } else {
            echo '<i class="far fa-star text-warning"></i>';
          }
        }
        ?>
      </div>

      <p class="price">₱<?= is_numeric($row['price']) ? number_format((float)$row['price'], 2) : 'Invalid Price'; ?></p>

      <div class="product-action">
        <form action="addToCart.php" method="POST" class="d-inline">
          <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="btn add-cart-btn">Add to Cart</button>
        </form>
      </div>
    </div>
  </li>
</ul>

<style>
.product-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  list-style: none;
  padding: 0;
  margin: 5px;
}

.product-item {
  background: #fff;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s;
}
.product-item:hover {
  transform: translateY(-5px);
}

.product-image-container {
  position: relative;
  overflow: hidden;
  border-radius: 6px;
}
.product-image {
  width: 100%;
  max-height: 180px;
  object-fit: cover;
  border-radius: 6px;
  transition: transform 0.3s ease;
}
.product-item:hover .product-image {
  transform: scale(1.1);
}

/* Hover overlay for "View Details" */
.view-details-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #ffffff; /* solid white background */
  color: #000; /* black text */
  font-weight: bold;
  padding: 10px 25px;
  border-radius: 30px;
  text-decoration: none;
  white-space: nowrap; /* ensures it's one line only */
  opacity: 0;
  transition: all 0.3s ease;
}
.product-image-container:hover .view-details-overlay {
  opacity: 1;
}


/* Product info */
.product-info {
  margin-top: 10px;
}
.product-name {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 5px;
}
.product-description {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 10px;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  display: -webkit-box;
  overflow: hidden;
}
.product-rating {
  margin-bottom: 10px;
}
.price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #28a745;
}

/* Add to Cart Button */
.add-cart-btn {
  background-color: #ffb300;
  color: #000;
  font-weight: 600;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  width: 100%;
  transition: all 0.3s ease;
}
.add-cart-btn:hover {
  background-color: #ff9900;
  color: #fff;
  transform: scale(1.05);
}

/* Responsive */
@media (max-width: 992px) {
  .product-list {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 768px) {
  .product-list {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 576px) {
  .product-list {
    grid-template-columns: 1fr;
  }
}
</style>

<!-- Star Interaction Script (updated) -->
<script>
$(document).ready(function () {
    $('.product-rating i').click(function () {
        var rating = $(this).data('index');
        var productId = $(this).data('product-id');
        alert("You clicked on rating " + rating + " for product ID " + productId);
        // Submit AJAX or redirect to submit_rating.php with rating data if needed
    });

    $('.product-rating i').hover(function () {
        var rating = $(this).data('index');
        var productId = $(this).data('product-id');
        $('#ratingStars-' + productId + ' i').each(function (index) {
            $(this).css('color', index < rating ? '#ffc107' : '#ccc');
        });
    }, function () {
        var productId = $(this).data('product-id');
        $('#ratingStars-' + productId + ' i').css('color', '#ccc');
    });
});
</script>