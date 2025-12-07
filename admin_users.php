
<?php
require __DIR__ . '/config.php';

// Access control
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$msg = "";

// Update role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $userId = (int)$_POST['user_id'];
    $role   = $_POST['role'] === 'admin' ? 'admin' : 'user';

    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $msg = "User role updated.";
}

// Delete user (not yourself)
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    if ($userId !== (int)$_SESSION['user_id']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = "User deleted.";
    }
    header("Location: admin_users.php");
    exit;
}

// Fetch users
$res = mysqli_query(
    $conn,
    "SELECT user_id, username, email, role, created_at
     FROM users
     ORDER BY user_id DESC"
);

include __DIR__ . '/header.php';
?>

<div class="main-content" style="padding:24px;">
    <h1 style="margin-bottom:10px;">Admin: Users</h1>

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
        <h2 style="margin-top:0;margin-bottom:12px;">User Accounts</h2>

        <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
            <thead>
                <tr style="background:#013783;color:#fff;">
                    <th style="text-align:left;padding:8px 10px;">ID</th>
                    <th style="text-align:left;padding:8px 10px;">Username</th>
                    <th style="text-align:left;padding:8px 10px;">Email</th>
                    <th style="text-align:left;padding:8px 10px;">Role</th>
                    <th style="text-align:left;padding:8px 10px;">Created</th>
                    <th style="text-align:left;padding:8px 10px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:8px 10px;"><?php echo (int)$row['user_id']; ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['username']); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="padding:8px 10px;">
                        <?php if ((int)$row['user_id'] === (int)$_SESSION['user_id']): ?>
                            <span style="padding:2px 6px;border-radius:999px;
                                         background:#e5e7eb;font-size:0.85rem;">
                                <?php echo htmlspecialchars($row['role']); ?> (you)
                            </span>
                        <?php else: ?>
                            <form method="post" style="margin:0;display:inline-flex;gap:4px;align-items:center;">
                                <input type="hidden" name="user_id"
                                       value="<?php echo (int)$row['user_id']; ?>">
                                <select name="role" style="padding:3px 6px;border-radius:4px;border:1px solid #d1d5db;">
                                    <option value="user"  <?php if ($row['role']==='user')  echo 'selected'; ?>>user</option>
                                    <option value="admin" <?php if ($row['role']==='admin') echo 'selected'; ?>>admin</option>
                                </select>
                                <button type="submit"
                                        style="padding:4px 8px;border:none;border-radius:4px;
                                               background:#013783;color:#fff;cursor:pointer;">
                                    Save
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td style="padding:8px 10px;">
                        <?php if ((int)$row['user_id'] === (int)$_SESSION['user_id']): ?>
                            <span style="color:#6b7280;font-size:0.9rem;">(current admin)</span>
                        <?php else: ?>
                            <a href="admin_users.php?delete=<?php echo (int)$row['user_id']; ?>"
                               onclick="return confirm('Delete this user?');"
                               style="color:#b91c1c;">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
