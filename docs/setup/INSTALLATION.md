# Installation Guide

**SanctumEMHR EMHR - Mental Health EMR System**

---

## Prerequisites

### Required Software

- **PHP 8.0+**
- **MySQL 8.0+** or **MariaDB 10.4+**
- **Apache 2.4+** or **Nginx**
- **Node.js 18+** and **npm 9+**
- **Composer** (PHP package manager)
- **Git**

### System Requirements

- **RAM:** 2GB minimum, 4GB recommended
- **Storage:** 10GB minimum
- **OS:** Linux (Ubuntu 20.04+, CentOS 8+), macOS, or Windows with WSL

---

## Installation Steps

### 1. Install OpenEMR Base

Since SanctumEMHR EMHR currently runs on OpenEMR infrastructure:

```bash
# Download OpenEMR
cd /var/www/
git clone https://github.com/openemr/openemr.git
cd openemr

# Install PHP dependencies
composer install --no-dev

# Set permissions
chmod 666 sites/default/sqlconf.php
chmod -R 755 sites/default/documents
chown -R www-data:www-data sites/default/documents
```

### 2. Configure Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE sanctum_emhr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'openemr'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON sanctum_emhr.* TO 'openemr'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure OpenEMR

1. Navigate to `http://localhost/openemr` in your browser
2. Follow the installation wizard:
   - Database: `sanctum_emhr`
   - User: `openemr`
   - Password: `your_secure_password`
   - Create initial admin user

3. Complete installation

### 4. Install SanctumEMHR Custom Files

```bash
# Clone SanctumEMHR EMHR repository
cd /var/www/openemr
git clone https://github.com/yourusername/sacwan-openemr-mh.git sanctumEMHR

# Copy custom API files
cp -r sanctumEMHR/custom /var/www/openemr/

# Set permissions
chown -R www-data:www-data /var/www/openemr/custom
chmod -R 755 /var/www/openemr/custom
```

### 5. Install React Frontend

```bash
# Navigate to React frontend
cd /var/www/openemr/sanctumEMHR/react-frontend

# Install dependencies
npm install

# Create environment file
cp .env.example .env

# Edit .env with your settings
nano .env
```

**`.env` Configuration:**
```env
VITE_API_BASE_URL=http://localhost/custom/api
VITE_APP_NAME=SanctumEMHR EMHR
```

### 6. Build Frontend (Production)

```bash
# Build for production
npm run build

# Copy build to web root
cp -r dist/* /var/www/html/sanctumEMHR/
```

### 7. Configure Apache

Create virtual host:

```bash
sudo nano /etc/apache2/sites-available/sanctumEMHR.conf
```

```apache
<VirtualHost *:80>
    ServerName emr.local
    DocumentRoot /var/www/html/sanctumEMHR

    # Frontend
    <Directory /var/www/html/sanctumEMHR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # API Proxy
    ProxyPass /custom/api http://localhost/openemr/custom/api
    ProxyPassReverse /custom/api http://localhost/openemr/custom/api

    # Enable CORS
    Header always set Access-Control-Allow-Origin "http://localhost:5173"
    Header always set Access-Control-Allow-Credentials "true"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type"

    ErrorLog ${APACHE_LOG_DIR}/sanctumEMHR-error.log
    CustomLog ${APACHE_LOG_DIR}/sanctumEMHR-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite sanctumEMHR.conf
sudo a2enmod proxy proxy_http headers
sudo systemctl reload apache2
```

### 8. Configure Hosts File (Development)

```bash
sudo nano /etc/hosts
```

Add:
```
127.0.0.1   emr.local
```

---

## Development Setup

### Run Frontend in Development Mode

```bash
cd /var/www/openemr/sanctumEMHR/react-frontend
npm run dev
```

Frontend will be available at `http://localhost:5173`

Backend API at `http://localhost/openemr/custom/api`

---

## Initial Configuration

### 1. Create Rooms/Locations

1. Login to OpenEMR admin panel
2. Navigate to **Administration > Lists**
3. Find or create **"rooms"** list
4. Add your rooms:
   - Room 101
   - Room 102
   - Therapy Room A
   - etc.

### 2. Create Appointment Categories

1. Navigate to **Administration > Calendar > Categories**
2. Create categories:
   - Initial Consult (Blue, Type: Appointment)
   - Follow-up (Green, Type: Appointment)
   - Out of Office (Red, Type: Availability)
   - Vacation (Orange, Type: Availability)

### 3. Configure Calendar Settings

1. Login to SanctumEMHR EMHR React app
2. Navigate to **Settings > Calendar Settings**
3. Configure:
   - Time interval: 15 minutes
   - Start hour: 8 AM
   - End hour: 6 PM
   - Default duration: 50 minutes

