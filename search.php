<?php
require __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');
$products = [];
$services = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    $stmt = mysqli_prepare(
        $conn,
        "SELECT product_id, product_name, description, price
         FROM products
         WHERE product_name LIKE ? OR description LIKE ?
         ORDER BY product_name
         LIMIT 50"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $like, $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, description, price, duration
         FROM services
         WHERE name LIKE ? OR description LIKE ?
         ORDER BY name
         LIMIT 50"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $like, $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = $row;
        }
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
    }
}

include __DIR__ . '/header.php';
?>
<div class="main-content">
    <h1>Search Results</h1>

    <form action="search.php" method="get" style="margin-bottom:20px;">
        <input type="text" name="q"
               value="<?php echo htmlspecialchars($q); ?>"
               placeholder="Search products &amp; services..."
               style="padding:4px; min-width:200px;">
        <button type="submit">Search</button>
    </form>

    <?php if ($q === ''): ?>
        <p>Type something in the search box to find products or services.</p>
    <?php else: ?>
        <h2>Products</h2>
        <?php if (empty($products)): ?>
            <p>No products found for "<?php echo htmlspecialchars($q); ?>".</p>
        <?php else: ?>
            <ul>
                <?php foreach ($products as $p): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($p['product_name']); ?></strong>
                        - $<?php echo number_format((float)$p['price'], 2); ?><br>
                        <?php echo nl2br(htmlspecialchars($p['description'])); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Services</h2>
        <?php if (empty($services)): ?>
            <p>No services found for "<?php echo htmlspecialchars($q); ?>".</p>
        <?php else: ?>
            <ul>
                <?php foreach ($services as $s): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                        (<?php echo htmlspecialchars($s['duration']); ?>)
                        - $<?php echo number_format((float)$s['price'], 2); ?><br>
                        <?php echo nl2br(htmlspecialchars($s['description'])); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>