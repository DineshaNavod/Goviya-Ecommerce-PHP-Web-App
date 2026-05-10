# 🌿 Goviya.lk — Fresh Farm Produce E-Commerce Website

> ICT2152 E-Commerce Implementation, Management and Security — Mini Project  
> Faculty of Technology, University of Ruhuna | 2026

An online fresh food & vegetable marketplace built with PHP, MySQL, and Bootstrap. Customers can browse products, manage a cart, and place orders. Admins manage products, orders, and users through a dedicated dashboard.

---

## 🚀 Tech Stack

| Layer    | Technology                             |
| -------- | -------------------------------------- |
| Frontend | HTML5, CSS3, Bootstrap 5.3, JavaScript |
| Backend  | PHP 8+ (PDO)                           |
| Database | MySQL                                  |
| Server   | Apache (XAMPP)                         |

---

## 📁 Project Structure

```
goviya/
├── index.php                  # Homepage
├── goviya_db.sql              # Database schema + seed data
├── fix_admin.php              # One-time admin password fix (delete after use)
├── .htaccess                  # Security headers & access rules
│
├── includes/
│   ├── config.php             # DB credentials & SITE_URL
│   ├── auth.php               # Sessions, CSRF, login helpers
│   ├── header.php             # Navbar, cart badge
│   └── footer.php             # Footer + JS includes
│
├── pages/
│   ├── products.php           # Shop — search, filter, categories
│   ├── product.php            # Single product detail
│   ├── cart.php               # Cart view
│   ├── cart_action.php        # AJAX cart handler
│   ├── checkout.php           # Checkout + payment
│   ├── order_confirm.php      # Order confirmation
│   ├── orders.php             # Customer order history
│   ├── login.php              # Login
│   ├── register.php           # Registration
│   ├── profile.php            # Profile + password change
│   └── forgot_password.php    # Password reset
│
├── admin/
│   ├── index.php              # Dashboard — stats & alerts
│   ├── products.php           # Add / Edit / Delete products + image upload
│   ├── categories.php         # Manage categories
│   ├── orders.php             # View & update order status
│   └── users.php              # Manage users & roles
│
└── assets/
    ├── css/style.css          # Main stylesheet (green & earthy theme)
    ├── js/main.js             # Cart AJAX, toasts, UI interactions
    └── images/products/       # Uploaded product images
```

---

## 🗄️ Database Tables

| Table         | Description                        |
| ------------- | ---------------------------------- |
| `users`       | Customer & admin accounts          |
| `categories`  | Product categories                 |
| `products`    | Products with stock, price, images |
| `orders`      | Customer orders                    |
| `order_items` | Line items per order               |
| `cart`        | Persistent server-side cart        |

---

## ⚙️ Setup (XAMPP)

```bash
# 1. Place folder in htdocs
C:\xampp\htdocs\goviya\

# 2. Import database
# phpMyAdmin → New DB → goviya_db → Import → goviya_db.sql

# 3. Edit credentials
# includes/config.php → set DB_USER, DB_PASS, SITE_URL

# 4. Fix admin password (visit once, then delete)
http://localhost/goviya/fix_admin.php

# 5. Open site
http://localhost/goviya/
```

**Admin login**

```
Email:    admin@goviya.lk
Password: Admin@1234
```

---

## ✅ Features

- User registration, login, logout, password reset
- Product listing with search, category & price filters
- AJAX shopping cart (add, update, remove)
- Checkout with Card, Cash on Delivery, Bank Transfer
- Order history & confirmation
- Admin dashboard — products (with image upload), orders, users, categories
- Responsive design — mobile friendly

## 🔒 Security

- CSRF tokens on all forms
- BCrypt password hashing
- PDO prepared statements (SQL injection safe)
- Input sanitisation & XSS prevention
- Role-based access control (admin / customer)
- Image upload validation (type & size)
- `.htaccess` security headers

---

_University of Ruhuna — Faculty of Technology — 2026_
