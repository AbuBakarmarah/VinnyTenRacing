<?php
require __DIR__ . '/config.php';

$error   = '';
$success = '';
$name = $email = $subject = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($message) < 10) {
        $error = 'Message should be at least 10 characters.';
    } else {
        try {
            // if logged in, capture user_id, else NULL
            $userId = $_SESSION['user_id'] ?? null;

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO contact_messages (user_id, name, email, subject, message, created_at)
                 VALUES (?,?,?,?,?, NOW())"
            );
            mysqli_stmt_bind_param($stmt, "issss", $userId, $name, $email, $subject, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $success = 'Thank you for your feedback. We will get back to you as soon as possible.';
            $name = $email = $subject = $message = '';
        } catch (mysqli_sql_exception $e) {
            $error = 'Could not send your message. Please try again later.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <aside class="left-sidebar">
            <div class="vinny-ten-info">
                <h2>Contact & Feedback</h2>
                <p>We’d love to hear from you.</p>
                <ul class="info-section">
                    <li><strong>Phone:</strong> 631-414-7590</li>
                    <li><strong>Shop Address:</strong><br>
                        1081 ROUTE 109<br>
                        LINDENHURST, NY 11757
                    </li>
                    <li><strong>Business Hours:</strong><br>
                        Mon – Sat: 9 AM – 6 PM<br>
                        Sun: Closed
                    </li>
                </ul>
            </div>
        </aside>

        <main class="right-content">
            <h2 class="page-title">Customer Feedback</h2>

            <?php if ($error): ?>
                <div style="padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; margin-bottom:10px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom:10px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="post" class="auth-form">
                    <label>
                        Name:
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($name); ?>">
                    </label>

                    <label>
                        Email:
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($email); ?>">
                    </label>

                    <label>
                        Subject:
                        <input type="text" name="subject" required
                               value="<?php echo htmlspecialchars($subject); ?>">
                    </label>

                    <label>
                        Message:
                        <textarea name="message" rows="5" required><?php
                            echo htmlspecialchars($message);
                        ?></textarea>
                    </label>

                    <button type="submit">Send Message</button>
                </form>
            </div>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>