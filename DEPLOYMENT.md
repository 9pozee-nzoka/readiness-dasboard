# Deployment Guide for Truehost (stmaryimc.co.ke)

This guide will help you deploy the St. Mary IMC Readiness Dashboard to Truehost shared hosting.

## Prerequisites

1. **Truehost Account** with cPanel access
2. **Domain**: stmaryimc.co.ke (already pointed to Truehost)
3. **MySQL Database** created in cPanel
4. **Email Account** (noreply@stmaryimc.co.ke recommended)
5. **SSH Access** (if available) or **File Manager/FTP**

---

## Step 1: Prepare Your Truehost Environment

### 1.1 Create MySQL Database
1. Log in to cPanel (https://your-server.truehost.cloud:2083)
2. Go to **MySQL Databases**
3. Create a new database: `stmaryim_readiness`
4. Create a database user: `stmaryim_user`
5. Set a strong password (save it!)
6. Add user to database with **ALL PRIVILEGES**
7. Note down: Database name, username, password, and host (usually `localhost`)

### 1.2 Set PHP Version
1. In cPanel, go to **Select PHP Version** or **MultiPHP Manager**
2. Select PHP **8.2** or **8.3** (minimum 8.2 required)
3. Enable required extensions:
   - ✅ bcmath
   - ✅ ctype
   - ✅ curl
   - ✅ dom
   - ✅ fileinfo
   - ✅ json
   - ✅ mbstring
   - ✅ openssl
   - ✅ pcre
   - ✅ pdo
   - ✅ pdo_mysql
   - ✅ tokenizer
   - ✅ xml
   - ✅ zip

### 1.3 Create Email Account
1. Go to **Email Accounts** in cPanel
2. Create: `noreply@stmaryimc.co.ke`
3. Set a strong password
4. Note the SMTP settings (usually `mail.stmaryimc.co.ke`, port 587)

---

## Step 2: Upload Files to Truehost

### Option A: Using File Manager (Recommended for Shared Hosting)

1. **Compress your project locally:**
   ```bash
   cd /home/dex/Desktop/projects/Readiness-Dashboard
   
   # Build frontend assets
   npm run build
   
   # Create deployment package (exclude unnecessary files)
   tar -czf readiness-dashboard.tar.gz \
     --exclude=node_modules \
     --exclude=.git \
     --exclude=.env \
     --exclude=storage/logs/* \
     --exclude=storage/framework/cache/* \
     --exclude=storage/framework/sessions/* \
     --exclude=storage/framework/views/* \
     --exclude=tests \
     --exclude=.github \
     .
   ```

2. **Upload via cPanel File Manager:**
   - Go to cPanel → **File Manager**
   - Navigate to your **home directory** (NOT public_html yet)
   - Upload `readiness-dashboard.tar.gz`
   - Extract it to a folder called `readiness-app`
   - Your structure should be: `/home/username/readiness-app/`

3. **Link public folder to public_html:**
   ```bash
   # Delete or rename existing public_html
   mv public_html public_html_backup
   
   # Create symlink (or copy) public folder to public_html
   ln -s /home/username/readiness-app/public /home/username/public_html
   
   # Alternative: Copy instead of symlink if symlink doesn't work
   cp -r /home/username/readiness-app/public /home/username/public_html
   ```

### Option B: Using FTP/SFTP

1. Use FileZilla or similar FTP client
2. Connect to your Truehost server
3. Upload files to `/home/username/readiness-app/`
4. Follow step 3 above to link public folder

---

## Step 3: Configure Environment Variables

1. **Copy the production environment file:**
   ```bash
   cd /home/username/readiness-app
   cp .env.production .env
   ```

2. **Edit `.env` file with your actual values:**
   ```env
   APP_NAME="St. Mary IMC Readiness Dashboard"
   APP_ENV=production
   APP_KEY=    # Will be generated in next step
   APP_DEBUG=false
   APP_URL=https://stmaryimc.co.ke

   # Database (from Step 1.1)
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=stmaryim_readiness
   DB_USERNAME=stmaryim_user
   DB_PASSWORD=your_database_password_here

   # Session
   SESSION_DRIVER=database
   SESSION_DOMAIN=.stmaryimc.co.ke
   SESSION_SECURE_COOKIE=true

   # Mail (from Step 1.3)
   MAIL_MAILER=smtp
   MAIL_HOST=mail.stmaryimc.co.ke
   MAIL_PORT=587
   MAIL_USERNAME=noreply@stmaryimc.co.ke
   MAIL_PASSWORD=your_email_password_here
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@stmaryimc.co.ke
   MAIL_FROM_NAME="${APP_NAME}"

   # Admin Account
   ADMIN_EMAIL=admin@stmaryimc.co.ke
   ADMIN_PASSWORD=your_secure_admin_password
   ADMIN_NAME="System Administrator"
   ```

---

## Step 4: Run Installation Commands

### Via SSH (if available):

```bash
cd /home/username/readiness-app

# Generate application key
php artisan key:generate

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/app storage/framework storage/logs

# Run database migrations
php artisan migrate --force

# Seed database with initial data
php artisan db:seed --force

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Link storage folder
php artisan storage:link
```

### Via Terminal in cPanel (if SSH not available):

Most commands above can be run in cPanel's **Terminal** feature (if available).

### Manual Alternative (No SSH):

1. **Generate APP_KEY:**
   - Visit: https://generate-random.org/laravel-key-generator
   - Copy the generated key (e.g., `base64:xyz...`)
   - Paste it in `.env` as `APP_KEY=base64:xyz...`

2. **Set permissions via File Manager:**
   - Select `storage` and `bootstrap/cache` folders
   - Right-click → Change Permissions → 755 or 775

3. **Import database manually:**
   - Go to cPanel → **phpMyAdmin**
   - Select your database
   - You'll need to run migrations manually or import a SQL dump

---

## Step 5: Verify Deployment

1. **Check website:** https://stmaryimc.co.ke
2. **Test login:** https://stmaryimc.co.ke/login
   - Use the ADMIN_EMAIL and ADMIN_PASSWORD from .env

3. **Check if HTTPS is working** (should redirect automatically)

4. **Test email notifications** (create a test event)

---

## Step 6: Post-Deployment Configuration

### 6.1 Set Up Cron Jobs (for scheduled tasks and queues)

In cPanel → **Cron Jobs**:

1. **Add cron for Laravel Scheduler:**
   ```
   * * * * * cd /home/username/readiness-app && php artisan schedule:run >> /dev/null 2>&1
   ```

2. **Add cron for Queue Worker (processes emails):**
   ```
   * * * * * cd /home/username/readiness-app && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
   ```

### 6.2 Configure File Permissions

Ensure these folders are writable:
```
storage/
storage/app/
storage/framework/
storage/logs/
bootstrap/cache/
```

### 6.3 Enable Error Logging

If something goes wrong, check:
- `storage/logs/laravel.log`
- cPanel → **Error Log**

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Solutions:**
1. Check `.env` file exists and is properly configured
2. Run `php artisan config:clear`
3. Check file permissions on storage and cache folders
4. Check error logs in `storage/logs/laravel.log`
5. Ensure PHP version is 8.2+
6. Check `.htaccess` files are present in root and public folder

### Issue: Database Connection Error

**Solutions:**
1. Verify database credentials in `.env`
2. Ensure database user has all privileges
3. Test database connection in phpMyAdmin
4. Check if `DB_HOST` should be `localhost` or an IP address

### Issue: Emails Not Sending

**Solutions:**
1. Verify SMTP credentials in `.env`
2. Check if port 587 is open (some hosts block it)
3. Try port 465 with `MAIL_ENCRYPTION=ssl`
4. Check queue is running: `php artisan queue:work`
5. Test email configuration in cPanel webmail

### Issue: CSS/JS Not Loading

**Solutions:**
1. Ensure `npm run build` was run locally before upload
2. Check if `public/build` folder exists and has files
3. Verify `APP_URL` in `.env` is correct
4. Run `php artisan storage:link`
5. Clear browser cache

### Issue: File Upload Errors

**Solutions:**
1. Check PHP upload limits in cPanel
2. Increase `upload_max_filesize` and `post_max_size` to 20M
3. Ensure `storage/app/public` is writable

---

## Security Checklist

- ✅ `APP_DEBUG=false` in production
- ✅ Strong `APP_KEY` generated
- ✅ Strong database password
- ✅ Strong admin password
- ✅ HTTPS enabled and forced
- ✅ `.env` file not publicly accessible
- ✅ `storage` and `bootstrap/cache` not publicly accessible
- ✅ Regular backups enabled in cPanel
- ✅ File permissions: folders 755, files 644
- ✅ Sensitive folders (vendor, node_modules, .git) outside public_html

---

## Maintenance

### Update Application

1. Back up database and files
2. Upload new files (overwrite old ones)
3. Run migrations: `php artisan migrate --force`
4. Clear cache: `php artisan cache:clear && php artisan config:cache`

### Database Backup

In cPanel → **Backup Wizard** → Create daily/weekly backups

### Monitor Disk Space

Laravel logs can grow large. Rotate logs or clear old ones periodically.

---

## Support

For Truehost-specific issues:
- **Support:** support@truehost.co.ke
- **Documentation:** https://truehost.co.ke/kb

For application issues:
- Check `storage/logs/laravel.log`
- Review this deployment guide

---

## Quick Reference

**Live Site:** https://stmaryimc.co.ke  
**Admin Login:** https://stmaryimc.co.ke/login  
**cPanel:** https://your-server.truehost.cloud:2083  
**Database Host:** localhost  
**SMTP Host:** mail.stmaryimc.co.ke  
**SMTP Port:** 587 (TLS) or 465 (SSL)

---

**Last Updated:** November 2026  
**Laravel Version:** 13.x  
**PHP Required:** 8.2+
