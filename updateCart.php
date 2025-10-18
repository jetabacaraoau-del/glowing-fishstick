
<?php
session_start();

// Check if product_id and action are set
if (isset($_POST['product_id'], $_POST['action'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    // Check if the product exists in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        // Get current quantity
        $current_quantity = $_SESSION['cart'][$product_id]['quantity'];

        // Increase or decrease quantity based on the action
        if ($action === 'increase') {
            $_SESSION['cart'][$product_id]['quantity'] = $current_quantity + 1;
        } elseif ($action === 'decrease' && $current_quantity > 1) {
            $_SESSION['cart'][$product_id]['quantity'] = $current_quantity - 1;
        }
    }
}

// Redirect back to the cart page
header('Location: cart.php');
exit();
