<?php
require __DIR__ . '/config.php';

// ---- Sorting logic ----
$allowedSorts = [
    'newest'     => 'created_at DESC',
    'price-asc'  => 'price ASC',
    'price-desc' => 'price DESC'
];

$sort = $_GET['sort'] ?? 'newest';
if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'newest';
}

$orderBy = $allowedSorts[$sort];

$sql = "SELECT product_id, product_name, description, price, stock_qty, image_url
        FROM products
        ORDER BY $orderBy";
$res = mysqli_query($conn, $sql);

$productCount = ($res) ? mysqli_num_rows($res) : 0;

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <aside class="left-sidebar">
            <div class="card shop-info-card">
                <h2>Shop Info</h2>
                <p>Browse our current performance packages and parts.</p>
                <ul class="shop-highlights">
                    <li>Carefully curated performance packages</li>
                    <li>Parts tested on real builds</li>
                    <li>Professional installation available</li>
                </ul>
            </div>
        </aside>

        <main class="right-content">
            <section class="shop-section">
                <header class="shop-header">
                    <div>
                        <h1 class="shop-title">Shop</h1>
                        <p class="shop-subtitle">
                            <?php if ($productCount > 0): ?>
                                Showing <?php echo $productCount; ?> product<?php echo $productCount > 1 ? 's' : ''; ?>.
                            <?php else: ?>
                                No products available yet – check back soon.
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($productCount > 0): ?>
                        <form class="shop-sort-form" method="get" action="">
                            <label for="sort" class="shop-sort-label">Sort by:</label>
                            <select id="sort" name="sort" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>
                                    Newest
                                </option>
                                <option value="price-asc" <?php echo $sort === 'price-asc' ? 'selected' : ''; ?>>
                                    Price: Low to High
                                </option>
                                <option value="price-desc" <?php echo $sort === 'price-desc' ? 'selected' : ''; ?>>
                                    Price: High to Low
                                </option>
                            </select>
                            <noscript>
                                <button type="submit" class="btn-secondary btn-xs">Apply</button>
                            </noscript>
                        </form>
                    <?php endif; ?>
                </header>

                <?php if ($productCount === 0): ?>
                    <div class="shop-empty-state">
                        <h2>We’re getting things ready.</h2>
                        <p>
                            Our online catalog is being updated. In the meantime, please contact us for
                            current pricing and availability on tuning packages and performance parts.
                        </p>
                        <a class="btn-primary" href="contact.php">Contact Us</a>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php while ($p = mysqli_fetch_assoc($res)): ?>
                            <article class="product-card">
                                <a href="product.php?id=<?php echo (int)$p['product_id']; ?>" class="product-card-link">
                                    <div class="product-image-wrapper">
                                        <?php if (!empty($p['image_url'])): ?>
                                            <img
                                                src="<?php echo htmlspecialchars($p['image_url']); ?>"
                                                alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                                class="product-image"
                                            >
                                        <?php else: ?>
                                            <div class="product-image-placeholder">
                                                <span class="placeholder-text">
                                                    Image coming soon
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-body">
                                        <h2 class="product-title">
                                            <?php echo htmlspecialchars($p['product_name']); ?>
                                        </h2>

                                        <p class="product-price">
                                            $<?php echo number_format((float)$p['price'], 2); ?>
                                        </p>

                                        <?php if (!empty($p['description'])): ?>
                                            <p class="product-description">
                                                <?php echo nl2br(htmlspecialchars($p['description'])); ?>
                                            </p>
                                        <?php endif; ?>

                                        <p class="product-stock
                                                <?php echo ((int)$p['stock_qty'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                                            <?php if ((int)$p['stock_qty'] > 0): ?>
                                                In stock: <?php echo (int)$p['stock_qty']; ?>
                                            <?php else: ?>
                                                Out of stock
                                            <?php endif; ?>
                                        </p>

                                        <div class="product-actions">
                                            <span class="btn-secondary">View details</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
