<?php
require __DIR__ . '/config.php';
require_login();

if (!csrf_verify()) {
    http_response_code(400);
    echo 'Invalid CSRF token';
    exit;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/stripe_config.php'; // sets Stripe API key

$cart = $_SESSION['cart'] ?? [];

// cart should look like: [ product_id => quantity_int ]
if (empty($cart) || !is_array($cart)) {
    header('Location: cart.php');
    exit;
}

// --- Build list of product IDs from cart keys ---
$productIds = array_keys($cart);
$productIds = array_map('intval', $productIds);

if (empty($productIds)) {
    header('Location: cart.php');
    exit;
}

$idList = implode(',', $productIds);

// --- Fetch products from DB ---
$line_items = [];
$cart_total = 0.0;

$sql = "SELECT product_id, product_name, price
        FROM products
        WHERE product_id IN ($idList)";
$result = mysqli_query($conn, $sql);

if (!$result) {
    error_log('DB error in create_checkout_session: ' . mysqli_error($conn));
    http_response_code(500);
    echo 'Database error';
    exit;
}

while ($row = mysqli_fetch_assoc($result)) {
    $pid = (int)$row['product_id'];

    // quantity from cart (int, not array)
    $qty = isset($cart[$pid]) ? (int)$cart[$pid] : 0;

    // Skip anything with qty < 1 so Stripe never sees 0
    if ($qty < 1) {
        continue;
    }

    $price = (float)$row['price'];
    $cart_total += $price * $qty;

    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $row['product_name'],
            ],
            'unit_amount' => (int)round($price * 100), // cents
        ],
        'quantity' => $qty,
    ];
}

if (empty($line_items)) {
    // all items had invalid quantities
    header('Location: cart.php');
    exit;
}

// --- Create order in DB ---
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO orders (user_id, total_amount, status, created_at, stripe_session_id)
     VALUES (?, ?, 'pending', NOW(), NULL)"
);
mysqli_stmt_bind_param($stmt, 'id', $_SESSION['user_id'], $cart_total);
mysqli_stmt_execute($stmt);
$order_id = mysqli_insert_id($conn);

// --- Create Stripe Checkout Session ---
$session = \Stripe\Checkout\Session::create([
    'mode' => 'payment',
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'success_url' => 'http://localhost/webdev2/lab2/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/webdev2/lab2/cart.php',
    'metadata' => [
        'order_id' => $order_id,
        'user_id'  => $_SESSION['user_id'],
    ],
]);

// Save Stripe session id on order
$update = mysqli_prepare(
    $conn,
    "UPDATE orders SET stripe_session_id = ? WHERE order_id = ?"
);
$session_id = $session->id;
mysqli_stmt_bind_param($update, 'si', $session_id, $order_id);
mysqli_stmt_execute($update);

// Redirect to Stripe
header('Location: ' . $session->url);
exit;
