# Vinny Ten Racing – PHP/MySQL Web App

A small, production-style web application for Vinny Ten Racing built for **MAC272 – Web Development II**.

Repo: https://github.com/AbuBakarmarah/VinnyTenRacing

---

## Quick Setup for Team Members

1. Clone into XAMPP `htdocs`:

   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs
   git clone https://github.com/AbuBakarmarah/VinnyTenRacing.git
   cd VinnyTenRacing
   ```

2. Create database and import schema:

   ```sql
   CREATE DATABASE vinnyten_racing
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```

   ```bash
   mysql -u root vinnyten_racing < sql/vinnyten_racing_schema.sql
   ```

   > If MySQL `root` has a password, use:
   >
   > ```bash
   > mysql -u root -p vinnyten_racing < sql/vinnyten_racing_schema.sql
   > ```
   >
   > Then update `config.php` with that password.

3. Check `config.php` DB settings:

   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";                 // or your root password
   $db   = "vinnyten_racing";
   ```

4. Start Apache and MySQL in XAMPP, then open:

   - `http://localhost/VinnyTenRacing/index.php`

5. Admin login for testing:

   - Username: `admin`
   - Password: `Admin123!`

---

## 1. Features

### Public

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

### User account

- Registration (`register.php`) with validation and password hashing.
- Login / Logout (`login.php`, `logout.php`) using sessions.
- Account page (`account.php`) with profile update + optional password change.
- My bookings (`my_bookings.php`) – shows service bookings for logged-in user.

### Admin

Admin pages are protected by `$_SESSION['role'] === 'admin'`:

- `admin_users.php` – view users, change role, delete (not yourself).
- `admin_products.php` – full CRUD for products + safe image URL validation.
- `admin_services.php` – manage services.
- `admin_orders.php` – view orders and update status.
- `admin_messages.php` – view and delete contact/feedback messages.

---

## 2. Tech Stack

- **Backend**: PHP 8 (XAMPP)
- **Database**: MySQL / MariaDB (InnoDB, utf8mb4)
- **Frontend**: HTML5, CSS (`global.css`, `home.css`), JS (`slideShow.js`, `youtubeAPICall.js`)
- **Auth**: PHP sessions + `password_hash()` / `password_verify()`

---

## 3. Project Structure

```text
VinnyTenRacing/
  assets/                        # Images & ER diagram
  sql/
    vinnyten_racing_schema.sql   # DB schema + sample data (MySQL dump)

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
  make_hash.php          # helper script to generate password hashes
  my_bookings.php
  product.php
  README.md
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
git clone https://github.com/AbuBakarmarah/VinnyTenRacing.git
cd VinnyTenRacing
```

### 4.3. Create database and import schema

1. Start **Apache** and **MySQL** in XAMPP.
2. In MySQL (Workbench or CLI), run:

   ```sql
   CREATE DATABASE vinnyten_racing
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;
   ```

3. Import the schema + sample data:

   ```bash
   mysql -u root vinnyten_racing < sql/vinnyten_racing_schema.sql
   ```

   > If your MySQL `root` has a password, use:  
   > `mysql -u root -p vinnyten_racing < sql/vinnyten_racing_schema.sql`  
   > and update `config.php` with that password.

### 4.4. Configure `config.php`

Check the DB settings in `config.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";                 // or your root password
$db   = "vinnyten_racing";
```

If you changed DB name or password, update here.

### 4.5. Run the site

Open in the browser:

- `http://localhost/VinnyTenRacing/index.php`

Default logins (from the SQL dump):

- **Admin**
  - Username: `admin`
  - Password: `Admin123!`
- **User accounts**
  - See the `users` table in the database for existing test users.

---

## 5. Code Overview

### 5.1. Layout

- `header.php` – shared header, navigation, opens `<main>`.
- `footer.php` – shared footer, closes `</main>`.
- `global.css` – global layout, shop, product, admin styles.
- `home.css` – home page–specific styles.

### 5.2. Core PHP Pages

- `index.php` – home page: slideshow, latest YouTube video, featured specials, packages.
- `shop.php` – product catalog page with sorting.
- `product.php` – product detail page with Add to Cart.
- `cart_add.php`, `cart_remove.php`, `cart.php` – session-based cart.
- `services.php` – services listing and booking form.
- `my_bookings.php` – list of bookings for logged-in user.
- `contact.php` – contact/feedback form → `contact_messages` table.

### 5.3. Auth

- `register.php` – new user registration, validation, `password_hash`.
- `login.php` – login with `password_verify`, sets `$_SESSION`.
- `logout.php` – clear session and redirect.
- `account.php` – update profile and password.

### 5.4. Admin Pages

All admin pages require:

```php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied.";
    exit;
}
```

Then they provide CRUD for:

- Users (`admin_users.php`)
- Products (`admin_products.php`)
- Services (`admin_services.php`)
- Orders (`admin_orders.php`)
- Messages (`admin_messages.php`)

---

## 6. Database Design (High Level)

See the ER diagram in `assets/Database ER diagram (crow's foot).png` and the full DDL in `sql/vinnyten_racing_schema.sql`.

Main tables:

- `users(user_id, first_name, last_name, email, username, password_hash, phone_number, role, created_at)`
- `categories(category_id, category_name)`
- `products(product_id, category_id, product_name, description, price, stock_qty, image_url, created_at)`
- `orders(order_id, user_id, total_amount, status, created_at)`
- `service_bookings(booking_id, user_id, service_id, preferred_date, status, notes, created_at)`
- `services(id, name, description, price, duration, created_at)`
- `contact_messages(message_id, user_id, name, email, subject, message, created_at)`

Foreign keys enforce relationships between users, products, services, and bookings.

---

## 7. Workflow for Team Members

1. **Pull latest changes**:

   ```bash
   git pull origin main
   ```

2. **Make changes** in a new branch:

   ```bash
   git checkout -b feature/some-change
   # edit files in VS Code
   git add .
   git commit -m "Describe your change"
   git push -u origin feature/some-change
   ```

3. Open a **Pull Request** on GitHub for review.

4. If DB structure changes:
   - Update the DB locally.
   - Export updated schema/data to `sql/vinnyten_racing_schema.sql`.
   - Commit that file with a clear message:
     - `"Update DB schema: add X table"`.

---

## 8. Troubleshooting

- **Blank page / 500 error**  
  Check Apache/PHP error log (XAMPP) and make sure `config.php` has correct DB credentials.

- **Cannot connect to database**  
  Confirm:
  - MySQL is running in XAMPP.
  - DB `vinnyten_racing` exists.
  - `sql/vinnyten_racing_schema.sql` was imported successfully.

- **Login not working**  
  Use the default admin from the dump (`admin` / `Admin123!`). If changed, reset password directly in DB or via `make_hash.php`.