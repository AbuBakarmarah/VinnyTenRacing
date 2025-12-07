<?php
require __DIR__ . '/config.php';
require __DIR__ . '/stripe_config.php';

$orderId   = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : '';

if ($orderId <= 0 || empty($sessionId)) {
    http_response_code(400);
    echo "Invalid success parameters.";
    exit;
}

// Fetch Stripe session from API (optional but safer)
$session = \Stripe\Checkout\Session::retrieve($sessionId);

if ($session->payment_status === 'paid') {
    // Update order to 'paid'
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE orders SET status = 'paid'
         WHERE order_id = ? AND stripe_session_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $orderId, $sessionId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Clear cart
    unset($_SESSION['cart']);
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Payment Successful</h1>
    <p>Thank you for your purchase. Your order number is
        <strong>#<?php echo htmlspecialchars($orderId); ?></strong>.
    </p>
    <a class="btn-primary" href="my_orders.php">View My Orders</a>
</div>

<?php include __DIR__ . '/footer.php'; ?>
