<?php
ob_start();
session_start();
date_default_timezone_set('Asia/Manila');
include('config/db.php');

// ✅ Only logged-in customers can access
if (!isset($_SESSION['customerid']) || empty($_SESSION['customerid'])) {
    header('Location: login.php');
    exit;
}

include('inc/nav.php');
$c_id = $_SESSION['customerid'];

// Pagination setup
$limit = 4;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter setup
$conditions = ["userid = ?"];
$params = [$c_id];
$types = "i";

$current_status = $_GET['order_status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!empty($current_status)) {
    $conditions[] = "orderstatus = ?";
    $params[] = $current_status;
    $types .= "s";
}
if (!empty($start_date)) {
    $conditions[] = "DATE(timestamp) >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $conditions[] = "DATE(timestamp) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$where = implode(" AND ", $conditions);

// Count total orders for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE $where");
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_stmt->bind_result($total_orders);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_orders / $limit);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    .modern-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    padding: 1.5rem;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
    }

.modern-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

.badge-status {
    font-size: 0.8rem;
    padding: 0.4em 0.6em;
    border-radius: 8px;
    }

.card-header-modern {
    font-weight: bold;
    font-size: 1.2rem;
    color: #333;
    }

</style>

<div class="container mt-5">
    <h2 class="text-center fw-bold mb-2" style="margin-top: -48px;">My Purchases</h2>

    <?php if (isset($_GET['cancelled'])): ?>
        <div class="alert text-center fw-semibold 
            <?php echo $_GET['cancelled'] === '1' ? 'alert-success' : 
                        ($_GET['cancelled'] === 'already' ? 'alert-warning' : 'alert-danger'); ?>">
            <?php
            switch ($_GET['cancelled']) {
                case '1': echo "✅ Order cancelled successfully."; break;
                case 'already': echo "⚠️ Order was already cancelled."; break;
                case 'notfound': echo "❌ Order not found or unauthorized access."; break;
                case 'invalid': echo "❌ Invalid order ID."; break;
                default: echo "⚠️ Unknown cancellation status.";
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="modern-card">
        <h5 class="mb-3">🔍 Filter Orders</h5>
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="order_status" class="form-label">Status</label>
                <select class="form-select" name="order_status" id="order_status">
                    <option value="">All</option>
                    <?php
                    $statuses = ['Placed Order','Preparing','On The Way','Delivered','Cancelled'];
                    foreach($statuses as $status) {
                        $selected = $current_status == $status ? 'selected' : '';
                        echo "<option value='{$status}' {$selected}>{$status}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100">Apply Filter</button>
            </div>
        </form>
    </div>

    <?php
    // Fetch orders for this user only
    $stmt = $conn->prepare("SELECT id, totalprice, orderstatus, timestamp, paymentmode 
                            FROM orders 
                            WHERE $where 
                            ORDER BY timestamp DESC 
                            LIMIT ? OFFSET ?");
    $stmt->bind_param($types . "ii", ...array_merge($params, [$limit, $offset]));
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        while ($order = $result->fetch_assoc()):
            $order_id = $order['id'];
            $products = [];

            $item_query = $conn->prepare("
                SELECT p.product_name, oi.quantity 
                FROM ordersitems oi 
                JOIN products p ON oi.productid = p.product_id 
                WHERE oi.orderid = ?
            ");
            $item_query->bind_param("i", $order_id);
            $item_query->execute();
            $items = $item_query->get_result();
            while ($item = $items->fetch_assoc()) {
                $products[] = "{$item['product_name']} (x{$item['quantity']})";
            }
            $product_list = implode(", ", $products);
    ?>

    <div class="modern-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="card-header-modern">Order #<?= $order_id ?></div>
            <span class="badge bg-<?= $order['orderstatus'] === 'Delivered' ? 'success' : ($order['orderstatus'] === 'Cancelled' ? 'danger' : 'secondary') ?> badge-status">
                <?= $order['orderstatus'] ?>
            </span>
        </div>
        <ul class="list-unstyled">
            <li><strong>Products:</strong> <?= htmlspecialchars($product_list) ?></li>
            <li><strong>Total:</strong> ₱<?= number_format($order['totalprice'], 2) ?></li>
            <li><strong>Payment:</strong> <?= htmlspecialchars($order['paymentmode']) ?></li>
            <li><strong>Date:</strong> 
                <?= date('M j, Y g:i A', strtotime($order['timestamp'] . ' +8 hours')) ?>
            </li>
        </ul>
        <div class="d-flex gap-2">
            <a href="view-order.php?id=<?= $order_id ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i> View</a>
            <?php if (!in_array($order["orderstatus"], ['Cancelled', 'Completed', 'Delivered'])): ?>
                <a href="cancel-order.php?id=<?= $order_id ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this order?');">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php
        endwhile;
    else:
        echo '<div class="alert alert-warning text-center">No orders found.</div>';
    endif;
    $stmt->close();
    ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-end"> <!-- moved left -->
                <?php
                $max_links = 5; // show 5 page links max
                $start = max(1, $page - floor($max_links / 2));
                $end = min($total_pages, $start + $max_links - 1);
    
                // Adjust start if we are near the end
                $start = max(1, $end - $max_links + 1);
    
                // Previous page
                if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">«</a>
                    </li>
                <?php endif; ?>
    
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
    
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">»</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>