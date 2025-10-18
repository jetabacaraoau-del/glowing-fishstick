<?php
session_start();
include('config/db.php');
include('inc/nav.php');

// Initialize cart
$cart = $_SESSION['cart'] ?? [];
?>

<style>
.card {
    margin-top: -45px; /* moves the card higher */
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}


.card-header {
    background: #fdfdfd;
    color: #333;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 10px; /* moves header slightly down */
}


.table {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.table th, .table td {
    vertical-align: middle !important;
    text-align: center;
    border: none;
}

.table thead {
    background: #e9f7ef;
}

.table img {
    max-width: 80px;
    border-radius: 8px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 6px;
}

.quantity-display {
    width: 60px;
    text-align: center;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin: 0 4px;
}

.cart-summary {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    font-size: 1.2rem;
    font-weight: 600;
}

.btn-orange {
    background: linear-gradient(90deg, #FFA500, #FFA500); /* orange to yellow */
    color: #333;
    border: none;
    transition: background 0.3s, transform 0.2s;
}

.btn-orange:hover {
    background: linear-gradient(90deg, #FFB733, #FFD633);
    color: #fff;
    transform: translateY(-2px);
}

.text-danger {
    transition: color 0.3s;
}

.text-danger:hover {
    color: #c82333;
}

.btn-orange-outline {
    display: inline-block;
    background: none;
    color: #FFA500;
    border: 2px solid #FFA500;
    border-radius: 8px;
    padding: 30px 40px; 
    font-size: 1rem;
    white-space: nowrap;
    transition: none; 
}

.btn-orange-outline:hover {
    background: none;
    color: #FFA500; 
    border-color: #FFA500; 
    transform: none; 
}

</style>

<div class="container my-5">
    <div class="card" style="margin-top: -45px;">
        <div class="card-body mb-3">
            <a href="shop.php" class="btn btn-orange-outline w-100 text-start" style="border: none; padding-left: 20px;">← Continue Ordering</a>
        </div>

    <div class="card">
        <div class="card-header">
            Your Orders
        </div>

        <div class="card-body">
            <?php if(empty($cart)): ?>
                <div class="alert alert-info text-center">Your cart is empty.</div>
            <?php else: ?>
            <table class="table mb-4">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $total = 0;
                foreach ($cart as $product_id => $quantity):
                    $product_id = (int)$product_id;
                    $sql = "SELECT * FROM products WHERE product_id = $product_id";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    if(!$row) continue;

                    $item_total = $row['price'] * $quantity;
                    $total += $item_total;
                ?>
                <tr>
                    <td><img src="admin/<?= $row['thumb']; ?>" alt=""></td>
                    <td><?= htmlspecialchars($row['product_name']); ?></td>
                    <td>₱<?= number_format($row['price'], 2); ?></td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center">
                            <button class="btn btn-outline-secondary quantity-btn" data-action="decrease" data-id="<?= $product_id; ?>">−</button>
                            <input type="number" min="1" value="<?= $quantity; ?>" class="quantity-display" data-id="<?= $product_id; ?>" readonly>
                            <button class="btn btn-outline-secondary quantity-btn" data-action="increase" data-id="<?= $product_id; ?>">+</button>
                        </div>
                    </td>
                    <td>₱<?= number_format($item_total, 2); ?></td>
                    <td>
                        <a href='deleteCart.php?id=<?= $product_id; ?>' class="text-danger" title="Delete" style="font-size: 1.5rem; font-weight: bold; text-decoration: none;">×</a>
                    </td>

                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div>Total: ₱<?= number_format($total, 2); ?></div>
                <a class="btn btn-orange btn-lg" href='checkout.php'>Proceed to Checkout</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function () {
        const action = this.dataset.action;
        const productId = this.dataset.id;

        fetch('update_quantity.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`.quantity-display[data-id="${productId}"]`).value = data.new_quantity;
                location.reload();
            }
        });
    });
});
</script>
