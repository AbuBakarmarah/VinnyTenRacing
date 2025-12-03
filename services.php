<?php
require __DIR__ . '/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id'] ?? null;

$bookingSuccess = null;
$bookingError   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isLoggedIn) {
        $bookingError = "You must be logged in to make a booking.";
    } else {
        $service_id   = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
        $booking_date = trim($_POST['booking_date'] ?? '');
        $notes        = trim($_POST['notes'] ?? '');

        if ($service_id <= 0 || $booking_date === '') {
            $bookingError = "Please choose a service and a valid date/time.";
        } else {
            $timestamp = strtotime($booking_date);
            if ($timestamp === false || $timestamp < time()) {
                $bookingError = "Booking date must be in the future.";
            } else {
                try {
                    $stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO bookings (user_id, service_id, booking_date, notes, created_at)
                         VALUES (?, ?, ?, ?, NOW())"
                    );
                    mysqli_stmt_bind_param($stmt, "iiss", $userId, $service_id, $booking_date, $notes);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $bookingSuccess = "Your booking has been created successfully.";
                } catch (mysqli_sql_exception $e) {
                    $bookingError = "Could not create booking. Please try again.";
                }
            }
        }
    }
}

$services = [];
try {
    $result = mysqli_query(
        $conn,
        "SELECT id, name, description, price, duration
         FROM services
         ORDER BY name"
    );
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }
    mysqli_free_result($result);
} catch (mysqli_sql_exception $e) {
    // log error in real app
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <h1>Performance Services</h1>

    <?php if (!empty($bookingSuccess)): ?>
        <div style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom:10px;">
            <?php echo htmlspecialchars($bookingSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($bookingError)): ?>
        <div style="padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; margin-bottom:10px;">
            <?php echo htmlspecialchars($bookingError); ?>
        </div>
    <?php endif; ?>

    <section style="margin-bottom:30px;">
        <h2>Available Services</h2>

        <?php if (empty($services)): ?>
            <p>No services are currently configured.</p>
        <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:900px;">
                <thead style="background-color:#013783; color:#fff;">
                    <tr>
                        <th>Service</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($service['description'])); ?></td>
                        <td><?php echo htmlspecialchars($service['duration']); ?></td>
                        <td>$<?php echo number_format((float)$service['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Book a Service</h2>

        <?php if (!$isLoggedIn): ?>
            <p>You must <a href="login.php">log in</a> or <a href="register.php">register</a> to make a booking.</p>
        <?php elseif (empty($services)): ?>
            <p>No services are available to book at this time.</p>
        <?php else: ?>
            <form method="post" style="max-width:500px;">
                <div style="margin-bottom:10px;">
                    <label for="service_id">Service</label><br>
                    <select id="service_id" name="service_id" required style="width:100%; padding:5px;">
                        <option value="">-- Choose a Service --</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo (int)$service['id']; ?>">
                                <?php echo htmlspecialchars($service['name']); ?> 
                                (<?php echo '$' . number_format((float)$service['price'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:10px;">
                    <label for="booking_date">Preferred Date & Time</label><br>
                    <input type="datetime-local" id="booking_date" name="booking_date"
                           required style="width:100%; padding:5px;">
                </div>

                <div style="margin-bottom:10px;">
                    <label for="notes">Notes (optional)</label><br>
                    <textarea id="notes" name="notes" rows="4" style="width:100%; padding:5px;"></textarea>
                </div>

                <button type="submit"
                        style="background-color:#013783; color:#fff; border:none; padding:8px 16px; cursor:pointer;">
                    Book Now
                </button>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>