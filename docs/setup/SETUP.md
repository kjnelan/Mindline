# SanctumEMHR Setup Guide

Complete step-by-step guide to set up SanctumEMHR from scratch.

---

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer (for autoloading)
- Web server (Apache/Nginx)

---

## Step 1: Database Setup

### 1.1 Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE mindline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 1.2 Import the Schema

```bash
mysql -u root -p mindline < database/mindline.sql
```

This will create 32 tables including:
- `users` - User accounts
- `sessions` - Session storage
- `clients` - Client/patient records
- `appointments` - Appointment scheduling
- `clinical_notes` - Clinical documentation
- `audit_logs` - Security and activity logs
- And 26 more tables...

### 1.3 Verify Import

```bash
mysql -u root -p mindline -e "SHOW TABLES;"
```

You should see 32 tables listed.

---

## Step 2: Database Configuration

### 2.1 Copy the Config Template

```bash
cp config/database.php.example config/database.php
```

### 2.2 Edit the Config File

```bash
nano config/database.php
```

Update with your database credentials:

```php
<?php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'mindline',
    'username' => 'your_db_user',      // ← Change this
    'password' => 'your_db_password',  // ← Change this
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

**Important**: Make sure `config/database.php` is in `.gitignore` so you don't commit credentials!

---

## Step 3: Create Your First Admin User

### Option A: Use the Helper Script (Recommended)

```bash
php scripts/create_admin_user.php
```

Follow the prompts to create an admin user.

### Option B: Manual SQL Method

1. Generate a password hash:

```bash
php -r "echo password_hash('YourPassword123!', PASSWORD_ARGON2ID) . \"\n\";"
```

2. Insert the user:

```bash
mysql -u root -p mindline
```

```sql
INSERT INTO users (
    username, email, password_hash, first_name, last_name,
    user_type, is_active, is_provider
) VALUES (
    'admin',
    'admin@mindline.local',
    '$argon2id$v=19$m=65536,t=4,p=1$...',  -- Paste hash from step 1
    'Admin',
    'User',
    'admin',
    1,
    0
);
```

---

## Step 4: Configure Web Server

### Apache Configuration

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName mindline.local
    DocumentRoot /path/to/Mineline

    <Directory /path/to/Mineline>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Enable CORS for React frontend
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization"

    ErrorLog ${APACHE_LOG_DIR}/mindline_error.log
    CustomLog ${APACHE_LOG_DIR}/mindline_access.log combined
</VirtualHost>
```

Enable modules and restart:

```bash
sudo a2enmod headers
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name mindline.local;
    root /path/to/Mineline;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # CORS headers
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization' always;

    location ~ /\.ht {
        deny all;
    }
}
```

Restart Nginx:

```bash
sudo systemctl restart nginx
```

---

## Step 5: Test Authentication

### 5.1 Test Login API

```bash
curl -X POST http://your-server/custom/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"YourPassword123!"}' \
  -c cookies.txt -v
```

Expected response:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@mindline.local",
    "firstName": "Admin",
    "lastName": "User",
    "fullName": "Admin User",
    "displayName": "Admin User",
    "userType": "admin",
    "isProvider": false,
    "isAdmin": true,
    "npi": null
  }
}
```

### 5.2 Test Session Check

```bash
curl -X GET http://your-server/custom/api/session_user.php \
  -b cookies.txt
```

Should return the same user data.

### 5.3 Test Logout

```bash
curl -X POST http://your-server/custom/api/session_logout.php \
  -b cookies.txt