### 4. Create Provider Accounts

1. Navigate to **Administration > Users**
2. Create user accounts for providers
3. Set permissions:
   - `authorized` = 1 (Provider access)
   - `calendar` = 1 (Calendar admin - optional)

---

## Verification

### Test API Endpoints

```bash
# Test authentication
curl -X POST http://localhost/openemr/custom/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"pass"}'

# Test appointments (requires session)
curl http://localhost/openemr/custom/api/get_appointments.php?start_date=2026-01-01&end_date=2026-01-31 \
  --cookie "PHPSESSID=your_session_id"
```

### Test Frontend

1. Navigate to `http://localhost:5173` (dev) or `http://emr.local` (prod)
2. Login with admin credentials
3. Verify:
   - Dashboard loads
   - Calendar displays
   - Can create appointments
   - Settings accessible

---

## Troubleshooting

### API Returns 401 Unauthorized

**Issue:** Session not being maintained

**Solution:**
- Check CORS settings
- Verify `credentials: 'include'` in fetch calls
- Check session cookie domain

### Frontend Can't Connect to API

**Issue:** CORS errors

**Solution:**
```php
// In each API file, verify headers:
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
```

### Database Connection Errors

**Issue:** Can't connect to MySQL

**Solution:**
- Verify database credentials in `/var/www/openemr/sites/default/sqlconf.php`
- Check MySQL is running: `sudo systemctl status mysql`
- Verify user permissions: `SHOW GRANTS FOR 'openemr'@'localhost';`

### Permission Errors

**Issue:** Can't write to files

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/openemr
sudo chmod -R 755 /var/www/openemr/custom
```

### React Build Fails

**Issue:** npm build errors

**Solution:**
```bash
# Clear cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
npm run build
```

---

## Security Hardening (Production)

### 1. Use HTTPS

```bash
# Install certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d emr.yourdomain.com
```

### 2. Disable Directory Listing

```apache
<Directory /var/www/html/sanctumEMHR>
    Options -Indexes
</Directory>
```

### 3. Set Strong Passwords

- Change default OpenEMR admin password
- Use strong database passwords
- Enable two-factor authentication (if available)

### 4. Configure PHP Security

Edit `/etc/php/8.0/apache2/php.ini`:

```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
session.cookie_httponly = 1
session.cookie_secure = 1
```

### 5. Configure Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## Backup Strategy

### Database Backup

```bash
# Create backup script
cat > /usr/local/bin/backup-sanctumEMHR-db.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/sanctumEMHR"
mkdir -p $BACKUP_DIR

mysqldump -u openemr -p sanctum_emhr | gzip > $BACKUP_DIR/sanctum_db_$DATE.sql.gz

# Keep last 30 days
find $BACKUP_DIR -name "sanctum_db_*.sql.gz" -mtime +30 -delete
EOF

chmod +x /usr/local/bin/backup-sanctumEMHR-db.sh

# Add to crontab (daily at 2 AM)
(crontab -l ; echo "0 2 * * * /usr/local/bin/backup-sanctumEMHR-db.sh") | crontab -
```

### File Backup

```bash
# Backup custom files
tar -czf /var/backups/sanctumEMHR/custom_$(date +%Y%m%d).tar.gz /var/www/openemr/custom
```

---

## Updating

### Update Frontend

```bash
cd /var/www/openemr/sanctumEMHR/react-frontend
git pull
npm install
npm run build
cp -r dist/* /var/www/html/sanctumEMHR/
```

### Update API

```bash
cd /var/www/openemr/sanctumEMHR
git pull
cp -r custom/* /var/www/openemr/custom/
sudo systemctl reload apache2
```

---

## Uninstallation

### Remove SanctumEMHR Files

```bash
rm -rf /var/www/openemr/custom
rm -rf /var/www/html/sanctumEMHR
rm -rf /var/www/openemr/sanctumEMHR
```

### Remove Database

```bash
mysql -u root -p
DROP DATABASE sanctum_emhr;
DROP USER 'openemr'@'localhost';
EXIT;
```

### Remove Apache Config

```bash
sudo a2dissite sanctumEMHR.conf
sudo rm /etc/apache2/sites-available/sanctumEMHR.conf
sudo systemctl reload apache2
```

---

## Next Steps

- [Configuration Guide](./CONFIGURATION.md)
- [Development Guide](../guides/DEVELOPMENT.md)
- [API Documentation](../api/ENDPOINTS.md)

---

**Last Updated:** January 3, 2026
**Version:** 1.0
