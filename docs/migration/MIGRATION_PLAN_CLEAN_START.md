# SanctumEMHR Database Migration - Clean Start Plan
**Date**: 2026-01-17
**Status**: PLANNING - Starting Fresh

## Executive Summary

We are **completely decoupling from OpenEMR** and migrating to our own SanctumEMHR database and infrastructure.

### Current Issues Identified

1. ❌ **Unused OpenEMR API constants** in React `api.js`
2. ❌ **All 49 custom API files still require `globals.php`** (loads entire OpenEMR)
3. ❌ **No custom `database.php`** - still using OpenEMR database functions
4. ❌ **React app references OpenEMR endpoints** that aren't actually used

### The Goal

Create a **standalone SanctumEMHR EMHR system** with:
- ✅ Our own database layer (no OpenEMR database functions)
- ✅ Our own bootstrap file (no `globals.php`)
- ✅ Our own authentication (no OpenEMR auth classes)
- ✅ New database schema (new table names, clean structure)
- ✅ Clean separation from OpenEMR codebase

---

## Phase 1: Create SanctumEMHR Infrastructure (Week 1)

### Step 1.1: Create New Database Configuration

**File**: `/custom/config/database.php`

```php
<?php
/**
 * SanctumEMHR EMHR - Database Configuration
 *
 * This replaces OpenEMR's database configuration
 */

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'mindline',
    'username' => getenv('DB_USER') ?: 'mindline_user',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### Step 1.2: Create SanctumEMHR Database Class

**File**: `/custom/lib/Database/Database.php`

This will replace OpenEMR's `sqlQuery()`, `sqlStatement()`, etc.

**Methods to implement**:
- `query($sql, $params = [])` - Execute query, return all rows
- `queryOne($sql, $params = [])` - Execute query, return first row
- `execute($sql, $params = [])` - Execute statement (INSERT/UPDATE/DELETE)
- `insert($table, $data)` - Insert row, return ID
- `update($table, $data, $where, $params)` - Update rows
- `delete($table, $where, $params)` - Delete rows
- `lastInsertId()` - Get last insert ID
- `beginTransaction()`, `commit()`, `rollback()` - Transactions

### Step 1.3: Create SanctumEMHR Bootstrap File

**File**: `/custom/bootstrap.php`

This will replace `globals.php`.

**What it does**:
1. Load composer autoloader
2. Load environment variables (.env file)
3. Initialize database connection
4. Start PHP session
5. Load SanctumEMHR configuration
6. Set up error handling
7. Define helper functions (if needed)
8. **NOT** load OpenEMR classes
9. **NOT** load OpenEMR globals

### Step 1.4: Create SanctumEMHR Auth Class

**File**: `/custom/lib/Auth/Auth.php`

This replaces OpenEMR's `AuthUtils`, `SessionUtil`, `UserService`.

**Methods**:
- `login($username, $password)` - Authenticate user
- `logout()` - End session
- `check()` - Verify session is valid
- `user()` - Get current user
- `id()` - Get current user ID
- `hasRole($role)` - Check user role
- `hasPermission($permission)` - Check permission

---

## Phase 2: Create New Database Schema (Week 1-2)

### Step 2.1: Create New SanctumEMHR Database

```bash
# Create new database
mysql -u root -p -e "CREATE DATABASE mindline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create mindline user
mysql -u root -p -e "CREATE USER 'mindline_user'@'localhost' IDENTIFIED BY 'secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON mindline.* TO 'mindline_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

### Step 2.2: Rename Tables from OpenEMR to SanctumEMHR

We already have the schema design in `DATABASE_SCHEMA.md`. Key changes:

| OpenEMR Table | New SanctumEMHR Table |
|---------------|-------------------|
| `users` | `mindline_users` |
| `patient_data` | `mindline_clients` |
| `openemr_postcalendar_events` | `mindline_appointments` |
| `openemr_postcalendar_categories` | `mindline_appointment_categories` |
| `facility` | `mindline_facilities` |
| `form_encounter` | `mindline_encounters` |
| `clinical_notes` | `mindline_clinical_notes` |
| `insurance_data` | `mindline_client_insurance` |
| `insurance_companies` | `mindline_insurance_providers` |

