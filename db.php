<?php
$mysqli = new mysqli("localhost", "u756235277_seventeasdiner", "Seventeasdiner#2025", "u756235277_shopping_cart");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

return $mysqli;
