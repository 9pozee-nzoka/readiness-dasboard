# Quick Setup Guide for Truehost cPanel

## 🚀 Fast Deployment Steps

### 1️⃣ Prepare Files Locally

```bash
# Navigate to project
cd /home/dex/Desktop/projects/Readiness-Dashboard

# Build frontend assets
npm run build

# Create deployment package
tar -czf stmary-dashboard.tar.gz \
  --exclude=node_modules \
  --exclude=.git \
  --exclude=.env \
  --exclude=storage/logs/* \
  --exclude=storage/framework/cache/* \
  --exclude=storage/framework/sessions/* \
  --exclude=storage/framework/views/* \
  --exclude=tests \
  .
```

Your `stmary-dashboard.tar.gz` is ready to upload!

---

### 2️⃣ cPanel Database Setup

**Access:** https://your-truehost-cpanel:2083

1. **MySQL Databases** → Create Database
   - Name: `stmaryim_readiness`
   - Click "Create Database"

2. **MySQL Databases** → Create User
   - Username: `stmaryim_user`
   - Password: Generate strong password (save it!)
   - Click "Create User"

3. **MySQL Databases** → Add User to Database
   - User: `stmaryim_user`
   - Database: `stmaryim_readiness`
   - Privileges: **ALL PRIVILEGES**
   - Click "Make Changes"

📝 **Save these credentials:**
```
DB_HOST=localhost
DB_DATABASE=stmaryim_readiness
DB_USERNAME=stmaryim_user
DB_PASSWORD=<your-password-here>
```

---

### 3️⃣ Upload Files to Truehost

1. **File Manager** → Navigate to home directory (e.g., `/home/stmaryim/`)

2. **Upload** `stmary-dashboard.tar.gz`

3. **Extract** the file:
   - Right-click → Extract
   - Extract to: `/home/stmaryim/laravel-app/`

4. **Your folder structure should be:**
   ```
   /home/stmaryim/
   ├── laravel-app/          ← Laravel application
   │   ├── app/
   │   ├── public/
   │   ├── vendor/
   │   └── ...
   └── public_html/          ← This needs to point to laravel-app/public
   ```

---

### 4️⃣ Point Domain to Laravel

**Option A: Rename and Symlink (Recommended)**

1. **File Manager** → Rename `public_html` to `public_html_backup`

2. **Terminal** (in cPanel) → Run:
   ```bash
   ln -s /home/stmaryim/laravel-app/public /home/stmaryim/public_html
   ```

**Option B: Copy Files (If symlink fails)**

1. Delete all files in `public_html`
2. Copy all files from `/home/stmaryim/laravel-app/public/` to `/home/stmaryim/public_html/`
3. Edit `/home/stmaryim/public_html/index.php`:
   ```php
   // Change this line:
   require __DIR__.'/../vendor/autoload.php';
   // To:
   require __DIR__.'/../laravel-app/vendor/autoload.php';
   
   // And this line:
   $app = require_once __DIR__.'/../bootstrap/app.php';
   // To:
   $app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';
   ```

---

### 5️⃣ Configure Environment

1. **Navigate to:** `/home/stmaryim/laravel-app/`

2. **Copy `.env.production` to `.env`:**
   ```bash
   cp .env.production .env
   ```

3. **Edit `.env` file** with your credentials:

```env
APP_NAME="St. Mary IMC Dashboard"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://stmaryimc.co.ke

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=stmaryim_readiness
DB_USERNAME=stmaryim_user
DB_PASSWORD=your_database_password_from_step2

SESSION_DRIVER=database
SESSION_DOMAIN=.stmaryimc.co.ke
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=mail.stmaryimc.co.ke
MAIL_PORT=587
MAIL_USERNAME=noreply@stmaryimc.co.ke
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@stmaryimc.co.ke
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=admin@stmaryimc.co.ke
ADMIN_PASSWORD=YourSecurePassword123!
ADMIN_NAME="System Administrator"
```

---

### 6️⃣ Set PHP Version & Extensions

