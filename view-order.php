<?php
session_start();
date_default_timezone_set('Asia/Manila');
include('config/db.php');
include('inc/nav.php');

// Redirect if not logged in
if (!isset($_SESSION['customerid'])) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set('Asia/Manila');

$c_id = $_SESSION['customerid'];
$o_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order data
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND userid = ?");
$order_stmt->bind_param("ii", $o_id, $c_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$row_orders = $order_result->fetch_assoc();
$order_stmt->close();

// Check if order exists
if (!$row_orders) {
    echo '<div class="container py-5 text-center"><div class="alert alert-danger">Order not found.</div></div>';
    include('inc/footer.php');
    exit;
}
?>

<style>
.card-modern {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.card-header-modern {
    background-color: #f8f9fa; /* Off-white */
    color: #333;               /* Dark text for readability */
    font-weight: 600;
    font-size: 1.15rem;
    padding: 1rem 1.5rem;
}

.table-modern {
    border-radius: 12px;
    overflow: hidden;
    background-color: #fff;
}
.table-modern th, .table-modern td {
    vertical-align: middle;
    text-align: center;
    border: none;
    padding: 0.75rem 1rem;
}
.table-modern thead {
    background-color: #f8f9fa;
    font-weight: 600;
}
.table-modern tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.billing-section {
    padding: 1rem 1.5rem;
    margin-top: 1rem;
    border-top: 1px solid #dee2e6;
}
.billing-section h5 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.billing-section p {
    margin-bottom: 0.25rem;
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card-modern">
                <div class="card-header-modern">
                    <i class="bi bi-bag-check-fill me-2"></i> Order Details & Billing Info
                </div>
                <div class="card-body bg-light">

                    <!-- Order Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $items_stmt = $conn->prepare("
                                    SELECT oi.quantity, oi.productprice, p.product_name, p.product_id 
                                    FROM ordersitems oi 
                                    JOIN products p ON oi.productid = p.product_id 
                                    WHERE oi.orderid = ?
                                ");
                                $items_stmt->bind_param("i", $o_id);
                                $items_stmt->execute();
                                $items_result = $items_stmt->get_result();

                                if ($items_result->num_rows > 0):
                                    while ($row = $items_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <a href="single.php?id=<?= htmlspecialchars($row['product_id']) ?>" class="text-decoration-none text-dark fw-medium">
                                            <?= htmlspecialchars($row['product_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= intval($row['quantity']) ?></td>
                                    <td>₱<?= number_format($row['productprice'], 2) ?></td>
                                    <td>₱<?= number_format($row['quantity'] * $row['productprice'], 2) ?></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No items found in this order.</td>
                                </tr>
                                <?php endif; $items_stmt->close(); ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2"></th>
                                    <th>Total Price</th>
                                    <th>₱<?= number_format($row_orders['totalprice'], 2) ?></th>
                                </tr>
                                <tr>
                                    <th colspan="2"></th>
                                    <th>Order Status</th>
                                    <th><?= htmlspecialchars($row_orders['orderstatus']) ?></th>
                                </tr>
                                <tr>
                                    <th colspan="2"></th>
                                    <th>Date</th>
                                    <th><?= date('F j, Y g:i A', strtotime($row_orders['timestamp'] . ' +8 hours')) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Billing Info -->
                    <div class="billing-section">
                        <?php
                        $stmt_add = $conn->prepare("SELECT firstname, lastname, mobile FROM user_data WHERE userid = ?");
                        $stmt_add->bind_param("i", $c_id);
                        $stmt_add->execute();
                        $result_add = $stmt_add->get_result();
                        $row_add = $result_add->fetch_assoc();
                        $stmt_add->close();
                        ?>
                        <h5><i class="bi bi-geo-alt-fill me-2"></i> Billing Name</h5>
                        <?php if ($row_add): ?>
                            <p><?= htmlspecialchars($row_add['firstname'] . " " . $row_add['lastname']) ?></p>
                            <p><strong>Mobile:</strong> <?= htmlspecialchars($row_add['mobile']) ?></p>
                        <?php else: ?>
                            <em class="text-muted">No address on file.</em>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
