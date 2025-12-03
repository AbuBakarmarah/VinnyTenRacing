<?php
require __DIR__ . '/config.php';

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
    $msg = "Role updated.";
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

$res = mysqli_query(
    $conn,
    "SELECT user_id, username, email, role, created_at
     FROM users
     ORDER BY user_id DESC"
);

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Admin: Users</h1>

    <p>
        <a href="admin_products.php">Products</a> |
        <a href="admin_services.php">Services</a> |
        <a href="admin_orders.php">Orders</a> |
        <a href="admin_messages.php">Messages</a>
    </p>

    <?php if ($msg): ?>
        <p style="color:green;"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="4" style="border-collapse:collapse; width:100%;">
        <tr style="background-color:#013783; color:#fff;">
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo (int)$row['user_id']; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <?php if ((int)$row['user_id'] === (int)$_SESSION['user_id']): ?>
                        <?php echo htmlspecialchars($row['role']); ?>
                    <?php else: ?>
                        <form method="post" style="margin:0;">
                            <input type="hidden" name="user_id"
                                   value="<?php echo (int)$row['user_id']; ?>">
                            <select name="role">
                                <option value="user"  <?php if ($row['role']==='user')  echo 'selected'; ?>>user</option>
                                <option value="admin" <?php if ($row['role']==='admin') echo 'selected'; ?>>admin</option>
                            </select>
                            <button type="submit">Save</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                    <?php if ((int)$row['user_id'] === (int)$_SESSION['user_id']): ?>
                        (you)
                    <?php else: ?>
                        <a href="admin_users.php?delete=<?php echo (int)$row['user_id']; ?>"
                           onclick="return confirm('Delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
