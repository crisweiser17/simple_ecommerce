# R2 Research Labs - Peptide Catalog & E-commerce

A dynamic e-commerce application built with PHP, SQLite, Alpine.js, and Tailwind CSS.

## Features
- **Product Catalog:** Filterable list of peptides with images and prices.
- **Product Details:** Detailed view with description tabs and PDF report download.
- **Shopping Cart:** Fast, client-side cart using Alpine.js.
- **Checkout Flow:** Magic Link authentication and PDF Order generation.
- **Admin Panel:** Manage products (CRUD) and view customer orders.

## Installation

1. **Install Dependencies:**
   ```bash
   composer install
   ```

2. **Initialize Database:**
   (Already done, but if needed to reset)
   ```bash
   php setup_db.php
   ```

## Running the Application

Start the built-in PHP server:
```bash
php -S localhost:8000 index.php
```

Visit `http://localhost:8000` in your browser.

## Login & Admin Access

### Admin Login
- **Email:** `admin@r2.com`
- **Password:** (Uses Magic Link Token)

### How to Login (Magic Link)
Since this is a local environment, emails are not sent. Instead, the login token is logged to a file.

1. Go to `/login` (or `/admin` for admin access).
2. Enter your email (e.g., `admin@r2.com` or any customer email).
3. Check the file `token.log` in the project root.
4. Copy the 6-digit code and enter it on the website.

## Project Structure
- `src/`: Backend logic (Auth, DB, PDF).
- `templates/`: HTML/PHP Views (Layout, Admin, Components).
- `data/`: SQLite database file.
- `public/`: Static assets (images, css).
