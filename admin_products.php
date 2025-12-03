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
        // cast numeric values
        $price = (float)$price;

        if (!is_numeric($stock)) {
            $stock = 0;
        }
        $stock = (int)$stock;
        if ($stock < 0) {
            $stock = 0;
        }

        // validate image URL if provided
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

<div class="main-content">
    <h1>Admin: Products</h1>
    <p>
        <a href="admin_users.php">Users</a> |
        <a href="admin_services.php">Services</a> |
        <a href="admin_orders.php">Orders</a> |
        <a href="admin_messages.php">Messages</a>
    </p>

    <?php if ($msg): ?>
        <p style="color:green;"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <h2>Existing Products</h2>
    <table border="1" cellpadding="4" style="border-collapse:collapse; width:100%;">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($p = mysqli_fetch_assoc($list)): ?>
            <tr>
                <td><?php echo (int)$p['product_id']; ?></td>
                <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td>$<?php echo number_format((float)$p['price'], 2); ?></td>
                <td><?php echo (int)$p['stock_qty']; ?></td>
                <td>
                    <?php if (!empty($p['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt=""
                             style="max-width:80px; max-height:80px;">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                <td>
                    <a href="admin_products.php?edit=<?php echo (int)$p['product_id']; ?>">Edit</a> |
                    <a href="admin_products.php?delete=<?php echo (int)$p['product_id']; ?>"
                       onclick="return confirm('Delete this product?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2><?php echo $edit ? "Edit Product #".(int)$edit['product_id'] : "Add New Product"; ?></h2>
    <form method="post" action="">
        <?php if ($edit): ?>
            <input type="hidden" name="product_id"
                   value="<?php echo (int)$edit['product_id']; ?>">
        <?php endif; ?>

        <p><label>Product Name:
           <input type="text" name="product_name" required
                  value="<?php echo htmlspecialchars($edit['product_name'] ?? ''); ?>">
        </label></p>

        <p><label>Description:<br>
           <textarea name="description" rows="4" cols="50"><?php
             echo htmlspecialchars($edit['description'] ?? '');
           ?></textarea>
        </label></p>

        <p><label>Price ($):
           <input type="number" step="0.01" name="price" required
                  value="<?php echo htmlspecialchars($edit['price'] ?? ''); ?>">
        </label></p>

        <p><label>Stock Quantity:
           <input type="number" name="stock_qty"
                  value="<?php echo htmlspecialchars($edit['stock_qty'] ?? '0'); ?>">
        </label></p>

        <p><label>Image URL:
           <input type="text" name="image_url"
                  value="<?php echo htmlspecialchars($edit['image_url'] ?? ''); ?>">
        </label></p>

        <button type="submit"><?php echo $edit ? "Update Product" : "Add Product"; ?></button>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