### Step 2.3: Run Migration Scripts

**File**: `/custom/sql/01_create_schema.sql`
**File**: `/custom/sql/02_migrate_data.sql`

---

## Phase 3: Update Custom API Files (Week 2-3)

### Step 3.1: Remove `globals.php` from All API Files

**Before** (49 files like this):
```php
<?php
require_once dirname(__FILE__, 3) . "/interface/globals.php";

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Services\UserService;

// Query using OpenEMR functions
$result = sqlQuery("SELECT * FROM users WHERE id = ?", [$userId]);
```

**After**:
```php
<?php
require_once dirname(__FILE__, 2) . "/bootstrap.php";

use SanctumEMHR\Database\Database;
use SanctumEMHR\Auth\Auth;

// Query using SanctumEMHR Database class
$db = Database::getInstance();
$result = $db->queryOne("SELECT * FROM mindline_users WHERE id = ?", [$userId]);
```

### Step 3.2: Update React Frontend API Constants

**File**: `/home/user/SanctumEMHR/react-frontend/src/utils/api.js`

**REMOVE** (lines 14-15):
```javascript
const API_BASE = '/apis/default';  // DELETE THIS
const FHIR_BASE = '/fhir';         // DELETE THIS
```

**These are never used!** All API calls use `/custom/api/` directly.

### Step 3.3: Update All Database Queries

Replace OpenEMR function calls in all 49 API files:

| Old (OpenEMR) | New (SanctumEMHR) |
|---------------|----------------|
| `sqlQuery($sql, $params)` | `$db->queryOne($sql, $params)` |
| `sqlStatement($sql, $params)` | `$db->query($sql, $params)` |
| `sqlInsert($sql, $params)` | `$db->execute($sql, $params)` |
| `sqlFetchArray($result)` | N/A (already in array format) |

---

## Phase 4: Test and Validate (Week 3-4)

### Step 4.1: Test Authentication

1. Login via React app
2. Verify session works
3. Verify logout works
4. Test session expiration

### Step 4.2: Test Each API Endpoint

Create test script to verify all 49 endpoints work:
- Session endpoints (login, logout, user)
- Appointment endpoints
- Client endpoints
- Clinical notes endpoints
- etc.

### Step 4.3: Data Verification

1. Verify all OpenEMR data was migrated correctly
2. Verify relationships (foreign keys) are intact
3. Verify no data loss

---

## Phase 5: Remove OpenEMR Dependencies (Week 4)

### Step 5.1: Remove OpenEMR Directories

**After migration is complete and tested:**

```bash
# Backup first!
tar -czf openemr_backup_$(date +%Y%m%d).tar.gz src/ library/ interface/

# Remove OpenEMR core (only after testing!)
# rm -rf src/           # 21MB OpenEMR core classes
# rm -rf library/       # OpenEMR legacy library
# rm -rf interface/     # OpenEMR UI (we use React)
```

### Step 5.2: Update Composer Dependencies

**File**: `/composer.json`

Remove OpenEMR-specific dependencies, keep only:
- Database drivers (PDO)
- Session management (if needed)
- Any other utilities we use

### Step 5.3: Update Documentation

Update README.md:
- Remove references to OpenEMR
- Update installation instructions
- Update architecture diagram
- Update API documentation

---

## Detailed File Changes Checklist

### Files to Create (New)

- [ ] `/custom/config/database.php` - Database configuration
- [ ] `/custom/bootstrap.php` - SanctumEMHR bootstrap (replaces globals.php)
- [ ] `/custom/lib/Database/Database.php` - Database abstraction layer
- [ ] `/custom/lib/Auth/Auth.php` - Authentication class
- [ ] `/custom/lib/Auth/SessionManager.php` - Session management
- [ ] `/custom/lib/User/UserService.php` - User service
- [ ] `/custom/sql/01_create_schema.sql` - New schema creation
- [ ] `/custom/sql/02_migrate_data.sql` - Data migration script
- [ ] `/.env.example` - Environment variables template
- [ ] `/.env` - Actual environment config (git-ignored)

