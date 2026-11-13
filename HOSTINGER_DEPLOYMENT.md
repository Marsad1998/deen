# DEEN Electronics — Hostinger Shared Hosting Deployment Guide

This guide details how to deploy the **DEEN Electronics** Laravel application to **Hostinger Shared Hosting** (or cPanel/LiteSpeed environments) inside the `public_html` directory, while preserving 100% compatibility with local `php artisan serve`.

---

## 1. Architecture Overview: How `public_html` Works

On Hostinger Shared Hosting, the domain's document root is `public_html`. Typically, Laravel applications keep their front controller inside `public/index.php`.

To ensure the application works out-of-the-box inside `public_html` without exposing sensitive files or requiring visitors to type `/public` in their browser, this repository includes:

1. **Root `.htaccess` Bridge (`/.htaccess`)**:
   - Automatically and silently rewrites all incoming web requests into the `/public/` directory.
   - **Blocks direct HTTP access** to sensitive files and folders (`.env`, `artisan`, `composer.json`, `app/`, `bootstrap/`, `storage/`, etc.) with a `403 Forbidden` response.
2. **Root `index.php` Fallback (`/index.php`)**:
   - For hosting environments where Apache/LiteSpeed evaluates the root index file before applying rewrite rules, this file seamlessly forwards execution to `public/index.php`.
3. **Local Development Compatibility (`/server.php`)**:
   - Preserved for local development so `php artisan serve` continues to run smoothly on `http://127.0.0.1:8000`.

---

## 2. Step-by-Step Deployment on Hostinger

### Step 1: Deploy Files into `public_html`

Log in to Hostinger hPanel and use either **Git** or **SSH Terminal**:

#### Option A: Via Hostinger Git Integration (Recommended)
1. Go to **hPanel** → **Advanced** → **Git**.
2. Repository URL: `https://github.com/Marsad1998/deen.git`
3. Branch: `main`
4. Directory: `public_html`
5. Click **Create & Deploy**.

#### Option B: Via SSH Terminal
Connect to Hostinger via SSH:
```bash
cd ~/public_html
git clone https://github.com/Marsad1998/deen.git .
```

---

### Step 2: Configure Environment (`.env`)

1. In Hostinger **File Manager** (or via SSH inside `public_html`), copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
2. Open `.env` and set your live domain and database details:
   ```ini
   APP_NAME="DEEN Electronics"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=u123456789_deen
   DB_USERNAME=u123456789_deen_user
   DB_PASSWORD=YourStrongDatabasePassword123!
   ```

---

### Step 3: Install Dependencies & Generate Application Key

Via Hostinger SSH inside `public_html`:
```bash
# 1. Install production Composer packages
composer install --no-dev --optimize-autoloader

# 2. Generate application encryption key
php artisan key:generate

# 3. Link public storage for uploads
php artisan storage:link
```

---

### Step 4: Run Database Migrations & Seeders

Run the combined migration and seeder to populate all categories, demo showcase products, default roles, payment methods, and initial settings:
```bash
php artisan migrate --force --seed
```

> **Default Super Admin Credentials**:
> - **URL**: `https://yourdomain.com/admin/login`
> - **Username**: `admin`
> - **Password**: `AdminPassword123!`
> *(Ensure you update this password after initial login via Admin Profile settings)*

---

### Step 5: Optimize for Production Performance

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Local Development (Unchanged)

When working locally, you do **not** need to change anything:
```bash
# Run local dev server:
php artisan serve --port=8000

# Run automated tests:
./vendor/bin/phpunit
```
The application will continue serving seamlessly at `http://127.0.0.1:8000`.
