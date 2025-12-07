<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    http_response_code(400);
    header("Location: shop.php");
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty       = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

if ($productId <= 0 || $qty <= 0) {
    header("Location: shop.php");
    exit;
}

// Initialize cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add / update quantity
if (!isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] = 0;
}
$_SESSION['cart'][$productId] += $qty;

// Optionally cap at 10
if ($_SESSION['cart'][$productId] > 10) {
    $_SESSION['cart'][$productId] = 10;
}

header("Location: cart.php");
exit;
