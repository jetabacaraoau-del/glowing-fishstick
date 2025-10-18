<?php
session_start();
include('config/db.php');

if (!isset($_SESSION['customerid'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['customerid'];

// Ensure orderid is provided
if (!isset($_GET['orderid'])) {
    header("Location: index.php");
    exit();
}

$orderid = (int)$_GET['orderid'];

// Fetch order total
$stmt_total = $conn->prepare("SELECT SUM(productprice * quantity) AS total FROM ordersitems WHERE orderid = ?");
$stmt_total->bind_param("i", $orderid);
$stmt_total->execute();
$res_total = $stmt_total->get_result();
$row_total = $res_total->fetch_assoc();
$total = $row_total['total'] ?? 0;
$stmt_total->close();

// Check if payment already submitted
$stmt_check = $conn->prepare("SELECT * FROM gcash_payment WHERE orderid = ?");
$stmt_check->bind_param("i", $orderid);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
$payment = $res_check->fetch_assoc();
$stmt_check->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
        die("Payment screenshot is required.");
    }

    $fileTmpPath = $_FILES['payment_screenshot']['tmp_name'];
    $fileName = $_FILES['payment_screenshot']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];

    if (!in_array($fileExt, $allowed)) {
        die("Invalid file type. Allowed: jpg, jpeg, png, gif");
    }

    $newFileName = md5(time() . $fileName) . '.' . $fileExt;
    $uploadDir = './uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $dest)) {
        die("Failed to upload file.");
    }

    $payment_status = 'pending';
    $payment_date = date('Y-m-d H:i:s');

    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO gcash_payment (orderid, amount_paid, payment_date, payment_status, payment_screenshot) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $orderid, $total, $payment_date, $payment_status, $newFileName);
    $stmt->execute();
    $stmt->close();

    // Update order status
    $stmt_update = $conn->prepare("UPDATE orders SET orderstatus = 'Pending' WHERE id = ?");
    $stmt_update->bind_param("i", $orderid);
    $stmt_update->execute();
    $stmt_update->close();

    // Clear cart only after payment submission
    unset($_SESSION['cart']);

    header("Location: gcash_payment.php?orderid=$orderid&success=1");
    exit();
}

// Fetch logged-in user info
$stmt2 = $conn->prepare("SELECT u.first_name, u.last_name, u.email, ud.mobile AS mobile_number FROM users u JOIN user_data ud ON ud.userid = u.id WHERE u.id = ?");
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$res2 = $stmt2->get_result();
$user = $res2->fetch_assoc();
$stmt2->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>GCash Payment</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f2f5; }
        .box { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); display: inline-block; max-width: 400px; }
        img { width: 200px; margin: 20px 0; }
        a.btn, button.btn { display: inline-block; background: #00c853; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin-top: 20px; border: none; cursor: pointer; }
        a.btn:hover, button.btn:hover { background: #009624; }
        .payment-info { margin-top: 20px; text-align: left; border-top: 1px solid #ddd; padding-top: 15px; }
        .payment-info p { margin: 6px 0; font-size: 14px; }
        .payment-status-paid { color: green; font-weight: bold; }
        .payment-status-pending { color: orange; font-weight: bold; }
        .payment-status-failed { color: red; font-weight: bold; }
        .screenshot-img { max-width: 100%; border: 1px solid #ddd; padding: 5px; margin-top: 10px; border-radius: 6px; }
        form { margin-top: 30px; text-align: left; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input[type="text"], input[type="file"] { width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
        input[readonly] { background: #f5f5f5; color: #888; }
    </style>
</head>
<body>
    <div class="box">
        <h2>GCash Payment</h2>
        <p>Order ID: <strong>#<?php echo $orderid; ?></strong></p>
        <p><strong>Total Order Amount:</strong> ₱<?php echo number_format($total, 2); ?></p>
        <p>Scan the QR code below to pay:</p>
        <img src="inc/gcash.jpg" alt="GCash QR Code">

        <?php if ($payment): ?>
            <div class="payment-info">
                <h4>Payment Details</h4>
                <p><strong>Amount Paid:</strong> ₱<?php echo number_format($payment['amount_paid'], 2); ?></p>
                <p><strong>Payment Date:</strong> <?php echo date('M d, Y g:i A', strtotime($payment['payment_date'])); ?></p>
                <p><strong>Status:</strong> 
                    <?php 
                    $status = strtolower($payment['payment_status']);
                    if ($status === 'paid') echo '<span class="payment-status-paid">Paid</span>';
                    elseif ($status === 'pending') echo '<span class="payment-status-pending">Pending</span>';
                    elseif ($status === 'failed') echo '<span class="payment-status-failed">Failed</span>';
                    else echo htmlspecialchars($payment['payment_status']);
                    ?>
                </p>
                <?php if (!empty($payment['payment_screenshot'])): ?>
                    <p><strong>Payment Screenshot:</strong></p>
                    <img src="uploads/<?php echo htmlspecialchars($payment['payment_screenshot']); ?>" alt="Payment Screenshot" class="screenshot-img">
                <?php endif; ?>
                <?php if ($user): ?>
                    <h4>User Details</h4>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user['mobile_number']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$payment && !isset($_GET['success'])): ?>
            <h3>Submit Payment Screenshot</h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="amount_paid">Amount Paid (₱):</label>
                <input type="text" id="amount_paid" name="amount_paid" value="<?php echo number_format($total, 2, '.', ''); ?>" readonly />

                <label for="payment_screenshot">Upload Payment Screenshot:</label>
                <input type="file" id="payment_screenshot" name="payment_screenshot" accept=".jpg,.jpeg,.png,.gif" required />

                <button type="submit" class="btn">Submit Payment</button>
            </form>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Payment details submitted successfully! Awaiting confirmation.</p>
            <a href="mypurchase.php" class="btn">Back to My Purchase</a>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
  const form = document.querySelector('form');
  if(form){
    form.addEventListener('submit', function(e){
      const fileInput = document.getElementById('payment_screenshot');
      if(!fileInput.files.length){
        alert('Please upload a payment screenshot before submitting.');
        e.preventDefault();
      }
    });
  }
</script>
