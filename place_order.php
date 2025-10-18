<?php
session_start();
include('config/db.php');

// Check if customer is logged in
if (!isset($_SESSION['customerid']) || !isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customerid'];
$customerEmail = $_SESSION['customer']['email'];  // Adjust if your session stores email differently

// Assume your cart is stored in session as an associative array: product_id => quantity
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty. Please add products before placing an order.";
    exit;
}

$cart = $_SESSION['cart'];

// Calculate total price
$totalPrice = 0.0;
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

// Prepare statement to fetch product prices
$sql = "SELECT product_id, product_name, price FROM products WHERE product_id IN ($placeholders)";
$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($productIds));
$stmt->bind_param($types, ...$productIds);
$stmt->execute();
$result = $stmt->get_result();

$productsData = [];
while ($row = $result->fetch_assoc()) {
    $productsData[$row['product_id']] = $row;
}

$stmt->close();

// Calculate total price using fetched product prices and cart quantities
foreach ($cart as $pid => $qty) {
    if (isset($productsData[$pid])) {
        $totalPrice += $productsData[$pid]['price'] * $qty;
    } else {
        echo "Product ID $pid not found.";
        exit;
    }
}

// Insert into orders table
$orderStatus = 'Placed Order';  // initial order status
$timestamp = date('Y-m-d H:i:s');

$orderStmt = $conn->prepare("INSERT INTO orders (userid, totalprice, orderstatus, timestamp) VALUES (?, ?, ?, ?)");
$orderStmt->bind_param("idss", $customerId, $totalPrice, $orderStatus, $timestamp);

if (!$orderStmt->execute()) {
    echo "Failed to place order: " . $conn->error;
    exit;
}

$orderId = $orderStmt->insert_id;
$orderStmt->close();

// Insert order items
$itemStmt = $conn->prepare("INSERT INTO ordersitems (orderid, productid, quantity) VALUES (?, ?, ?)");

foreach ($cart as $pid => $qty) {
    $itemStmt->bind_param("iii", $orderId, $pid, $qty);
    if (!$itemStmt->execute()) {
        echo "Failed to insert order items: " . $conn->error;
        exit;
    }
}
$itemStmt->close();

// Clear cart after successful order
unset($_SESSION['cart']);

// Send confirmation email
$to = $customerEmail;
$subject = "Order Confirmation - Your Order #$orderId";
$productsList = "";
foreach ($cart as $pid => $qty) {
    $productsList .= $productsData[$pid]['product_name'] . " (x$qty)\n";
}

$message = "Dear Customer,\n\nThank you for your order. Here are your order details:\n\n";
$message .= "Order ID: $orderId\n";
$message .= "Products:\n$productsList\n";
$message .= "Total Price: ₱" . number_format($totalPrice, 2) . "\n";
$message .= "Order Status: $orderStatus\n";
$message .= "Order Date: $timestamp\n\n";
$message .= "We will notify you once your order is shipped.\n\nThank you for shopping with us.\n";

$headers = "From: no-reply@yourdomain.com\r\n";
$headers .= "Reply-To: support@yourdomain.com\r\n";

// Send the email (use mail() or a better mail library like PHPMailer in production)
if (!mail($to, $subject, $message, $headers)) {
    // Optional: Log the failure or inform the user
    // For demo, we'll just continue silently
}

// Redirect to purchases page with success confirmation
header('Location: mypurchases.php?confirmation=success');
exit;
?>
