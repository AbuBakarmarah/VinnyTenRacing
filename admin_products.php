<?php
require __DIR__ . '/config.php';

// Access control: only admins
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$msg = "";

// DELETE
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = "Product deleted.";
    }
    header("Location: admin_products.php");
    exit;
}

// ADD / UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST['product_name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock_qty'] ?? '');
    $image = trim($_POST['image_url'] ?? '');

    if ($name === "" || $price === "" || !is_numeric($price)) {
        $msg = "Name and numeric price are required.";
    } else {
        $price = (float)$price;

        if (!is_numeric($stock)) {
            $stock = 0;
        }
        $stock = (int)$stock;
        if ($stock < 0) {
            $stock = 0;
        }

        if ($image !== '' &&
            !preg_match('~^https?://.+\.(jpe?g|png|webp|gif)$~i', $image)) {
            $msg = "Image URL must be a valid http(s) URL to a JPG/PNG/WebP/GIF image.";
        } elseif ($price <= 0) {
            $msg = "Price must be greater than zero.";
        } else {
            // UPDATE
            if (!empty($_POST['product_id'])) {
                $id = (int) $_POST['product_id'];
                $sql = "UPDATE products
                        SET product_name=?, description=?, price=?, stock_qty=?, image_url=?
                        WHERE product_id=?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssdisi",
                    $name,
                    $desc,
                    $price,
                    $stock,
                    $image,
                    $id
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = "Product updated.";
            } else {
                // INSERT
                $sql = "INSERT INTO products (product_name, description, price, stock_qty, image_url)
                        VALUES (?,?,?,?,?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssdis",
                    $name,
                    $desc,
                    $price,
                    $stock,
                    $image
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = "Product added.";
            }
        }
    }
}

// EDIT MODE
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int) $_GET['edit'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT product_id, product_name, description, price, stock_qty, image_url
         FROM products WHERE product_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $eid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && mysqli_num_rows($res) === 1) {
        $edit = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($stmt);
}

// LIST
$list = mysqli_query(
    $conn,
    "SELECT product_id, product_name, price, stock_qty, image_url, created_at
     FROM products
     ORDER BY created_at DESC"
);

include __DIR__ . '/header.php';
?>

<div class="main-content" style="padding:24px;">
    <h1 style="margin-bottom:10px;">Admin: Products</h1>

    <nav class="admin-nav" style="margin-bottom:20px;">
        <a href="admin_users.php">Users</a>
        <a href="admin_products.php">Products</a>
        <a href="admin_orders.php">Orders</a>
        <a href="admin_messages.php">Messages</a>
        <a href="admin_services.php">Services</a>
    </nav>

    <?php if ($msg): ?>
        <div style="margin-bottom:16px;padding:10px 14px;border-radius:6px;
                    background:#e0f2fe;color:#075985;border:1px solid #7dd3fc;">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(15,23,42,0.1);padding:16px;margin-bottom:24px;">
        <h2 style="margin-top:0;margin-bottom:12px;">Existing Products</h2>
        <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
            <thead>
                <tr style="background:#013783;color:#fff;">
                    <th style="padding:8px 10px;text-align:left;">ID</th>
                    <th style="padding:8px 10px;text-align:left;">Name</th>
                    <th style="padding:8px 10px;text-align:left;">Price</th>
                    <th style="padding:8px 10px;text-align:left;">Stock</th>
                    <th style="padding:8px 10px;text-align:left;">Image</th>
                    <th style="padding:8px 10px;text-align:left;">Created</th>
                    <th style="padding:8px 10px;text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = mysqli_fetch_assoc($list)): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:8px 10px;"><?php echo (int)$p['product_id']; ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($p['product_name']); ?></td>
                    <td style="padding:8px 10px;">$<?php echo number_format((float)$p['price'], 2); ?></td>
                    <td style="padding:8px 10px;"><?php echo (int)$p['stock_qty']; ?></td>
                    <td style="padding:8px 10px;">
                        <?php if (!empty($p['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt=""
                                 style="max-width:80px; max-height:80px;border-radius:4px;">
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($p['created_at']); ?></td>
                    <td style="padding:8px 10px;">
                        <a href="admin_products.php?edit=<?php echo (int)$p['product_id']; ?>">Edit</a>
                        |
                        <a href="admin_products.php?delete=<?php echo (int)$p['product_id']; ?>"
                           onclick="return confirm('Delete this product?');"
                           style="color:#b91c1c;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(15,23,42,0.1);padding:16px;">
        <h2 style="margin-top:0;margin-bottom:12px;">
            <?php echo $edit ? "Edit Product #".(int)$edit['product_id'] : "Add New Product"; ?>
        </h2>

        <form method="post" action="" style="max-width:600px;">
            <?php if ($edit): ?>
                <input type="hidden" name="product_id"
                       value="<?php echo (int)$edit['product_id']; ?>">
            <?php endif; ?>

            <p>
                <label>Product Name:<br>
                    <input type="text" name="product_name" required
                           value="<?php echo htmlspecialchars($edit['product_name'] ?? ''); ?>"
                           style="width:100%;padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                </label>
            </p>

            <p>
                <label>Description:<br>
                    <textarea name="description" rows="4" cols="50"
                              style="width:100%;padding:6px;border-radius:4px;border:1px solid #d1d5db;"><?php
                        echo htmlspecialchars($edit['description'] ?? '');
                    ?></textarea>
                </label>
            </p>

            <p>
                <label>Price ($):<br>
                    <input type="number" step="0.01" name="price" required
                           value="<?php echo htmlspecialchars($edit['price'] ?? ''); ?>"
                           style="width:100%;padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                </label>
            </p>

            <p>
                <label>Stock Quantity:<br>
                    <input type="number" name="stock_qty"
                           value="<?php echo htmlspecialchars($edit['stock_qty'] ?? '0'); ?>"
                           style="width:100%;padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                </label>
            </p>

            <p>
                <label>Image URL:<br>
                    <input type="text" name="image_url"
                           value="<?php echo htmlspecialchars($edit['image_url'] ?? ''); ?>"
                           style="width:100%;padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                </label>
            </p>

            <button type="submit"
                    style="background:#013783;color:#fff;border:none;border-radius:4px;
                           padding:8px 16px;cursor:pointer;">
                <?php echo $edit ? "Update Product" : "Add Product"; ?>
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
