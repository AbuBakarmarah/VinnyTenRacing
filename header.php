
<?php
require_once __DIR__ . '/config.php';

$is_logged_in = !empty($_SESSION['user_id']);
$is_admin     = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vinny Ten Racing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="global.css">

    <!-- JS for homepage slideshow and YouTube latest video -->
    <script src="slideShow.js" defer></script>
    <script src="youtubeAPICall.js" defer></script>
</head>
<body>
<div class="page-wrapper">
    <!-- Top header -->
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="assets/VTR-Logo-transparent.png"
                     alt="Vinny Ten Racing Logo"
                     width="72"
                     height="72">
            </a>
            <div>
                <h1>Vinny Ten Racing</h1>
                <p style="margin:0;color:#6b7280;font-size:0.9rem;">
                    Built for speed, tuned for perfection.
                </p>
            </div>
        </div>

        <div class="shop-info">
            <h2>Performance &amp; Tuning</h2>
            <p>631-414-7590</p>
            <p>1081 ROUTE 109, LINDENHURST, NY 11757</p>
        </div>
    </header>

    <!-- Search + account bar -->
    <div class="utility-bar">
        <div class="search">
            <form action="search.php" method="get">
                <input type="search" name="q" placeholder="Search packages, services..." required>
                <button type="submit">Search</button>
            </form>
        </div>

        <nav class="personal-features">
            <?php if ($is_logged_in): ?>
                <a href="account.php">Account</a>
                <a href="my_bookings.php">My bookings</a>
                <?php if ($is_admin): ?>
                    <a href="admin_products.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Main navigation -->
    <nav class="nav-bar">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="services.php">Performance Services</a>
        <a href="gallery.php">Gallery</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="cart.php">Cart</a>
    </nav>
