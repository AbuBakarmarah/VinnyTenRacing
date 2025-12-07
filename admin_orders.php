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

<div class="main-content" style="padding:24px;">
    <h1 style="margin-bottom:10px;">Admin: Orders</h1>

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

    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(15,23,42,0.1);padding:16px;">
        <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
            <thead>
                <tr style="background:#013783;color:#fff;">
                    <th style="padding:8px 10px;text-align:left;">Order ID</th>
                    <th style="padding:8px 10px;text-align:left;">User</th>
                    <th style="padding:8px 10px;text-align:left;">Email</th>
                    <th style="padding:8px 10px;text-align:left;">Total</th>
                    <th style="padding:8px 10px;text-align:left;">Status</th>
                    <th style="padding:8px 10px;text-align:left;">Created</th>
                    <th style="padding:8px 10px;text-align:left;">Update Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($o = mysqli_fetch_assoc($res)): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:8px 10px;"><?php echo $o['order_id']; ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($o['username']); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($o['email']); ?></td>
                    <td style="padding:8px 10px;">$<?php echo number_format($o['total_amount'], 2); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($o['status']); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($o['created_at']); ?></td>
                    <td style="padding:8px 10px;">
                        <form method="post" action="" style="display:inline-flex;gap:4px;align-items:center;margin:0;">
                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                            <select name="status"
                                    style="padding:3px 6px;border-radius:4px;border:1px solid #d1d5db;">
                                <option value="pending"    <?php if ($o['status']==='pending')    echo 'selected'; ?>>pending</option>
                                <option value="processing" <?php if ($o['status']==='processing') echo 'selected'; ?>>processing</option>
                                <option value="completed"  <?php if ($o['status']==='completed')  echo 'selected'; ?>>completed</option>
                                <option value="cancelled"  <?php if ($o['status']==='cancelled')  echo 'selected'; ?>>cancelled</option>
                            </select>
                            <button type="submit"
                                    style="padding:4px 8px;border:none;border-radius:4px;
                                           background:#013783;color:#fff;cursor:pointer;">
                                Save
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
