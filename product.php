<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    echo "Product not found.";
    exit;
}

// Fetch this product
$stmt = mysqli_prepare(
    $conn,
    "SELECT product_id, product_name, description, price, stock_qty, image_url, created_at
     FROM products
     WHERE product_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    http_response_code(404);
    echo "Product not found.";
    exit;
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch a few "related" products (simple: just other latest products)
$related = mysqli_query(
    $conn,
    "SELECT product_id, product_name, price, image_url
     FROM products
     WHERE product_id <> " . (int)$product['product_id'] . "
     ORDER BY created_at DESC
     LIMIT 3"
);

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <aside class="left-sidebar">
            <div class="card shop-info-card">
                <h2>Package Overview</h2>
                <p>High-performance tuning solution from Vinny Ten Racing.</p>
                <p style="font-size:0.9rem;color:#6b7280;">
                    For availability, dyno tuning, or installation scheduling,
                    please contact our team directly.
                </p>
                <a href="contact.php" class="btn-primary" style="margin-top:0.5rem;">
                    Contact about this package
                </a>
            </div>
        </aside>

        <main class="right-content">
            <section class="product-detail">
                <div class="product-detail-main">
                    <div class="product-detail-image-wrapper">
                        <?php if (!empty($product['image_url'])): ?>
                            <img
                                src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                class="product-detail-image"
                            >
                        <?php else: ?>
                            <div class="product-image-placeholder">
                                <span class="placeholder-text">Image coming soon</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-detail-body">
                        <h1 class="product-detail-title">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </h1>

                        <p class="product-detail-price">
                            $<?php echo number_format((float)$product['price'], 2); ?>
                        </p>

                        <p class="product-detail-stock
                               <?php echo ((int)$product['stock_qty'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php if ((int)$product['stock_qty'] > 0): ?>
                                In stock: <?php echo (int)$product['stock_qty']; ?>
                            <?php else: ?>
                                Currently out of stock
                            <?php endif; ?>
                        </p>

                        <?php if (!empty($product['description'])): ?>
                            <div class="product-detail-description">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </div>
                        <?php endif; ?>

                        <div class="product-detail-actions">
                            <form method="post" action="cart_add.php" class="add-to-cart-form">
                                <input type="hidden" name="product_id"
                                       value="<?php echo (int)$product['product_id']; ?>">
                                <label>
                                    Qty:
                                    <input type="number" name="qty" value="1" min="1" max="10">
                                </label>
                                <button type="submit" class="btn-primary">
                                    Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($related && mysqli_num_rows($related) > 0): ?>
                    <section class="related-products">
                        <h2>Related Packages</h2>
                        <div class="product-grid">
                            <?php while ($r = mysqli_fetch_assoc($related)): ?>
                                <article class="product-card product-card-compact">
                                    <a href="product.php?id=<?php echo (int)$r['product_id']; ?>"
                                       class="product-card-link">
                                        <div class="product-image-wrapper">
                                            <?php if (!empty($r['image_url'])): ?>
                                                <img
                                                    src="<?php echo htmlspecialchars($r['image_url']); ?>"
                                                    alt="<?php echo htmlspecialchars($r['product_name']); ?>"
                                                    class="product-image"
                                                >
                                            <?php else: ?>
                                                <div class="product-image-placeholder">
                                                    <span class="placeholder-text">Image coming soon</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-body">
                                            <h3 class="product-title">
                                                <?php echo htmlspecialchars($r['product_name']); ?>
                                            </h3>
                                            <p class="product-price">
                                                $<?php echo number_format((float)$r['price'], 2); ?>
                                            </p>
                                        </div>
                                    </a>
                                </article>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </section>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
