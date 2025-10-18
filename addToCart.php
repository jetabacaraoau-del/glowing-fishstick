<?php
session_start();
include('config/db.php');

// Redirect to login if no customer session or customerid
if(!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])){
header("Location: login.php?from_cart=1");
    exit();
}

$cid = (int)$_SESSION['customerid'];
$total = 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['product_id'], $_POST['quantity'])) {
        echo "Product ID and quantity are required.";
        exit;
    }

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($product_id <= 0 || $quantity <= 0) {
        echo "Invalid product ID or quantity.";
        exit;
    }

    // Check if product exists using prepared statement
    $stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Product not found.";
        exit;
    }

    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update product in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Redirect to cart page
    header("Location: cart.php");
    exit;

} else {
    echo "Invalid request method.";
    exit;
}
