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

<div class="main-content" style="padding:24px;">
    <h1 style="margin-bottom:10px;">Admin: Contact Messages</h1>

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
                <tr style="background:#013783; color:#fff;">
                    <th style="padding:8px 10px;text-align:left;">ID</th>
                    <th style="padding:8px 10px;text-align:left;">User ID</th>
                    <th style="padding:8px 10px;text-align:left;">Name</th>
                    <th style="padding:8px 10px;text-align:left;">Email</th>
                    <th style="padding:8px 10px;text-align:left;">Subject</th>
                    <th style="padding:8px 10px;text-align:left;">Message</th>
                    <th style="padding:8px 10px;text-align:left;">Created</th>
                    <th style="padding:8px 10px;text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:8px 10px;"><?php echo (int)$row['message_id']; ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['user_id'] ?? ''); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td style="padding:8px 10px;"><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                    <td style="padding:8px 10px;"><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td style="padding:8px 10px;">
                        <a href="admin_messages.php?delete=<?php echo (int)$row['message_id']; ?>"
                           onclick="return confirm('Delete this message?');"
                           style="color:#b91c1c;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
