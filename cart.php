<?php
require __DIR__ . '/config.php';

$cart = $_SESSION['cart'] ?? [];

$productRows = [];
$total = 0.0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $ids = array_map('intval', $ids);
    $idList = implode(',', $ids);

    $sql = "SELECT product_id, product_name, price
            FROM products
            WHERE product_id IN ($idList)";
    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $pid = (int)$row['product_id'];
        $qty = (int)($cart[$pid] ?? 0);
        if ($qty <= 0) continue;

        $row['qty'] = $qty;
        $row['line_total'] = $qty * (float)$row['price'];
        $total += $row['line_total'];
        $productRows[] = $row;
    }
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <aside class="left-sidebar">
            <div class="card shop-info-card">
                <h2>Your Cart</h2>
                <p>Review your selections before checking out.</p>
            </div>
        </aside>

        <main class="right-content">
            <h1>Shopping Cart</h1>

            <?php if (empty($productRows)): ?>
                <div class="shop-empty-state">
                    <h2>Your cart is empty</h2>
                    <p>Browse our shop and add a package to get started.</p>
                    <a href="shop.php" class="btn-primary">Back to Shop</a>
                </div>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productRows as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td style="text-align:right;">
                                    $<?php echo number_format((float)$item['price'], 2); ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php echo (int)$item['qty']; ?>
                                </td>
                                <td style="text-align:right;">
                                    $<?php echo number_format((float)$item['line_total'], 2); ?>
                                </td>
                                <td style="text-align:right;">
                                    <a href="cart_remove.php?id=<?php echo (int)$item['product_id']; ?>">
                                        Remove
                                    </a>
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
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div style="margin-top:1rem;">
                    <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
