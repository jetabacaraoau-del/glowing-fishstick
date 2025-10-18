<?php
session_start();
include('config/db.php');

// Check if customer is logged in
if (!isset($_SESSION['customer']) || !isset($_SESSION['customerid'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customerid'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id > 0) {
    // Check if the order belongs to this customer and is not already cancelled
    $stmt = $conn->prepare("SELECT orderstatus FROM orders WHERE id = ? AND userid = ?");
    $stmt->bind_param("ii", $order_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();

        if ($order['orderstatus'] !== 'Cancelled') {
            // Update order status to Cancelled
            $update = $conn->prepare("UPDATE orders SET orderstatus = 'Cancelled' WHERE id = ? AND userid = ?");
            $update->bind_param("ii", $order_id, $customer_id);
            $update->execute();
            $update->close();

            // Redirect to mypurchase.php with success
            header("Location: mypurchase.php?cancelled=1");
            exit;
        } else {
            // Already cancelled
            header("Location: mypurchase.php?cancelled=already");
            exit;
        }
    } else {
        // Order not found
        header("Location: mypurchase.php?cancelled=notfound");
        exit;
    }

    $stmt->close();
} else {
    // Invalid order ID
    header("Location: mypurchase.php?cancelled=invalid");
    exit;
}
?>
