<?php
session_start();
include('inc/header.php');  
include('config/db.php'); 

// Fetch all products with thumbnails
$query = "SELECT p.product_id, p.product_name, p.thumb, COALESCE(SUM(oi.quantity), 0) AS total_sold
          FROM products p
          LEFT JOIN ordersitems oi ON p.product_id = oi.productid
          GROUP BY p.product_id";
$result = mysqli_query($conn, $query);

if (isset($_SESSION['login_success'])): ?>
  <script>
    alert('You are now logged in successfully!');
  </script>
<?php
  unset($_SESSION['login_success']); // ensure it only shows once
endif;

$orderNowLink = 'shop.php'; // default
if (!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    $orderNowLink = 'login.php?from_cart=1'; // customer login redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Seventeas Diner</title>
  <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
  <style>
    body {
      margin: 0;
      font-family: 'Karla', sans-serif;
      background-color: beige;
      color: #333;
    }

    .hero-section {
      position: relative;
      height: 90vh;
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.4)),
                  url('inc/hero-bg.jpg') center 0%/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #fff;
      padding: 0 20px;
    }

    .hero-content {
      max-width: 900px; 
      animation: fadeIn 1.5s ease-in-out;
    }

    .hero-content h2 {
      font-size: 4rem;
      margin-bottom: 25px;
      font-weight: 700;
    }

    .hero-content p {
      font-size: 1.75rem;
      line-height: 1.8;
      margin-bottom: 35px;
    }

