<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['product_id'], $_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$action = $_POST['action'];

if (!isset($_SESSION['cart'][$product_id])) {
    echo json_encode(['success' => false, 'message' => 'Product not in cart']);
    exit;
}

switch ($action) {
    case 'increase':
        $_SESSION['cart'][$product_id]++;
        break;
    case 'decrease':
        if ($_SESSION['cart'][$product_id] > 1) {
            $_SESSION['cart'][$product_id]--;
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

echo json_encode([
    'success' => true,
    'new_quantity' => $_SESSION['cart'][$product_id]
]);