1. **Select PHP Version** (or **MultiPHP Manager**)
   - Select: **PHP 8.2** or **PHP 8.3**
   - Enable these extensions:
     ```
     ✅ bcmath      ✅ mbstring    ✅ pdo_mysql
     ✅ ctype       ✅ openssl     ✅ tokenizer
     ✅ curl        ✅ pcre        ✅ xml
     ✅ dom         ✅ pdo         ✅ zip
     ✅ fileinfo    
     ✅ json
     ```

---

### 7️⃣ Run Installation Commands

**In cPanel Terminal:**

```bash
cd /home/stmaryim/laravel-app

# Generate application key
php artisan key:generate --force

# Set permissions
chmod -R 755 storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;

# Run database migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Link storage
php artisan storage:link

# Cache for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### 8️⃣ Set Up Email Account

1. **Email Accounts** → Create Email
   - Email: `noreply@stmaryimc.co.ke`
   - Password: Strong password
   - Mailbox Quota: 250 MB

2. **Note SMTP Settings:**
   ```
   SMTP Host: mail.stmaryimc.co.ke
   Port: 587 (TLS)
   Username: noreply@stmaryimc.co.ke
   Password: <password-you-set>
   ```

3. **Update `.env`** with these SMTP settings

---

### 9️⃣ Configure Cron Jobs

**Cron Jobs** section:

**Every Minute - Laravel Scheduler:**
```
* * * * * cd /home/stmaryim/laravel-app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Every Minute - Queue Worker (for emails):**
```
* * * * * cd /home/stmaryim/laravel-app && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

> **Note:** Path to PHP might be `/usr/bin/php` or `/usr/local/bin/php` - check with `which php` in terminal.

---

### 🔟 Enable SSL/HTTPS

1. **SSL/TLS Status**
   - Find your domain: `stmaryimc.co.ke`
   - Click "Issue" or "Install" next to AutoSSL/Let's Encrypt
   - Wait 5-10 minutes for certificate to be issued

2. **Verify HTTPS:**
   - Visit: https://stmaryimc.co.ke
   - Should automatically redirect from HTTP to HTTPS

---

## ✅ Verification Checklist

After deployment, test:

- [ ] Website loads: https://stmaryimc.co.ke
- [ ] HTTP redirects to HTTPS automatically
- [ ] Login page accessible: https://stmaryimc.co.ke/login
- [ ] Can login with admin credentials from `.env`
- [ ] Dashboard loads correctly
- [ ] Can create an event
- [ ] Email notifications work (check spam folder)
- [ ] File uploads work
- [ ] No errors in browser console (F12)

---

## 🔍 Troubleshooting

### "500 Internal Server Error"

```bash
# Check logs
cat /home/stmaryim/laravel-app/storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check permissions
chmod -R 755 storage bootstrap/cache
```

### "Database Connection Error"

- Verify database credentials in `.env`
- Check database exists in cPanel → Databases
- Ensure user has ALL PRIVILEGES

### "Blank Page / No Errors"

```bash
# Enable debug mode temporarily
# Edit .env:
APP_DEBUG=true

# Check the error, then disable:
APP_DEBUG=false
```

### "Emails Not Sending"

```bash
# Test queue
php artisan queue:work --once

# Check mail configuration
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });

# Verify cron job is running
```

### "CSS/JS Not Loading"

- Ensure `npm run build` was run before upload
- Check `APP_URL` in `.env` matches your domain
- Clear browser cache (Ctrl+Shift+R)

---

## 📞 Support Contacts

**Truehost Support:**
- Email: support@truehost.co.ke
- Phone: +254 (0) 20 528 0000
- Live Chat: https://truehost.co.ke

**Check Logs:**
- Application: `/home/stmaryim/laravel-app/storage/logs/laravel.log`
- cPanel: **Metrics** → **Errors**

---

## 🎉 Success!

Your St. Mary IMC Readiness Dashboard should now be live at:

**🌐 https://stmaryimc.co.ke**

**Default Login:**
- Email: From `ADMIN_EMAIL` in `.env`
- Password: From `ADMIN_PASSWORD` in `.env`

**Remember to:**
1. Change default admin password after first login
2. Create additional user accounts
3. Set up regular backups (cPanel → Backup Wizard)
4. Monitor disk space usage
5. Keep application updated

---

**Need help?** Review the full `DEPLOYMENT.md` guide for detailed information.
