# How to Apply the Database Migration

## ⚠️ CRITICAL: This fixes all your CRUD errors!

The new database I created **changed your column names and enum values**, which broke your working CRUD operations. This migration **restores all your old columns** while adding new features.

## What This Migration Does

### ✅ Restores Your Working Database
- **Keeps** `current_occupant` column in properties
- **Keeps** `property_purpose` enum('rent','maintenance')
- **Keeps** `tenant` and `issue` columns
- **Keeps** `approval_status` separate from `status`
- **Keeps** `policy_description`, `policy_type`, `expiry_date`
- **Keeps** `rejection_reason` in property_manager
- **Keeps** `company` and `address` in service_providers
- **Keeps** all your enum values: 'inactive', 'rental', 'security', etc.

### ✅ Adds New Features
- Bookings system
- Lease agreements
- Payments tracking
- Messages
- Notifications
- Reviews

## Step-by-Step Instructions

### Step 1: Backup Your Database (CRITICAL!)

```bash
# Navigate to your project directory
cd /home/user/Rentigo_test

# Create backups directory
mkdir -p database/backups

# Backup your database
mysqldump -u root -p rentigo_db > database/backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh database/backups/
```

### Step 2: Apply the Migration

```bash
# Apply the in-place migration (preserves all your data)
mysql -u root -p rentigo_db < database/migrations/migrate_to_new_schema.sql
```

**Expected output:**
```
Migration completed successfully! Your old columns and enum values are preserved.
All existing CRUD operations should continue to work.
New tables have been created for enhanced features.
```

### Step 3: Verify the Migration

```bash
# Run verification script
mysql -u root -p rentigo_db < database/migrations/verify_migration.sql
```

**Expected output:**
- All checks should show `PASS ✓`
- If you see any `FAIL ✗`, check the error message

### Step 4: Test Your Application

1. **Test Properties CRUD:**
   ```php
   // These should all work now:
   - Create property with listing_type='rent'
   - Update property.current_occupant
   - Read property.tenant
   - Update property.approval_status
   ```

2. **Test Policies CRUD:**
   ```php
   // These should all work now:
   - Create policy with category='rental'
   - Create policy with category='security'
   - Update policy status to 'inactive'
   - Set expiry_date
   ```

3. **Test Property Manager:**
   ```php
   // These should all work now:
   - Reject manager with rejection_reason
   - Read property_manager.id (not manager_id)
   ```

4. **Test Service Providers:**
   ```php
   // These should all work now:
   - Create provider with company name
   - Set provider address
   ```

## Common Issues & Solutions

### Issue: "Table doesn't exist" after migration
**Solution:**
```bash
# Check if database is selected
mysql -u root -p -e "USE rentigo_db; SHOW TABLES;"
```

### Issue: "Unknown column 'current_occupant'"
**Solution:** The migration didn't complete. Re-run:
```bash
mysql -u root -p rentigo_db < database/migrations/migrate_to_new_schema.sql
```

### Issue: "Data truncated for column"
**Solution:** Check enum values. Old values are preserved:
- `listing_type`: 'rent', 'maintenance', 'rental'
- `policy_status`: 'draft', 'active', 'inactive', 'archived', 'under_review'
- `policy_category`: 'rental', 'security', 'maintenance', 'financial', 'general', 'privacy', 'terms_of_service', 'refund', 'data_protection'

## Rollback (If Needed)

If something goes wrong:

```bash
# Restore from backup
mysql -u root -p -e "DROP DATABASE rentigo_db; CREATE DATABASE rentigo_db;"
mysql -u root -p rentigo_db < database/backups/backup_YYYYMMDD_HHMMSS.sql
```

## Verification Checklist

After migration, verify these work:

- [ ] Create property with `listing_type='rent'`
- [ ] Update `properties.current_occupant`
- [ ] Read `properties.tenant`
- [ ] Update `properties.approval_status`
- [ ] Create policy with `category='rental'`
- [ ] Update policy `status='inactive'`
- [ ] Set policy `expiry_date`
- [ ] Reject property manager with `rejection_reason`
- [ ] Create service provider with `company` and `address`
- [ ] Create issue with category 'Heating/Cooling'

## What Changed (Summary)

### Properties Table
```sql
-- KEPT (your old columns):
current_occupant VARCHAR(100)
property_purpose ENUM('rent','maintenance')
tenant VARCHAR(200)
issue TEXT
approval_status ENUM('pending','approved','rejected')
listing_type ENUM('rent','maintenance','rental') -- extended

-- Your old code continues to work!
```

### Policies Table
```sql
-- KEPT (your old columns):
policy_description TEXT
policy_type ENUM('standard','custom')
expiry_date DATE
policy_status ENUM('draft','active','inactive','archived','under_review') -- extended
policy_category ENUM('rental','security','maintenance','financial','general',...) -- extended

-- Your old code continues to work!
```

### Property Manager Table
```sql
-- KEPT (your old structure):
id INT PRIMARY KEY -- NOT manager_id
rejection_reason TEXT

-- Your old code continues to work!
```

### Service Providers Table
```sql
-- KEPT (your old columns):
company VARCHAR(100)
address TEXT

-- Your old code continues to work!
```

## Success Indicators

✅ No errors during migration
✅ All verification checks show PASS ✓
✅ Your CRUD operations work without code changes
✅ Old enum values still work (rent, maintenance, inactive, rental, etc.)
✅ Old columns exist (current_occupant, tenant, issue, rejection_reason, etc.)

## Need Help?

If you see errors:
1. Check error message for specific column/table name
2. Run verification script to see what's missing
3. Check if backup exists before retrying
4. Review your existing code for column names

## Final Notes

- **NO CODE CHANGES NEEDED** - Your existing code should work as-is
- **ALL DATA PRESERVED** - Migration only adds/extends, never deletes
- **BACKWARD COMPATIBLE** - Old values/columns still work
- **NEW FEATURES** - Access new tables for enhanced functionality

Your CRUD operations should now work perfectly! 🎉
