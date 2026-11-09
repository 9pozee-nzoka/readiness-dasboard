# Pre-Deployment Checklist for stmaryimc.co.ke

Complete this checklist before deploying to production.

## 📋 Local Preparation

### Code & Dependencies
- [ ] All code committed to Git
- [ ] No pending changes or uncommitted files
- [ ] `composer.json` and `composer.lock` are up to date
- [ ] `package.json` and `package-lock.json` are up to date
- [ ] All tests passing: `php artisan test`
- [ ] Code formatted: `vendor/bin/pint`

### Build Assets
- [ ] Frontend assets built for production: `npm run build`
- [ ] `public/build` folder exists with compiled assets
- [ ] No console errors when testing locally

### Environment Configuration
- [ ] `.env.production` file created and reviewed
- [ ] All sensitive data removed from `.env.production`
- [ ] APP_KEY placeholder is empty (will be generated on server)
- [ ] APP_DEBUG set to `false`
- [ ] APP_ENV set to `production`
- [ ] APP_URL set to `https://stmaryimc.co.ke`

### Database
- [ ] All migrations created and tested
- [ ] Seeders updated (AdminUserSeeder, etc.)
- [ ] No migrations will drop/lose critical data
- [ ] Database backup strategy planned

---

## 🌐 Truehost Account Setup

### Account Access
- [ ] Truehost account active
- [ ] cPanel login credentials saved
- [ ] cPanel URL noted (e.g., `https://server.truehost.cloud:2083`)

### Domain Configuration
- [ ] Domain `stmaryimc.co.ke` registered
- [ ] Domain pointing to Truehost nameservers:
  - `ns1.truehost.co.ke`
  - `ns2.truehost.co.ke`
