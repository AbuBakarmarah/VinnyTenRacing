# Vinny Ten Racing – PHP/MySQL Web App

A small, production-style web application for Vinny Ten Racing built for **MAC272 – Web Development II**.

## 1. Features

**Public**

- Home page with hero slideshow, latest YouTube video, featured specials and packages.
- Shop listing (`shop.php`) with:
  - Sorting (newest, price asc/desc).
  - Product cards (image, price, stock, description).
- Product detail (`product.php`):
  - Full description, price, stock, related packages.
  - Add to Cart form.
- Shopping cart (`cart.php`) stored in PHP session.
- Performance Services (`services.php`):
  - Dynamic services list from DB.
  - Service booking form (requires login).
- Contact / feedback (`contact.php`) stored in `contact_messages` table.
- Global search (`search.php`) across products + services.

**User account**

- Registration (`register.php`) with validation and password hashing.
- Login / Logout (`login.php`, `logout.php`) using sessions.
- Account page (`account.php`) with profile update + optional password change.
- My bookings (`my_bookings.php`) – shows service bookings for logged-in user.

**Admin**

Admin pages are protected by `$_SESSION['role'] === 'admin'`:

- `admin_users.php` – view users, change role, delete (not yourself).
- `admin_products.php` – full CRUD for products + safe image URL validation.
- `admin_services.php` – manage services (optional, some logic lives in `services.php`).
- `admin_orders.php` – view orders and update status (pending / processing / completed / cancelled).
- `admin_messages.php` – view and delete contact/feedback messages.

---

## 2. Tech Stack

- **Backend**: PHP 8 (tested with XAMPP)
- **Database**: MySQL (InnoDB, utf8mb4)
- **Frontend**: HTML5, CSS (`global.css`, `home.css`), vanilla JS (`slideShow.js`, `youtubeAPICall.js`)
- **Auth**: PHP sessions + `password_hash()` / `password_verify()`

---

## 3. Folder Structure

```text
vinnyten_racing/
  assets/                # Images & static assets
  sql/
    vinnyten_racing_schema.sql  # DB schema + sample data (exported from Workbench)

  account.php
  admin_messages.php
  admin_orders.php
  admin_products.php
  admin_services.php
  admin_users.php
  cart.php
  cart_add.php
  cart_remove.php
  config.php
  contact.php
  footer.php
  global.css
  header.php
  home.css
  index.php
  login.php
  logout.php
  make_hash.php          # helper script used once to generate admin password hash
  my_bookings.php
  product.php
  register.php
  search.php
  services.php
  shop.php
  slideShow.js
  youtubeAPICall.js
```

---

## 4. Getting Started (Local XAMPP)

### 4.1. Requirements

- XAMPP (Apache + MySQL + PHP) on macOS/Windows
- MySQL Workbench or `mysql` CLI

### 4.2. Clone the repo

```bash
cd /Applications/XAMPP/xamppfiles/htdocs
git clone https://github.com/your-username/VinnyTenLite.git vinnyten_racing
cd vinnyten_racing
```

### 4.3. Create database and import schema

1. Start Apache and MySQL from XAMPP.
2. Create DB:

```sql
CREATE DATABASE vinnyten_racing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import:

```bash
mysql -u root vinnyten_racing < sql/vinnyten_racing_schema.sql
```

> If your MySQL `root` has a password, update `config.php` accordingly.

### 4.4. Configure `config.php`

Check that `config.php` uses the correct DB name and credentials (see file).

### 4.5. Visit the site

In your browser:

- `http://localhost/vinnyten_racing/index.php`

Logins (from seed data, adjust to your actual DB):

- **Admin**: `admin` / `Admin123!` (password hashed in DB).
- **User**: see `users` table in DB.

---

## 5. Code Overview

### 5.1. Layout

- `header.php` – shared header, nav, search, login/logout, opens `<main>`.
- `footer.php` – shared footer, closes `</main>` and wrapper.
- `global.css` – global layout, shop, product, admin styles.
- `home.css` – home page specific layout (hero, featured sections).

### 5.2. Core PHP Pages

- `index.php` – home page: slideshow, latest video, featured specials, featured packages (from `products`).
- `shop.php` – product catalog, sorting, product cards link to `product.php`.
- `product.php` – single product page, add-to-cart, related packages.
- `cart_add.php` / `cart_remove.php` / `cart.php` – session-based cart logic.
- `services.php` – list of services from DB + booking form.
- `my_bookings.php` – bookings list for logged-in user.
- `contact.php` – contact/feedback form saved into `contact_messages`.

### 5.3. Auth

- `register.php` – registration with:
  - required fields,
  - email validation,
  - strong password rule (min 8 chars, upper, lower, number),
  - `password_hash` and insert into `users`.
- `login.php` – login with `password_verify`, sets:
  - `$_SESSION['user_id']`
  - `$_SESSION['username']`
  - `$_SESSION['role']`
- `logout.php` – `session_unset()`, `session_destroy()` and redirect.

### 5.4. Admin Pages

All admin pages start with:

```php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}
```

and then implement CRUD for their respective entities using prepared statements or safe casting + `htmlspecialchars` when echoing.

---

## 6. Database Design (High Level)

Main tables (see ERD PNG in `assets` and `sql/vinnyten_racing_schema.sql` for details):

- `users(user_id, first_name, last_name, email, username, password_hash, role, created_at)`
- `products(product_id, product_name, description, price, stock_qty, image_url, created_at)`
- `orders(order_id, user_id, total_amount, status, created_at)`
- `order_items(order_item_id, order_id, product_id, quantity, price_each)`
- `services(id, name, description, price, duration)`
- `bookings(id, user_id, service_id, booking_date, notes, created_at)`
- `contact_messages(message_id, user_id, name, email, subject, message, created_at)`

---

## 7. Notes for Team Members

- Use `config.php` for DB credentials; **do not hard‑code** elsewhere.
- Always escape output with `htmlspecialchars(...)` when printing user data.
- When adding new pages:
  - include `config.php` (for DB + session).
  - include `header.php` and `footer.php` for consistent layout.
- For new DB changes, update `sql/vinnyten_racing_schema.sql` and commit so others can import.

---

## 8. Future Improvements (Nice‑to‑Have)

- Implement real order creation on checkout (insert into `orders` + `order_items`).
- Add CSRF tokens to forms.
- Add pagination to `shop.php`.
- Move config values (DB, API keys) into an `.env` file or separate config.