### Files to Update (Modify)

- [ ] `/react-frontend/src/utils/api.js` - Remove unused OpenEMR constants
- [ ] All 49 files in `/custom/api/*.php` - Replace globals.php with bootstrap.php
- [ ] All 49 files in `/custom/api/*.php` - Replace OpenEMR database functions
- [ ] All 49 files in `/custom/api/*.php` - Update table names to SanctumEMHR tables
- [ ] `/README.md` - Remove OpenEMR references
- [ ] `/composer.json` - Remove OpenEMR dependencies
- [ ] `/.gitignore` - Add .env file

### Files to Delete (After Migration)

- [ ] `/src/` - OpenEMR core (21MB) - **ONLY after migration complete**
- [ ] `/library/` - OpenEMR legacy library - **ONLY after migration complete**
- [ ] `/interface/` - OpenEMR UI - **ONLY after migration complete**

---

## Risk Assessment

### High Risk Items

1. **Data Migration** - Risk of data loss if migration script fails
   - **Mitigation**: Full database backup before migration
   - **Mitigation**: Test migration on copy of database first
   - **Mitigation**: Verify all data after migration

2. **Authentication Breaking** - Risk of users unable to login
   - **Mitigation**: Keep OpenEMR system running in parallel initially
   - **Mitigation**: Test authentication thoroughly before switching
   - **Mitigation**: Have rollback plan ready

3. **Missing Dependencies** - Risk of breaking functionality
   - **Mitigation**: Identify all OpenEMR functions we actually use
   - **Mitigation**: Replicate those functions in SanctumEMHR classes
   - **Mitigation**: Comprehensive testing of all endpoints

### Medium Risk Items

1. **Session Compatibility** - New session system may not be compatible
   - **Mitigation**: Test session handling thoroughly
   - **Mitigation**: Use same session configuration as OpenEMR initially

2. **Database Performance** - New queries may be slower
   - **Mitigation**: Add indexes to new tables
   - **Mitigation**: Monitor query performance
   - **Mitigation**: Optimize slow queries

### Low Risk Items

1. **Frontend Changes** - Minimal changes to React code
   - **Mitigation**: Only removing unused constants

---

## Timeline Estimate

| Phase | Duration | Tasks |
|-------|----------|-------|
| Phase 1: Infrastructure | 3-5 days | Create Database class, Auth class, bootstrap file |
| Phase 2: Schema Migration | 3-5 days | Create new database, migrate data |
| Phase 3: Update API Files | 5-7 days | Update all 49 API files |
| Phase 4: Testing | 5-7 days | Test all endpoints, verify data |
| Phase 5: Cleanup | 2-3 days | Remove OpenEMR files, update docs |
| **TOTAL** | **18-27 days** | **~4-5 weeks** |

---

## Next Steps

1. **Review this plan** - Approve the approach
2. **Set up new database** - Create `mindline` database
3. **Create infrastructure files** - Database.php, Auth.php, bootstrap.php
4. **Test infrastructure** - Verify new files work
5. **Migrate schema** - Create new tables
6. **Migrate data** - Copy OpenEMR data to SanctumEMHR tables
7. **Update API files** - Replace globals.php (one by one)
8. **Test each API** - Verify functionality
9. **Update React app** - Remove OpenEMR constants
10. **Final testing** - End-to-end tests
11. **Remove OpenEMR** - Delete old directories
12. **Celebrate** 🎉

---

## Questions to Answer Before Starting

1. **New database name?** Suggested: `mindline` (vs current OpenEMR database)
2. **Keep existing data?** Yes - migration script will copy all data
3. **Parallel systems?** Run both systems side-by-side during migration?
4. **Rollback plan?** Keep OpenEMR backup for X days after migration?
5. **Testing environment?** Have separate dev/staging environment for testing?

---

## Ready to Start?

Once you approve this plan, we'll begin with:
1. Creating `/custom/lib/Database/Database.php`
2. Creating `/custom/bootstrap.php`
3. Creating `/custom/lib/Auth/Auth.php`

Then we'll test these new files work before touching the 49 API files.

**Sound good?**