```

Expected response:

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Step 6: React Frontend Setup

### 6.1 Update API Base URL

Edit `/react-frontend/.env` or equivalent:

```env
REACT_APP_API_BASE_URL=http://your-server/custom/api
```

### 6.2 Update Auth Service (if needed)

The API response format has changed. Make sure your React auth service handles:

```javascript
// Login response
{
  success: true,
  user: {
    id: number,
    username: string,
    firstName: string,
    lastName: string,
    fullName: string,
    displayName: string,
    userType: 'admin' | 'provider' | 'staff' | 'billing',
    isProvider: boolean,
    isAdmin: boolean
  }
}
```

### 6.3 Start React Development Server

```bash
cd react-frontend
npm install
npm start
```

---

## Troubleshooting

### Database Connection Errors

**Error**: "Database connection failed"

**Solutions**:
1. Verify database credentials in `/config/database.php`
2. Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`
3. Check user permissions: `GRANT ALL ON mindline.* TO 'user'@'localhost';`
4. Verify MySQL is running: `systemctl status mysql`

### "Table doesn't exist" Errors

**Error**: "Table 'mindline.users' doesn't exist"

**Solution**:
```bash
# Re-import the schema
mysql -u root -p mindline < database/mindline.sql
```

### Login Always Fails

**Check password hash**:
```sql
SELECT id, username, password_hash FROM users WHERE username='admin';
```

If password_hash looks wrong, regenerate:
```bash
php -r "echo password_hash('YourPassword123!', PASSWORD_ARGON2ID) . \"\n\";"
```

Then update:
```sql
UPDATE users SET password_hash='$argon2id$v=19$...' WHERE username='admin';
```

### Account Locked

**Error**: User can't login after multiple failed attempts

**Solution**:
```sql
-- Check if account is locked
SELECT username, failed_login_attempts, locked_until FROM users WHERE username='admin';

-- Unlock account
UPDATE users
SET failed_login_attempts = 0, locked_until = NULL
WHERE username='admin';
```

### Session Issues

**Error**: "Not authenticated" even after login

**Solutions**:
1. Verify `sessions` table exists:
   ```sql
   SHOW TABLES LIKE 'sessions';
   ```

2. Check PHP session settings:
   ```bash
   php -i | grep session
   ```

3. Clear old sessions:
   ```sql
   TRUNCATE TABLE sessions;
   ```

4. Check browser cookies are enabled

---

## Security Checklist

### Production Deployment

- [ ] Change default database password
- [ ] Set strong admin password (min 8 chars, mixed case, numbers, special chars)
- [ ] Disable error display: `ini_set('display_errors', 0);`
- [ ] Enable HTTPS/SSL
- [ ] Set secure cookie flags in production
- [ ] Restrict CORS to your frontend domain only
- [ ] Set file permissions: `chmod 600 config/database.php`
- [ ] Regular database backups
- [ ] Monitor `audit_logs` table for suspicious activity

---

## What's Next?

After completing this setup, you have:

✅ Clean SanctumEMHR database with 32 tables
✅ Working authentication system
✅ Database abstraction layer
✅ Session management
✅ Admin user account

### Phase 2: Update Remaining APIs

The following API endpoints still use OpenEMR and need to be updated:

**High Priority**:
- Client management (6 files)
- Appointment management (4 files)
- User/provider management (2 files)

**Medium Priority**:
- Clinical notes (2 files)
- Documents (2 files)

**Lower Priority**:
- Billing (2 files)
- Reference data (5 files)

See `AUTHENTICATION_SETUP.md` for migration patterns and next steps.

---

## Need Help?

1. Check the logs:
   - Apache: `/var/log/apache2/mindline_error.log`
   - PHP: Check `error_log()` output in web server logs
   - Database: Check MySQL error log

2. Review documentation:
   - `AUTHENTICATION_SETUP.md` - Auth system details
   - `DATABASE_SCHEMA.md` - Complete schema documentation
   - `DECOUPLING_ANALYSIS.md` - Migration roadmap

3. Check audit logs:
   ```sql
   SELECT * FROM audit_logs
   ORDER BY created_at DESC
   LIMIT 20;
   ```

---

## Summary

You now have a fully functional SanctumEMHR authentication system running on your own clean database, completely independent of OpenEMR!

To log in:
- **URL**: `http://your-server/custom/api/login.php`
- **Username**: (the username you created)
- **Password**: (the password you set)

🎉 **Congratulations! SanctumEMHR is ready to use.**
