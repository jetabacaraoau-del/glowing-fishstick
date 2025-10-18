<?php
include('config/db.php');
include('inc/nav.php');

$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
$categoryCondition = '';

if (!empty($categoryFilter)) {
    $safeCategory = mysqli_real_escape_string($conn, $categoryFilter);
    $categoryCondition = "WHERE c.cat_name = '$safeCategory'";
    $displayCategory = $categoryFilter;
} else {
    $displayCategory = "All Products";
}
?>

<!-- Main Content -->
<main class="col-md-10">
    <?php if (empty($categoryFilter)) : ?>
    <section class="best-sellers-section mb-4">
        <h3 class="section-title ms-4" style="font-weight: 600; font-size: 1.4rem; color: #333; letter-spacing: 0.5px;">
          Best Seller
        </h3>
        <div class="best-seller-container">
            <?php
            $topSellingQuery = "
                SELECT p.*, COALESCE(SUM(oi.quantity), 0) AS total_sold
                FROM products p
                LEFT JOIN ordersitems oi ON p.product_id = oi.productid
                LEFT JOIN orders o ON oi.orderid = o.id AND o.orderstatus = 'Delivered'
                GROUP BY p.product_id
                ORDER BY total_sold DESC
                LIMIT 10
            ";
            $topSellingResult = mysqli_query($conn, $topSellingQuery);

            if ($topSellingResult) {
                while ($row = mysqli_fetch_assoc($topSellingResult)) {
                    include 'product_card.php';
                }
            } else {
                echo "<p class='text-danger'>Error fetching top sellers.</p>";
            }
            ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (empty($categoryFilter)) : ?>
      <hr class="section-divider">
    <?php endif; ?>
    
    <section class="all-products-section">

         <h3 class="section-title ms-3" style="font-weight: 600; font-size: 1.4rem; color: #333; letter-spacing: 0.5px;">
                <?= htmlspecialchars($displayCategory) ?>
            </h3>        
            <div class="all-products-container">
            <?php
            $allProductsQuery = "
                SELECT p.*, c.cat_name
                FROM products p
                LEFT JOIN category c ON p.cat_id = c.cat_id
                $categoryCondition
                ORDER BY p.product_name ASC
            ";
            $allProductsResult = mysqli_query($conn, $allProductsQuery);

            if ($allProductsResult && mysqli_num_rows($allProductsResult) > 0) {
                while ($row = mysqli_fetch_assoc($allProductsResult)) {
                    include 'product_card.php';
                }
            } else {
                echo "<p class='text-danger'>No products found.</p>";
            }
            ?>
        </div>
    </section>
</main>

<style>

.best-seller-container, .all-products-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 18px;
    padding: 20px;
    margin-left: 80px; /* Added slight shift to the right */
    margin-right: -160px;
}


/* --- Product Item --- */
.product-item {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease-in-out;
}

.product-item:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
    background-color: #f0f8ff;
}

/* --- Image and Overlay --- */
.product-image-container {
    position: relative;
    text-align: center;
    overflow: hidden;
    border-radius: 8px;
}

.product-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.product-image-container:hover .product-image {
    transform: scale(1.05);
    filter: brightness(0.8);
}

/* View Details Overlay */
.view-details-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(255, 255, 255, 0.7); /* white with transparency */
    color: #000; /* black text for contrast */
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 6px;
    opacity: 0;
    text-decoration: none;
    transition: all 0.3s ease;
    pointer-events: none;
}


.product-image-container:hover .view-details-overlay {
    opacity: 1;
    pointer-events: auto;
}

/* --- Product Info --- */
.product-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    margin-top: 10px;
}

.product-name {
    font-size: 1.1rem;
    font-weight: bold;
    transition: color 0.3s;
}

.product-item:hover .product-name {
    color: #198754;
}

.price {
    font-size: 1.2rem;
    font-weight: bold;
    color: #444; /* light black */
}

/* --- Add to Cart Button --- */
.product-action {
    margin-top: auto;
    display: flex;
    justify-content: center;
}

.product-action .btn {
    background-color: #FFA500; /* Yellow-orange */
    color: #000;
    border: none;
    min-width: 120px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.product-action .btn:hover {
    background-color: #E6BE00;
    color: #fff;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(230, 190, 0, 0.6);
}

/* --- Divider --- */
.section-divider {
    border: none;
    border-top: 3px solid #FFA500; /* Yellow-orange */
    width: 100%; /* increase or adjust to make it longer (e.g., 90% or 100%) */
    margin: 80px auto 50px 80px; /* adjust left spacing if needed */
    opacity: 0.9; /* slightly stronger visibility */
}


.section-title {
  text-align: left;
  margin-left: 100px !important;
  margin-top: 0px;
  margin-bottom: 20px;
  font-weight: 800;
  font-size: 3.5rem;
  color: #333;
}


</style>
