<?php
require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinny Ten Racing</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="global.css">
    <script src="slideShow.js" defer></script>
    <script src="youtubeAPICall.js" defer></script>
</head>
<body>

<div class="page-wrapper">
    <header class="header">
        <div class="logo">
            <img src="./assets/VTR-Logo-transparent.png" alt="VTR Logo" width="100" height="100">
            <h1>Vinny Ten Racing</h1>
        </div>

        <div class="shop-info">
            <h2>631-414-7590</h2>
            <p>1081 ROUTE 109 LINDENHURST, NY 11757</p>
        </div>
    </header>

    <div class="utility-bar">
        <div class="search">
            <form action="search.php" method="get" style="display:flex; gap:4px;">
                <input type="text"
                       name="q"
                       placeholder="Search..."
                       value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <button type="submit">🔎</button>
            </form>
        </div>

        <div class="personal-features">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="account.php">👤 Account</a>
                <span><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="admin_users.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">👤 Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <a href="my_bookings.php">📘 My bookings</a>
        </div>
    </div>

    <nav class="nav-bar">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="services.php">Performance Services</a>
        <a href="gallery.html">Gallery</a>
        <a href="about.html">About</a>
        <a href="contact.php">Contact</a>
    </nav>

    <!-- main content of each page starts here -->
    <main class="main-content">
