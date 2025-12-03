<?php
require __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$msg    = '';
$error  = '';

// Load current user info
$stmt = mysqli_prepare(
    $conn,
    "SELECT first_name, last_name, email, username, created_at
     FROM users
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$dbUser = mysqli_fetch_assoc($res);
mysqli_free_result($res);
mysqli_stmt_close($stmt);

if (!$dbUser) {
    $error = "User not found.";
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $first    = trim($_POST['first_name'] ?? '');
    $last     = trim($_POST['last_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($first === '' || $last === '' || $email === '') {
        $error = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($password !== '') {
        // If user is changing password, enforce same rules as register
        if (strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)) {
            $error = 'New password must be at least 8 characters and include upper, lower, and a number.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        }
    }

    if (!$error) {
        // Update basic profile
        $sql = "UPDATE users
                SET first_name = ?, last_name = ?, email = ?
                WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $first, $last, $email, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // If password provided and passed validation, update it
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users SET password_hash = ? WHERE user_id = ?"
            );
            mysqli_stmt_bind_param($stmt, "si", $hash, $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Refresh user data
        $stmt = mysqli_prepare(
            $conn,
            "SELECT first_name, last_name, email, username, created_at
             FROM users
             WHERE user_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $res    = mysqli_stmt_get_result($stmt);
        $dbUser = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        mysqli_stmt_close($stmt);

        if (is_array($dbUser) && isset($dbUser['username'])) {
            $_SESSION['username'] = $dbUser['username'];
        }

        header('Location: account.php?updated=1');
        exit;
    }
}

if (isset($_GET['updated']) && !$error) {
    $msg = 'Profile updated.';
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <main class="right-content">
            <h2>My Account</h2>

            <?php if ($error): ?>
                <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if ($msg && !$error): ?>
                <p style="color:green;"><?php echo htmlspecialchars($msg); ?></p>
            <?php endif; ?>

            <?php if ($dbUser): ?>
                <form method="post" action="account.php" class="auth-form">
                    <p><strong>Username:</strong>
                        <?php echo htmlspecialchars($dbUser['username']); ?>
                    </p>

                    <label>
                        First Name:
                        <input type="text" name="first_name"
                               value="<?php echo htmlspecialchars($dbUser['first_name']); ?>" required>
                    </label>

                    <label>
                        Last Name:
                        <input type="text" name="last_name"
                               value="<?php echo htmlspecialchars($dbUser['last_name']); ?>" required>
                    </label>

                    <label>
                        Email:
                        <input type="email" name="email"
                               value="<?php echo htmlspecialchars($dbUser['email']); ?>" required>
                    </label>

                    <p><small>Account created:
                        <?php echo htmlspecialchars($dbUser['created_at']); ?>
                    </small></p>

                    <hr>

                    <h3>Change Password (optional)</h3>
                    <label>
                        New Password:
                        <input type="password" name="password">
                    </label>
                    <label>
                        Confirm New Password:
                        <input type="password" name="confirm_password">
                    </label>

                    <button type="submit">Save Changes</button>
                </form>
            <?php endif; ?>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>