/* ...existing code... */
.hero-content .btn {
  background-color: #f1b04c;
  border: none;
  padding: 12px 25px;
  width: 30%;
  font-size: 1rem;
  color: #fff;
  text-decoration: none;
  border-radius: 30px;
  font-weight: 600;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.hero-content .btn:hover {
  background-color: #f9a825; /* slightly deeper orange on hover */
  transform: scale(1.05);
}


.hero-content .btn:hover {
  background-color: #336129; /* Darker orange on hover */
  transform: scale(1.05);
}
/* ...existing code... */

    .top-products-slider {
      width: 80%;
      margin: 50px auto;
      position: relative;
    }

    .top-products-slider .product {
      text-align: center;
      padding: 20px;
    }

    .top-products-slider img {
      width: 230px;
      height: 230px;
      object-fit: cover;
      border-radius: 30px;
      margin: 0 auto;
      display: block;
    }

    .top-products-slider .product-name {
      font-size: 1.2rem;
      margin-top: 10px;
    }

 /* Arrow styling */
.slick-prev, .slick-next {
  font-size: 24px;
  color: #fff;
  background-color: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  padding: 30px;
  cursor: pointer;
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  transition: background-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
  opacity: 0.7;
}

.slick-prev:hover, .slick-next:hover {
  background-color: #107869;
  opacity: 1;
  transform: translateY(-50%) scale(1.1);
}

.slick-prev {
  left: 10px;
}

.slick-next {
  right: 10px;
}

.slick-prev i, .slick-next i {
  color: #fff;
  font-size: 18px;
}

/* Subtle shadows for the arrows */
.slick-prev, .slick-next {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.slick-prev:hover, .slick-next:hover {
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
}


    /* Contact section styles */
    .contact-section {
      background-color: #fff;
      padding: 50px 20px;
      text-align: center;
    }

    .contact-section h2 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: #107869;
    }

    .contact-section p {
      font-size: 1rem;
      margin-bottom: 30px;
    }

    .contact-form {
      max-width: 600px;
      margin: 0 auto;
      text-align: left;
    }

    .contact-form input,
    .contact-form textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    .contact-form button {
      background-color: #4D9A46;
      color: #fff;
      border: none;
      padding: 12px 25px;
      font-size: 1rem;
      border-radius: 30px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .contact-form button:hover {
      background-color: #88B06E;
    }

    .product-container {
      background-color: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin: 10px;
      padding: 20px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .product-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .product-btn {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background-color: #4D9A46;
      color: white;
      text-decoration: none;
      border-radius: 30px;
      transition: background-color 0.3s ease, transform 0.2s ease;
      font-weight: 600;
    }

   .product-btn:hover {
    background-color: #FFD700; /* Golden yellow */
    color: #fff;
    transform: scale(1.05);
    }


    /* Original styles here... (keep everything you've written) */

/* Responsive Design Enhancements */
@media (max-width: 1024px) {
  .hero-content h2 {
    font-size: 3rem;
  }

  .hero-content p {
    font-size: 1.4rem;
  }

  .top-products-slider {
    width: 90%;
  }

  .top-products-slider img {
    width: 200px;
    height: 200px;
  }
}

@media (max-width: 768px) {
  .hero-content h2 {
    font-size: 2.5rem;
  }

  .hero-content p {
    font-size: 1.2rem;
    line-height: 1.5;
  }

  .hero-content .btn {
    width: 80%;
    font-size: 1rem;
    padding: 10px 20px;
  }

  .top-products-slider {
    width: 95%;
  }

  .top-products-slider img {
    width: 180px;
    height: 180px;
  }

  .product-container {
    padding: 15px;
  }

  .contact-section h2 {
    font-size: 2rem;
  }



  .contact-form button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .hero-content h2 {
    font-size: 2rem;
  }

  .hero-content p {
    font-size: 1rem;
    line-height: 1.4;
  }

  .hero-content .btn {
    width: 100%;
    font-size: 1rem;
  }

  .top-products-slider img {
    width: 150px;
    height: 150px;
  }

  .product-name {
    font-size: 1rem;
  }

  .product-btn {
    font-size: 0.9rem;
    padding: 8px 16px;
  }

  .contact-section {
    padding: 30px 15px;
  }

  .about-us-section {
    padding: 30px 15px;
  }

  .about-us-section h2 {
    font-size: 1.6rem;
  }

  .about-us-section p {
    font-size: 0.95rem;
  }
}

  </style>
</head>
<body>

  <section class="hero-section">
    <div class="hero-content">
      <h2>Seventeas Diner</h2>
      <p>Get ready to indulge in a culinary adventure like no other! Our menu is a masterpiece of flavors that will tantalize your taste buds. From gourmet creations to irresistible delicacies, feast like royalty and embark on a journey of gastronomic delight.</p>
        <a href="<?php echo $orderNowLink; ?>" class="btn">Order Now</a>
    </div>
  </section>

<!-- Top Products Slider Section -->
<section class="top-products-slider">
  <h2 style="text-align:center;">Our Products</h2>
  <div class="slider">
    <?php
    if (!$result) {
        echo "<div class='text-danger'>Failed to connect to the database or fetch products.</div>";
    } else {
      while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="product-container">
          <div class="product">
            <a href="shop.php">
              <img src="admin/<?php echo $row['thumb']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            </a>
            <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
            <div class="product-sold">Sold: <?php echo $row['total_sold']; ?></div>
            <form action="addToCart.php" method="post" style="display:inline;">
              <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="product-btn">Add To Cart</button>
            </form>
          </div>
        </div>
      <?php }
    }
    ?>
  </div>
  <!-- Slick Slider Navigation Arrows -->
  <div class="slick-prev">
    <i class="fas fa-chevron-left"></i>
  </div>
  <div class="slick-next">
    <i class="fas fa-chevron-right"></i>
  </div>
</section>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- About Us Section -->
<section class="about-us-section" style="background-color: #fff; padding: 50px 20px;">
  <div style="max-width: 800px; margin: 0 auto;">
    <h2 style="text-align:center; font-size: 2rem; margin-bottom: 20px;">About Us</h2>
    <p style="font-size: 16px; line-height: 1.6; margin-bottom: 15px;">
      Welcome to our Diner! We are dedicated to crafting high-quality drinks and meals that bring comfort and delight to your everyday moments.
    </p>
    <p style="font-size: 16px; line-height: 1.6;">
      Our company was founded by Frances Luisa Gallema with a mission to blend passion, flavor, and a welcoming atmosphere. We believe in good food, great service, and community connection.
    </p>
    
    <h3 style="text-align:center; margin-top: 30px;">Our Store Location</h3>
    <div id="map" style="height: 400px; margin-top: 20px; border-radius: 8px;"></div>
  </div>
</section>

<section class="contact-info">
  <h3>Contact Information</h3>

  <p><i class="fas fa-map-marker-alt"></i> Monday - Saturday | 9AM–5PM</p>
  <p><i class="fas fa-phone"></i> 0994 746 2961</p>
  <p><i class="fas fa-envelope"></i>zest.munoz@gmail.com</p>

  <h4>Social Media</h4>
  <div class="social-icons">
    <a href="https://www.facebook.com/seventeasdiner" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="https://seventeasdiner.shop/" aria-label="Website"><i class="fab fa-google"></i></a>
  </div>
</section>


<!-- Leaflet Map Script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  var map = L.map('map').setView([15.730729, 120.931167], 17);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
  }).addTo(map);
  L.marker([15.730729, 120.931167]).addTo(map)
    .bindPopup('Our Store Location: Seventeas Diner')
    .openPopup();
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
  $(document).ready(function(){
    $('.slider').slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 2000,
      nextArrow: $('.slick-next'),
      prevArrow: $('.slick-prev'),
      responsive: [
        {
          breakpoint: 1024,
          settings: { slidesToShow: 2 }
        },
        {
          breakpoint: 768,
          settings: { slidesToShow: 1 }
        }
      ]
    });
  });
</script>

</body>
</html>

<?php include('inc/footer.php'); ?>