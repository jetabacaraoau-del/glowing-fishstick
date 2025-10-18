<?php

include('inc/header.php');  
include('config/db.php'); 

?>

<?php include('inc/nav.php'); ?>

<!DOCTYPE html>
<html>
<title>About Us</title>
<head>
  <style>
    .table-container {
      width: 100%;
      max-width: 800px;
      margin: 250px auto;
    }

    .about-us {
      margin-top: -100px;
      padding: 20px;
      border: black;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
      border-radius: 8px;
      background-color: #FFF1D0;
    }

    .about-us h2 {
      text-align: center;
      font-size: 24px;
      margin-bottom: 10px;
    }

    .about-us p {
      font-size: 16px;
      line-height: 1.6;
    }

    #map {
      height: 400px;
      margin-top: 20px;
      border-radius: 8px;
    }
  </style>

  <!-- Leaflet CSS & JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

<div class="table-container">
  <div class="about-us">
    <h2>About Us</h2>
    <p>Welcome to our Diner! We are dedicated to...</p>
    <p>Our company was founded by Frances Luisa Gallema...</p>

    <!-- Map Container -->
    <h3 style="text-align:center;">Our Store Location</h3>
    <div id="map"></div>
  </div>
</div>

<script>
  // Correct center coordinates for Seventeas Diner
  var map = L.map('map').setView([15.730729, 120.931167], 17);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
  }).addTo(map);

  // Add a marker to Seventeas Diner location
  L.marker([15.730729, 120.931167]).addTo(map)
    .bindPopup('Our Store Location: Seventeas Diner')
    .openPopup();
</script>

</body>
</html>

<?php include('inc/footer.php'); ?>
