## 9. New Cart + Stripe Features (Abubakar)

This section explains the **new features I added** and how the rest of the team should use them.

### 9.1. What I added

- A **shopping cart** system:
  - `cart_add.php` – adds items to the session cart.
  - `cart.php` – displays cart items, quantity, and totals.
  - `cart_remove.php` – removes items from the cart.
- A **Stripe Checkout** payment flow:
  - `checkout.php` – shows order summary before payment.
  - `create_checkout_session.php` – creates a Stripe Checkout Session and a new order in the `orders` table.
  - `checkout_success.php` – marks order as paid and clears the cart.
  - `checkout_cancel.php` – handles cancelled checkout.
- **Stripe integration**:
  - Installed `stripe/stripe-php` via Composer.
  - `stripe_config.php` – sets Stripe test keys and configures the SDK.
- **Orders pages**:
  - `orders` table in `sql/vinnyten_racing_schema.sql`.
  - `my_orders.php` – users can see their own orders.
  - `admin_orders.php` – admin can see all orders and update status.

### 9.2. How to run my part

1. **Install dependencies (only once)**

   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/VinnyTenRacing
   composer install
   ```

2. **Import database**

   - In phpMyAdmin, create a database (for example: `vinnyten_racing`).
   - Import `sql/vinnyten_racing_schema.sql`.

3. **Configure Stripe keys**

   Open:

   ```php
   // filepath: /Applications/XAMPP/xamppfiles/htdocs/webdev2/lab2/stripe_config.php
   ```
   ### Stripe keys (local only)

  Create a `.env` file or export environment variables before running:

```bash
export STRIPE_SECRET_KEY="sk_test_..."
export STRIPE_PUBLIC_KEY="pk_test_..."
```

The app reads these in `stripe_config.php` and **no real keys are stored in Git**.

   Make sure it has **test** keys:

   ```php
   // ...existing code...
   $stripeSecretKey = 'sk_test_...'; // Stripe test secret key
   $stripePublicKey = 'pk_test_...'; // Stripe test publishable key

   \Stripe\Stripe::setApiKey($stripeSecretKey);
   // ...existing code...
   ```

4. **Start the project**

   - Start **Apache** and **MySQL** in XAMPP.
   - Go to:
     - `http://localhost/VinnyTenRacing/index.php`
     - `http://localhost/VinnyTenRacing/shop.php`

5. **Test the full flow**

   - Register (`register.php`) and log in (`login.php`).
   - Go to `shop.php`, click a product → `product.php`.
   - Add the product to cart.
   - Go to `cart.php`, then click **Checkout** → `checkout.php`.
   - Click **Pay Securely with Card**.
   - On the Stripe page, use this **test** card:
     - Number: `4242 4242 4242 4242`
     - Any future expiry date, any CVC, any ZIP.
   - After paying, you will be redirected to `checkout_success.php` and the cart is cleared.

### 9.3. Important code detail (for teammates)

On the **product page**, the cart form sends the quantity using the field name `qty`:

```php
<form method="post" action="cart_add.php" class="add-to-cart-form">
    <?php csrf_field(); ?>
    <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
    <label>
        Qty:
        <input
            type="number"
            name="qty"
            value="1"
            min="1"
            max="<?php echo max(1, (int)$product['stock_qty']); ?>">
    </label>
    <button type="submit" class="btn-primary">Add to Cart</button>
</form>
```

`cart_add.php` expects this exact name:

```php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/webdev2/lab2/cart_add.php
// ...existing code...
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = (int)($_POST['qty'] ?? 1);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
// ...existing code...
```

If you change the input name, you must also update `cart_add.php`.

### 9.4. Who did what

- **Abubakar**
  - Implemented cart pages (`cart_add.php`, `cart.php`, `cart_remove.php`).
  - Implemented checkout flow (`checkout.php`, `create_checkout_session.php`, `checkout_success.php`, `checkout_cancel.php`, `stripe_config.php`).
  - Added `orders` table, `my_orders.php`, and `admin_orders.php`.
  - Set up Composer + `stripe/stripe-php` and wrote this documentation.

- **Belle**
  - Built the original VinnyTen Racing front-end (home, shop, services, etc.).
  - Helped design UX and content.
  - Helped test the cart and checkout flow.

This section explains **exactly** what changed so that the whole team can run, test, and present the new cart + Stripe features.