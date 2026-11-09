# Quick Reference Card - stmaryimc.co.ke

## 🌐 Website URLs
- **Live Site:** https://stmaryimc.co.ke
- **Admin Login:** https://stmaryimc.co.ke/login
- **cPanel:** https://your-server.truehost.cloud:2083

## 📁 Server Paths
```
Application Root: /home/stmaryim/laravel-app/
Public Directory: /home/stmaryim/public_html/ → symlink to laravel-app/public/
Log Files: /home/stmaryim/laravel-app/storage/logs/
```

## 🗄️ Database
```
Connection: mysql
Host: localhost
Port: 3306
Database: stmaryim_readiness
Username: stmaryim_user
Password: [from .env]
```

## ✉️ Email (SMTP)
```
Host: mail.stmaryimc.co.ke
Port: 587 (TLS) or 465 (SSL)
Username: noreply@stmaryimc.co.ke
Password: [from .env]
From Address: noreply@stmaryimc.co.ke
From Name: St. Mary IMC Dashboard
```

## 🔧 Essential Commands

### Via SSH/Terminal:
```bash
# Navigate to app
cd /home/stmaryim/laravel-app

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Process queued emails
php artisan queue:work --once

# Check application status
php artisan about

# View recent logs
tail -n 50 storage/logs/laravel.log
```

## 📅 Cron Jobs

**Laravel Scheduler (runs every minute):**
```
* * * * * cd /home/stmaryim/laravel-app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Queue Worker (processes emails every minute):**
```
* * * * * cd /home/stmaryim/laravel-app && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

## 🔍 Debugging

**Check logs:**
```bash
tail -f /home/stmaryim/laravel-app/storage/logs/laravel.log
```

**Check permissions:**
```bash
ls -la /home/stmaryim/laravel-app/storage/
ls -la /home/stmaryim/laravel-app/bootstrap/cache/
```

**Fix permissions:**
```bash
chmod -R 755 /home/stmaryim/laravel-app/storage
chmod -R 755 /home/stmaryim/laravel-app/bootstrap/cache
find /home/stmaryim/laravel-app/storage -type d -exec chmod 775 {} \;
```

**Test database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

**Test email:**
```bash
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('your@email.com')->subject('Test'); });
>>> exit
```

## 🚨 Common Issues

### 500 Error
```bash
# Check logs first
tail -n 50 storage/logs/laravel.log

# Clear all cache
php artisan config:clear && php artisan cache:clear

# Regenerate autoload
composer dump-autoload
```

### Database Error
- Check `.env` database credentials
- Verify database exists in cPanel
- Check user has ALL PRIVILEGES

### Email Not Sending
```bash
# Process queue manually
php artisan queue:work --once

# Check mail config
php artisan config:show mail
```

### CSS/JS Not Loading
- Clear browser cache (Ctrl+Shift+R)
- Check `APP_URL` in `.env`
- Verify `public/build/` folder exists

## 📊 Monitoring

**Check disk space (cPanel):**
- Metrics → Disk Usage

**Check application health:**
```bash
php artisan about
```

**Check queue jobs:**
```bash
# View failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <id>

# Retry all failed
php artisan queue:retry all
```

## 🔐 Security

**Update password:**
```bash
php artisan tinker
>>> $user = User::where('email', 'admin@stmaryimc.co.ke')->first();
>>> $user->password = Hash::make('NewPassword123!');
>>> $user->save();
>>> exit
```

**Force HTTPS:**
- Already configured in `.htaccess`
- SSL certificate in cPanel → SSL/TLS Status

## 🔄 Update Process

**To update application:**
```bash
# 1. Backup first!
# In cPanel: Backup Wizard → Full Backup

# 2. Upload new files (overwrite)
# 3. Run migrations
php artisan migrate --force

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 💾 Backup

**Manual backup:**
```bash
# Database export (via phpMyAdmin or SSH)
mysqldump -u stmaryim_user -p stmaryim_readiness > backup.sql

# Files backup (cPanel → Backup Wizard)
```

**Restore database:**
```bash
mysql -u stmaryim_user -p stmaryim_readiness < backup.sql
```

## 📞 Support Contacts

**Truehost:**
- Support: support@truehost.co.ke
- Phone: +254 (0) 20 528 0000
- Website: https://truehost.co.ke

**System Admin:**
- Email: admin@stmaryimc.co.ke

## 📚 Documentation

- Full Deployment: `DEPLOYMENT.md`
- Quick Setup: `TRUEHOST-SETUP.md`
- Pre-Deploy Checklist: `PRE-DEPLOYMENT-CHECKLIST.md`
- Automated Deploy: `bash deploy.sh`

## ⚡ Emergency Recovery

**Site down?**
1. Check error logs: `storage/logs/laravel.log`
2. Check cPanel Error Log
3. Verify database is running
4. Check disk space
5. Restart queue: Kill cron, start again
6. Put in maintenance mode: `php artisan down`
7. Fix issue
8. Bring back online: `php artisan up`

**Database corrupted?**
1. Restore from latest backup
2. Re-run migrations if needed

**Files corrupted?**
1. Re-upload from backup or Git
2. Set permissions: `chmod -R 755 storage bootstrap/cache`

---

**Keep this document handy for quick reference! 📌**
