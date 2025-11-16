# Database Migrations for Rentigo

## How to Apply Migrations

### Using MySQL Command Line:
```bash
mysql -u root -p rentigo_db < /path/to/migration/file.sql
```

### Using phpMyAdmin:
1. Open phpMyAdmin
2. Select `rentigo_db` database
3. Click on "SQL" tab
4. Copy and paste the migration SQL
5. Click "Go"

### Using MAMP/XAMPP MySQL:
1. Open the MySQL command line
2. USE rentigo_db;
3. SOURCE /path/to/migration/file.sql;

---

## Migration 002: Update Issues Table

**File**: `002_update_issues_table.sql`
**Date**: 2025-01-16
**Required for**: Issue Tracking Module (Todo #7)

### What it does:
- Adds columns for linking issues to maintenance requests and inspections
- Adds resolution tracking (notes, resolved timestamp)
- Adds assignment tracking (PM, landlord)
- Adds notification tracking flags
- Updates existing issues with landlord_id from properties

### Columns Added:
- `maintenance_request_id` - Links issue to maintenance request
- `inspection_id` - Links issue to inspection
- `resolution_notes` - PM's notes on how issue was resolved
- `assigned_to` - PM assigned to handle the issue
- `landlord_id` - Landlord of the property (auto-populated)
- `resolved_at` - Timestamp when issue was resolved
- `pm_notified` - Flag indicating PM was notified
- `landlord_notified` - Flag indicating landlord was notified

### Dependencies:
- Requires `issues` table to exist
- Requires `properties` table to have `landlord_id` column
- Requires `maintenance_requests` table (optional, for foreign keys)
- Requires `inspections` table (optional, for foreign keys)

### To Apply:
```sql
SOURCE /Applications/MAMP/htdocs/Rentigo_test/dev/migrations/002_update_issues_table.sql;
```

Or copy the contents and run in your MySQL client.

---

## Verification

After applying the migration, verify with:

```sql
DESCRIBE issues;
```

You should see all the new columns listed above.

## Rollback

If you need to rollback this migration:

```sql
ALTER TABLE `issues`
DROP COLUMN `maintenance_request_id`,
DROP COLUMN `inspection_id`,
DROP COLUMN `resolution_notes`,
DROP COLUMN `assigned_to`,
DROP COLUMN `landlord_id`,
DROP COLUMN `resolved_at`,
DROP COLUMN `pm_notified`,
DROP COLUMN `landlord_notified`;
```

**Warning**: This will delete all data in these columns!
