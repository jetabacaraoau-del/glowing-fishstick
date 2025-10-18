<?php
ob_start();
session_start();

include('inc/nav.php');  
include('config/db.php');

// Redirect to login if no customer session or customerid
if(!isset($_SESSION['customer']) || empty($_SESSION['customer']) || !isset($_SESSION['customerid'])){
    header('Location: login.php');
    exit();
}

$cid = (int)$_SESSION['customerid'];
$total = 0;
$message = '';

// Initialize cart
$cart = $_SESSION['cart'] ?? [];
if(count($cart) > 0){
    foreach($cart as $product_id => $quantity){
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        $sql_cart = "SELECT price FROM products WHERE product_id = $product_id";
        $result_cart = mysqli_query($conn, $sql_cart);
        if($row_cart = mysqli_fetch_assoc($result_cart)){
            $total += ($row_cart['price'] * $quantity);
        }
    }
} else {
    header('Location: cart.php');
    exit();
}

// Fetch user data
$sql = "SELECT ud.*, u.email FROM user_data ud LEFT JOIN users u ON ud.userid = u.id WHERE ud.userid = $cid LIMIT 1";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Handle form submission
if(isset($_POST['submit'])){
    if(isset($_POST['agree']) && $_POST['agree'] === 'true' 
       && !empty($_POST['payment']) && !empty($_POST['delivery_option'])){

        $firstname = mysqli_real_escape_string($conn, $_POST['first_name']);
        $lastname = mysqli_real_escape_string($conn, $_POST['last_name']);
        $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
        $payment = mysqli_real_escape_string($conn, $_POST['payment']);
        $payment_lower = strtolower($payment);
        $delivery_option = mysqli_real_escape_string($conn, $_POST['delivery_option']);

        // Update user info
        $up_sql = "UPDATE user_data SET 
            firstname='$firstname', 
            lastname='$lastname', 
            mobile='$mobile' 
            WHERE userid=$cid";
        $Updated = mysqli_query($conn, $up_sql);

        if($Updated){
            // Insert order
            $order_status = ($payment_lower === 'gcash') ? 'Pending' : 'Placed Order';
            $insertOrder = "INSERT INTO orders (userid, totalprice, orderstatus, paymentmode, delivery_option) 
                VALUES ('$cid', '$total', '$order_status', '$payment', '$delivery_option')";
            if(mysqli_query($conn, $insertOrder)){
                $orderid = mysqli_insert_id($conn);

                // Insert each cart item
                foreach($cart as $product_id => $quantity){
                    $product_id = (int)$product_id;
                    $quantity = (int)$quantity;
                    $sql_cart = "SELECT price FROM products WHERE product_id = $product_id";
                    $result_cart = mysqli_query($conn, $sql_cart);
                    if($row_cart = mysqli_fetch_assoc($result_cart)){
                        $price_product = $row_cart['price'];
                        $insertOrdersItems = "INSERT INTO ordersitems (orderid, productid, quantity, productprice) 
                                             VALUES ('$orderid', '$product_id', '$quantity', '$price_product')";
                        mysqli_query($conn, $insertOrdersItems);
                    }
                }

                // Process payment
                if($payment_lower === 'cash on delivery'){
                    // Send confirmation email
                    $email_sql = "SELECT email FROM users WHERE id = $cid LIMIT 1";
                    $email_result = mysqli_query($conn, $email_sql);
                    $user_email = '';
                    if ($email_row = mysqli_fetch_assoc($email_result)) {
                        $user_email = $email_row['email'];
                    }

                    if(!empty($user_email)){
                        $to = $user_email;
                        $subject = "Order Confirmation - Your Order #$orderid";
                        $product_list = '';
                        foreach($cart as $pid => $qty){
                            $pid = (int)$pid;
                            $qty = (int)$qty;
                            $sql_cart = "SELECT product_name FROM products WHERE product_id = $pid";
                            $res_cart = mysqli_query($conn, $sql_cart);
                            if($row_cart = mysqli_fetch_assoc($res_cart)){
                                $product_list .= $row_cart['product_name']." (Qty: $qty)\n";
                            }
                        }
                        $message_body = "Dear $firstname $lastname,\n\n"
                            . "Thank you for your order. Here are the details:\n\n"
                            . "Order ID: $orderid\n"
                            . "Payment Method: $payment\n"
                            . "Delivery Option: $delivery_option\n"
                            . "Total Price: ₱" . number_format($total, 2) . "\n"
                            . "Products:\n$product_list\n\nBest regards,\nSeventea's Diner";

                        $headers = "From: Seventea's Diner <no-reply@yourstore.com>\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        mail($to, $subject, $message_body, $headers);
                    }

                    // Clear cart only after COD confirmation
                    $_SESSION['cart'] = [];
                    header('Location: mypurchase.php?confirmation=success');
                    exit();

                } elseif($payment_lower === 'gcash'){
                    // Do NOT clear cart yet
                    header("Location: gcash_payment.php?orderid=" . $orderid);
                    exit();
                } else {
                    header('Location: mypurchase.php');
                    exit();
                }

            } else {
                $message = "Failed to place order. Please try again.";
            }
        } else {
            $message = "Failed to update user details. Please try again.";
        }
    } else {
        $message = "You must agree to the terms, select a payment method and delivery option.";
    }
}
?>

