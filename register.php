<?php
require __DIR__ . '/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first    = trim($_POST['first_name'] ?? '');
    $last     = trim($_POST['last_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($first === '' || $last === '' || $email === '' || $username === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8 ||
              !preg_match('/[A-Z]/', $password) ||
              !preg_match('/[a-z]/', $password) ||
              !preg_match('/[0-9]/', $password)) {
        $error = 'Password must be at least 8 characters and include upper, lower, and a number.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (first_name, last_name, email, username, password_hash, role, created_at) 
                 VALUES (?,?,?,?,?,'user', NOW())"
            );
            $stmt->bind_param('sssss', $first, $last, $email, $username, $hash);
            if ($stmt->execute()) {
                $success = 'Account created. You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Error creating account.';
            }
        }
        $stmt->close();
    }
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <main class="right-content">
            <h2>Register</h2>

            <?php if ($error): ?>
                <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p style="color:green;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="post" action="register.php" class="auth-form">
                <label>
                    First Name:
                    <input type="text" name="first_name" required
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </label>
                <label>
                    Last Name:
                    <input type="text" name="last_name" required
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </label>
                <label>
                    Email:
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </label>
                <label>
                    Username:
                    <input type="text" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </label>
                <label>
                    Password:
                    <input type="password" name="password" required>
                </label>
                <label>
                    Confirm Password:
                    <input type="password" name="confirm_password" required>
                </label>

                <button type="submit">Register</button>
            </form>

            <p>Already have an account? <a href="login.php">Login</a></p>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>