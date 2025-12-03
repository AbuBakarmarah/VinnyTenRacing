<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$msg = "";

// UPDATE STATUS
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'], $_POST['status'])) {
    $id     = (int) $_POST['order_id'];
    $status = $_POST['status'];

    $allowed = ['pending','processing','completed','cancelled'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $stmt = mysqli_prepare($conn,
            "UPDATE orders SET status=? WHERE order_id=?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = "Order #$id status updated to $status.";
    }
}

// LIST ORDERS
$sql = "SELECT o.order_id, o.user_id, o.total_amount, o.status, o.created_at,
               u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC";
$res = mysqli_query($conn, $sql);

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Admin: Orders</h1>
    <p>
        <a href="admin_users.php">Users</a> |
        <a href="admin_products.php">Products</a> |
        <a href="admin_services.php">Services</a>
    </p>

    <?php if ($msg): ?>
        <p style="color:green;"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="4" style="border-collapse:collapse; width:100%;">
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created</th>
            <th>Update Status</th>
        </tr>
        <?php while ($o = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo $o['order_id']; ?></td>
                <td><?php echo htmlspecialchars($o['username']); ?></td>
                <td><?php echo htmlspecialchars($o['email']); ?></td>
                <td>$<?php echo number_format($o['total_amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($o['status']); ?></td>
                <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                        <select name="status">
                            <option value="pending"    <?php if ($o['status']==='pending')    echo 'selected'; ?>>pending</option>
                            <option value="processing" <?php if ($o['status']==='processing') echo 'selected'; ?>>processing</option>
                            <option value="completed"  <?php if ($o['status']==='completed')  echo 'selected'; ?>>completed</option>
                            <option value="cancelled"  <?php if ($o['status']==='cancelled')  echo 'selected'; ?>>cancelled</option>
                        </select>
                        <button type="submit">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
