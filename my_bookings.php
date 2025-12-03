<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$bookings = [];
try {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT b.id,
                b.booking_date,
                b.notes,
                b.created_at,
                s.name  AS service_name,
                s.price AS service_price
         FROM bookings b
         JOIN services s ON b.service_id = s.id
         WHERE b.user_id = ?
         ORDER BY b.booking_date DESC"
    );
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    // log in real app
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>My Bookings</h1>

    <?php if (empty($bookings)): ?>
        <p>You have no bookings yet. <a href="services.php">Book a service.</a></p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:900px;">
            <thead style="background-color:#013783; color:#fff;">
                <tr>
                    <th>Service</th>
                    <th>Booking Date</th>
                    <th>Notes</th>
                    <th>Price</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($b['notes'])); ?></td>
                    <td>$<?php echo number_format((float)$b['service_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>