<?php
require __DIR__ . '/config.php';

// Require login if you want only logged-in users to pay
require_login();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

$ids = array_keys($cart);
$ids = array_map('intval', $ids);
$idList = implode(',', $ids);

$sql = "SELECT product_id, product_name, price
        FROM products
        WHERE product_id IN ($idList)";
$res = mysqli_query($conn, $sql);

$items = [];
$total = 0.0;

while ($row = mysqli_fetch_assoc($res)) {
    $pid = (int)$row['product_id'];
    $qty = (int)($cart[$pid] ?? 0);
    if ($qty <= 0) {
        continue;
    }

    $price     = (float)$row['price'];
    $lineTotal = $price * $qty;
    $total    += $lineTotal;

    $items[] = [
        'product_id' => $pid,
        'name'       => $row['product_name'],
        'price'      => $price,
        'qty'        => $qty,
        'line_total' => $lineTotal,
    ];
}

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

include __DIR__ . '/header.php';
?>
<div class="main-content">
    <h1>Checkout</h1>

    <table class="cart-table">
        <thead>
        <tr>
            <th>Product</th>
            <th style="text-align:right;">Price</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?php echo htmlspecialchars($it['name']); ?></td>
                <td style="text-align:right;">
                    $<?php echo number_format($it['price'], 2); ?>
                </td>
                <td style="text-align:center;"><?php echo $it['qty']; ?></td>
                <td style="text-align:right;">
                    $<?php echo number_format($it['line_total'], 2); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" style="text-align:right;">Subtotal:</th>
            <th style="text-align:right;">
                $<?php echo number_format($total, 2); ?>
            </th>
        </tr>
        </tfoot>
    </table>

    <form method="post" action="create_checkout_session.php">
        <?php csrf_field(); ?>
        <button type="submit" class="btn-primary">
            Pay Securely with Card
        </button>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