- [ ] DNS propagation complete (check: https://dnschecker.org)
- [ ] Domain added to cPanel

### Hosting Resources
- [ ] Disk space sufficient (check cPanel dashboard)
- [ ] Bandwidth limits reviewed
- [ ] Database quota sufficient
- [ ] Email quota sufficient

---

## 🗄️ Database Setup

- [ ] MySQL database created in cPanel
  - Database name: `stmaryim_readiness`
- [ ] MySQL user created with strong password
  - Username: `stmaryim_user`
- [ ] User added to database with ALL PRIVILEGES
- [ ] Database credentials saved securely
- [ ] Connection tested from phpMyAdmin

---

## ✉️ Email Configuration

### Email Account
- [ ] Email account created: `noreply@stmaryimc.co.ke`
- [ ] Strong password set for email
- [ ] Email quota allocated (min 250MB)
- [ ] Test email sent from cPanel webmail

### SMTP Settings
- [ ] SMTP host identified: `mail.stmaryimc.co.ke`
- [ ] SMTP port confirmed: 587 (TLS) or 465 (SSL)
- [ ] SMTP credentials saved
- [ ] Authentication method verified

---

## 🔧 Server Configuration

### PHP Configuration
- [ ] PHP version 8.2 or 8.3 selected
- [ ] Required PHP extensions enabled:
  - [ ] bcmath
  - [ ] ctype
  - [ ] curl
  - [ ] dom
  - [ ] fileinfo
  - [ ] json
  - [ ] mbstring
  - [ ] openssl
  - [ ] pcre
  - [ ] pdo
  - [ ] pdo_mysql
  - [ ] tokenizer
  - [ ] xml
  - [ ] zip

### PHP Limits
- [ ] `upload_max_filesize` = 20M or higher
- [ ] `post_max_size` = 20M or higher
- [ ] `max_execution_time` = 300 or higher
- [ ] `memory_limit` = 256M or higher

### Server Software
- [ ] Composer available (check: `composer --version`)
- [ ] Node.js version checked (if needed on server)
- [ ] Git available (if using deployment from Git)

---

## 🔐 Security Configuration

### SSL/HTTPS
- [ ] SSL certificate plan confirmed (Let's Encrypt/AutoSSL)
- [ ] HTTPS will be enabled after deployment
- [ ] Force HTTPS redirect configured (in .htaccess)

### Application Security
- [ ] Strong `ADMIN_PASSWORD` chosen
- [ ] Strong database password set
- [ ] Strong email password set
- [ ] `.env` file will not be publicly accessible
- [ ] `storage/` and `vendor/` not in public_html

### File Permissions
- [ ] Understanding of proper Laravel permissions (755/644)
- [ ] Plan to set writable folders (storage, bootstrap/cache)

---

## 📦 Deployment Package

- [ ] Deployment package created: `stmary-dashboard.tar.gz`
- [ ] Package excludes:
  - [ ] `node_modules/`
  - [ ] `.git/`
  - [ ] `.env`
  - [ ] `tests/`
  - [ ] Temporary/cache files
- [ ] Package includes:
  - [ ] `public/build/` (compiled assets)
  - [ ] `vendor/` OR plan to run `composer install` on server
  - [ ] All application code
  - [ ] `.htaccess` files

---

## 🚀 Deployment Process

### Pre-Upload
- [ ] Backup any existing website files
- [ ] Document current cPanel configuration
- [ ] Maintenance page prepared (if needed)

### Upload & Extract
- [ ] Files uploaded to Truehost
- [ ] Archive extracted to correct location
- [ ] Folder structure verified

### Configuration
- [ ] `.env` file created from `.env.production`
- [ ] `.env` updated with actual credentials
- [ ] `APP_KEY` generated
- [ ] All environment variables verified

### Installation
- [ ] Composer dependencies installed (if not in package)
- [ ] Database migrations run
- [ ] Database seeded
- [ ] Storage linked
- [ ] Configuration cached
- [ ] File permissions set

---

## ⚙️ Post-Deployment

### Cron Jobs
- [ ] Laravel scheduler cron added
- [ ] Queue worker cron added
- [ ] Cron jobs tested (wait 1 minute, check logs)

### Testing
- [ ] Website loads: https://stmaryimc.co.ke
- [ ] Login successful with admin credentials
- [ ] Dashboard displays correctly
- [ ] Create test event works
- [ ] Email notification sent and received
- [ ] File upload tested
- [ ] All menu items accessible
- [ ] Mobile responsiveness checked

### Performance
- [ ] Page load speed acceptable
- [ ] No console errors (F12)
- [ ] Images loading correctly
- [ ] CSS/JS loading correctly

### Monitoring
- [ ] Error logging working (check `storage/logs/`)
- [ ] Email delivery working
- [ ] Queue processing working
- [ ] Disk space monitored

---

## 📝 Documentation

- [ ] Admin credentials saved in password manager
- [ ] Database credentials saved securely
- [ ] SMTP credentials documented
- [ ] cPanel access documented
- [ ] SSH access documented (if available)
- [ ] Deployment process documented
- [ ] Backup procedure documented

---

## 🔄 Backup Strategy

### Before Deployment
- [ ] Local database exported
- [ ] Local files backed up
- [ ] Git repository pushed to remote

### After Deployment
- [ ] Production database backed up
- [ ] Production files backed up
- [ ] Backup schedule configured in cPanel
- [ ] Backup restoration tested

---

## 👥 Access & Credentials

### Admin Access
- Email: `____________________`
- Password: `____________________`
- Name: `____________________`

### Database
- Host: `localhost`
- Database: `stmaryim_readiness`
- Username: `stmaryim_user`
- Password: `____________________`

### Email
- Address: `noreply@stmaryimc.co.ke`
- Password: `____________________`
- SMTP Host: `mail.stmaryimc.co.ke`
- SMTP Port: `587`

### cPanel
- URL: `____________________`
- Username: `____________________`
- Password: `____________________`

---

## ⚠️ Known Issues & Solutions

### Issue 1: [List any known issues]
**Solution:** [Solution description]

### Issue 2: [List any known issues]
**Solution:** [Solution description]

---

## 📞 Emergency Contacts

**Truehost Support:**
- Email: support@truehost.co.ke
- Phone: +254 (0) 20 528 0000

**System Administrator:**
- Name: ____________________
- Email: ____________________
- Phone: ____________________

**Developer:**
- Name: ____________________
- Email: ____________________
- Phone: ____________________

---

## ✅ Final Sign-Off

**Deployed By:** ____________________  
**Date:** ____________________  
**Time:** ____________________  
**Deployment Status:** ☐ Success  ☐ Issues (describe below)

**Notes:**
```
[Add any deployment notes, issues encountered, or important information]
```

---

**All checkboxes completed?** ✅ You're ready to deploy!

**Reference Documents:**
- Full guide: `DEPLOYMENT.md`
- Quick guide: `TRUEHOST-SETUP.md`
- Deployment script: `deploy.sh`
