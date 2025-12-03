<?php
require __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, username, password_hash, role
             FROM users
             WHERE username = ?"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res  = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="main-content">
    <section class="content-grid">
        <main class="right-content">
            <h2>Login</h2>

            <?php if ($error): ?>
                <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="post" action="login.php" class="auth-form">
                <label>
                    Username:
                    <input type="text" name="username" required>
                </label>

                <label>
                    Password:
                    <input type="password" name="password" required>
                </label>

                <button type="submit">Login</button>
            </form>

            <p>Don’t have an account? <a href="register.php">Register</a></p>
        </main>
    </section>
</div>

<?php include __DIR__ . '/footer.php'; ?>