<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold" style="margin-top: -40px;">Checkout</h2>

    <?php if($message): ?>
        <div class="alert alert-warning text-center shadow-sm rounded"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row gx-5">
        <!-- Billing Details -->
        <div class="col-md-7">
            <form method="post" novalidate class="shadow-sm p-4 rounded bg-white border border-light">
                <h4 class="mb-4 fw-semibold text-secondary">Billing Details</h4>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">First Name</label>
                    <input type="text" class="form-control form-control-lg rounded-pill shadow-sm" name="first_name" value="<?php echo htmlspecialchars($row['firstname'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Last Name</label>
                    <input type="text" class="form-control form-control-lg rounded-pill shadow-sm" name="last_name" value="<?php echo htmlspecialchars($row['lastname'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Email</label>
                    <input type="email" class="form-control form-control-lg rounded-pill shadow-sm" id="email" name="email" value="<?php echo htmlspecialchars($row['email'] ?? ''); ?>" readonly>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark">Mobile Number</label>
                    <input type="text" class="form-control form-control-lg rounded-pill shadow-sm" id="mobile" name="mobile" value="<?php echo htmlspecialchars($row['mobile'] ?? ''); ?>" required>
                </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-5">
            <form method="post" novalidate class="shadow-sm p-4 rounded bg-white border border-light">
            <div class="shadow-sm p-4 rounded bg-white border border-light">
                <h4 class="mb-4 fw-semibold text-secondary">Your Order</h4>
                <ul class="list-group mb-3 shadow-sm rounded border border-light">
                    <?php foreach($cart as $product_id => $quantity):
                        $product_id = (int)$product_id;
                        $quantity = (int)$quantity;
                        $sql_cart = "SELECT product_name, price FROM products WHERE product_id = $product_id";
                        $result_cart = mysqli_query($conn, $sql_cart);
                        if($row_cart = mysqli_fetch_assoc($result_cart)):
                            $item_total = $row_cart['price'] * $quantity;
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                        <span class="fw-medium text-dark"><?= htmlspecialchars($row_cart['product_name']); ?></span>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">Qty: <?= $quantity; ?></span>
                    </li>
                    <?php endif; endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between fw-bold fs-5 border-top border-light pt-3">
                        <span>Cart Subtotal</span>
                        <strong>₱<?= number_format($total, 2); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between fw-bold fs-5 border-top border-light pt-3">
                        <span>Total</span>
                        <strong>₱<?= number_format($total, 2); ?></strong>
                    </li>
                </ul>
            </div>
            
             <div class="row">
            <!-- Delivery Option -->
            <div class="col-md-6">
                <h4 class="mb-3 fw-semibold text-secondary">Delivery Option</h4>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="delivery_option" id="delivery_pickup" value="Pick-up" required>
                    <label class="form-check-label fw-semibold" for="delivery_pickup">Pick-up</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="delivery_option" id="delivery_delivery" value="Delivery" required>
                    <label class="form-check-label fw-semibold" for="delivery_delivery">Delivery</label>
                </div>
            </div>
        
            <!-- Payment Method -->
            <div class="col-md-6">
                <h4 class="mb-3 fw-semibold text-secondary">Payment Method</h4>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment" id="payment_cash" value="Cash on Delivery" required>
                    <label class="form-check-label fw-semibold" for="payment_cash">Cash</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="payment" id="payment_gcash" value="Gcash" required>
                    <label class="form-check-label fw-semibold" for="payment_gcash">Gcash</label>
                </div>
            </div>
        </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="agree" name="agree" value="true" required>
                    <label class="form-check-label text-secondary" for="agree">
                        I agree to the <a href="terms.php" target="_blank" class="text-decoration-none">terms & conditions</a>
                    </label>
                </div>

                <button type="submit" name="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-semibold">Place Order</button>

            </form>
        </div>
    </div>
</div>

<style>
body { 
background-color: #f8f9fa; 
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
h2, h4 { color: #222; }
.btn-primary { background-color: #007bff; 
border: none; }
.btn-primary:hover { background-color: #0056b3; }
form { background: #fff; border-radius: 12px; box-shadow: 0 4px 25px rgb(0 0 0 / 0.05); }
</style>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const confirmed = confirm("Are you sure you want to place this order?");
    if (!confirmed) e.preventDefault();
});
</script>
