## 9. Cart + Stripe Checkout (Abubakar’s part)

The current version of this project includes a full **shopping cart** and **Stripe Checkout** integration. This was implemented to simulate a real payment flow without having to manually handle credit card forms.

### 9.1. What I implemented

- Session-based **cart**:
  - `cart_add.php` – adds items to `$_SESSION['cart']`.
  - `cart.php` – shows items, quantities, line totals, and subtotal.
  - `cart_remove.php` – removes a line item from the cart.
- **Checkout** flow:
  - `checkout.php` – review order summary before payment.
  - `create_checkout_session.php` – creates a Stripe Checkout Session based on the cart contents and creates a row in the `orders` table.
  - `checkout_success.php` – handles successful payment, marks the order as paid, clears the cart.
  - `checkout_cancel.php` – handles cancelled checkout and (optionally) marks order as cancelled.
- **Stripe integration**:
  - `composer.json` / `composer.lock` / `vendor/` – installed `stripe/stripe-php`.
  - `stripe_config.php` – sets Stripe secret/public keys and initializes the SDK.
- **Orders + admin**:
  - `orders` table in `sql/vinnyten_racing_schema.sql` with sample data.
  - `admin_orders.php` – admin can view orders and update their status.
  - `my_orders.php` – logged-in users can see their own orders.

These pieces work together to simulate a production-style checkout flow with real Stripe test payments.

### 9.2. How the cart data flows

1. **Add to cart**  
   On `product.php`, the “Add to Cart” form posts to `cart_add.php`:

   ```php
   <form method="post" action="cart_add.php" class="add-to-cart-form">
       <?php csrf_field(); ?>
       <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
       <label>
           Qty:
           <input type="number" name="qty" value="1" min="1" max="<?php echo max(1, (int)$product['stock_qty']); ?>">
       </label>
       <button type="submit" class="btn-primary">Add to Cart</button>
   </form>
   ```

   `cart_add.php` stores this in the session:

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

2. **View cart**  
   `cart.php` reads `$_SESSION['cart']`, looks up product details from the `products` table, and calculates totals.

3. **Checkout**  
   `checkout.php` again reads `$_SESSION['cart']` and shows a summary. When the user clicks “Pay Securely with Card”, it posts to `create_checkout_session.php`.

4. **Create Stripe session + order**  
   `create_checkout_session.php`:
   - Validates the cart.
   - Inserts a row into the `orders` table with `status='pending'`.
   - Creates a Stripe Checkout Session with line items and total.
   - Saves Stripe session id in the order.
   - Redirects the user to Stripe’s hosted checkout page.

5. **Success / Cancel**  
   - On success, Stripe redirects to `checkout_success.php?session_id=...&order=...`.  
     This page:
       - Validates the session with Stripe.
       - Sets order status to `paid`.
       - Clears `$_SESSION['cart']`.
   - On cancel, user is sent to `checkout_cancel.php`, which optionally marks the order as `cancelled` and links back to `cart.php`.

### 9.3. How to set up Stripe (teammates)

**Important**: These keys are in **test mode** only.

1. Install PHP dependencies (only needed once):

   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/VinnyTenRacing
   composer install
   ```

2. Verify `stripe_config.php` has working test keys:

   ```php
   // filepath: /Applications/XAMPP/xamppfiles/htdocs/webdev2/lab2/stripe_config.php
   // ...existing code...
   $stripeSecretKey = 'sk_test_...'; // keep as test key
   $stripePublicKey = 'pk_test_...'; // keep as test key

   \Stripe\Stripe::setApiKey($stripeSecretKey);
   // ...existing code...
   ```

3. Start Apache + MySQL from XAMPP.

4. In the browser, go to:

   - `http://localhost/VinnyTenRacing/index.php` – home
   - `http://localhost/VinnyTenRacing/shop.php` – shop

5. Test a full flow:

   - Register a user (`register.php`) and log in (`login.php`).
   - Go to `shop.php` → click a product → `product.php`.
   - Add to cart.
   - Go to `cart.php` → click checkout → `checkout.php`.
   - Click **Pay Securely with Card**.
   - You’ll be redirected to Stripe hosted checkout (test mode). Use a test card like:
     - Card: `4242 4242 4242 4242`
     - Any future expiration date, any CVC, any ZIP.
   - After payment, Stripe sends you back to `checkout_success.php` and your cart is cleared.

### 9.4. Who did what (team note)

- **Abubakar**:
  - Implemented `cart_add.php`, `cart.php`, `cart_remove.php`.
  - Implemented checkout flow: `checkout.php`, `create_checkout_session.php`, `checkout_success.php`, `checkout_cancel.php`, `stripe_config.php`.
  - Added `orders` table and `my_orders.php`, `admin_orders.php`.
  - Configured Composer + `stripe/stripe-php` and documented setup here.
  - Integrated CSRF helpers in `config.php` and wired forms (cart/checkout) to use them.

- **Belle**:
  - Built the original VinnyTen Racing layout (home, shop, services, etc.) in HTML/CSS/JS.
  - Helped design the overall user experience and content.
  - Coordinated design decisions and testing of the cart/checkout UX.

This section is mainly to guide teammates (and professor) through the Stripe/cart setup without running into errors.