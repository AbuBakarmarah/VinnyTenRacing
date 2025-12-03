<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

$msg = "";

// Delete message
if (isset($_GET['delete'])) {
    $messageId = (int)$_GET['delete'];
    if ($messageId > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE message_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $messageId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = "Message deleted.";
    }
    header("Location: admin_messages.php");
    exit;
}

$res = mysqli_query(
    $conn,
    "SELECT message_id, user_id, name, email, subject, message, created_at
     FROM contact_messages
     ORDER BY created_at DESC"
);

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Admin: Contact Messages</h1>

    <p>
        <a href="admin_users.php">Users</a> |
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
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo (int)$row['message_id']; ?></td>
                <td><?php echo htmlspecialchars($row['user_id'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                    <a href="admin_messages.php?delete=<?php echo (int)$row['message_id']; ?>"
                       onclick="return confirm('Delete this message?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include __DIR__ . '/footer.php'; ?>
