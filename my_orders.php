<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT order_id, total_amount, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>My Orders</h1>

    <?php if (mysqli_num_rows($res) === 0): ?>
        <p>You have no orders yet. <a href="shop.php">Browse the shop.</a></p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>Order #</th>
                <th>Total</th>
                <th>Status</th>
                <th>Placed</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($o = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td>#<?php echo (int)$o['order_id']; ?></td>
                    <td>$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($o['status']); ?></td>
                    <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
