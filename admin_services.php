<?php
require __DIR__ . '/config.php';

// If the user is not logged in, they can see services but not book
$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id'] ?? null;

// Handle booking form submit
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

// Load services from DB for display + dropdown
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
    // You might log this error in production
}

include __DIR__ . '/header.php';
?>

<div class="main-content" style="padding:24px;">
    <h1 style="margin-bottom:10px;">Performance Services</h1>

    <?php if (!empty($bookingSuccess)): ?>
        <div style="margin-bottom:16px;padding:10px 14px;border-radius:6px;
                    background:#d4edda;color:#155724;border:1px solid #c3e6cb;">
            <?php echo htmlspecialchars($bookingSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($bookingError)): ?>
        <div style="margin-bottom:16px;padding:10px 14px;border-radius:6px;
                    background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;">
            <?php echo htmlspecialchars($bookingError); ?>
        </div>
    <?php endif; ?>

    <section style="margin-bottom:30px;">
        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(15,23,42,0.1);padding:16px;">
            <h2 style="margin-top:0;margin-bottom:12px;">Available Services</h2>

            <?php if (empty($services)): ?>
                <p>No services are currently configured.</p>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;font-size:0.95rem;">
                    <thead>
                        <tr style="background-color:#013783; color:#fff;">
                            <th style="padding:8px 10px;text-align:left;">Service</th>
                            <th style="padding:8px 10px;text-align:left;">Description</th>
                            <th style="padding:8px 10px;text-align:left;">Duration</th>
                            <th style="padding:8px 10px;text-align:left;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:8px 10px;">
                                <?php echo htmlspecialchars($service['name']); ?>
                            </td>
                            <td style="padding:8px 10px;">
                                <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                            </td>
                            <td style="padding:8px 10px;">
                                <?php echo htmlspecialchars($service['duration']); ?>
                            </td>
                            <td style="padding:8px 10px;">
                                $<?php echo number_format((float)$service['price'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(15,23,42,0.1);padding:16px;max-width:520px;">
            <h2 style="margin-top:0;margin-bottom:12px;">Book a Service</h2>

            <?php if (!$isLoggedIn): ?>
                <p>
                    You must <a href="login.php">log in</a> or
                    <a href="register.php">register</a> to make a booking.
                </p>
            <?php elseif (empty($services)): ?>
                <p>No services are available to book at this time.</p>
            <?php else: ?>
                <form method="post">
                    <div style="margin-bottom:10px;">
                        <label for="service_id">Service</label><br>
                        <select id="service_id" name="service_id" required
                                style="width:100%; padding:6px;border-radius:4px;border:1px solid #d1d5db;">
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
                        <label for="booking_date">Preferred Date &amp; Time</label><br>
                        <input type="datetime-local" id="booking_date" name="booking_date" required
                               style="width:100%; padding:6px;border-radius:4px;border:1px solid #d1d5db;">
                    </div>

                    <div style="margin-bottom:10px;">
                        <label for="notes">Notes (optional)</label><br>
                        <textarea id="notes" name="notes" rows="4"
                                  style="width:100%; padding:6px;border-radius:4px;border:1px solid #d1d5db;"></textarea>
                    </div>

                    <button type="submit"
                            style="background-color:#013783; color:#fff; border:none;
                                   padding:8px 16px; cursor:pointer; border-radius:4px;">
                        Book Now
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
