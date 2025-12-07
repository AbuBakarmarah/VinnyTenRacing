<?php
require __DIR__ . '/config.php';

$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;

// Optional: mark as 'cancelled'
if ($orderId > 0) {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND status = 'pending'"
    );
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Checkout Cancelled</h1>
    <p>Your payment was not completed. You can return to your cart and try again.</p>
    <a class="btn-primary" href="cart.php">Back to Cart</a>
</div>

<?php include __DIR__ . '/footer.php'; ?>
