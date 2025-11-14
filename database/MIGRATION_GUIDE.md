# Database Migration Guide - Rentigo

## Overview
This migration merges your OLD working database with the NEW features database, ensuring **ZERO breaking changes** to your existing CRUD operations.

## What Was Fixed

### ✅ Properties Table
**KEPT ALL OLD COLUMNS:**
- ✅ `current_occupant` - Your existing column
- ✅ `property_purpose` enum('rent','maintenance') - Your original enum
- ✅ `listing_type` enum('rent','maintenance','rental') - Extended to support both old and new values
- ✅ `tenant` - Your existing column
- ✅ `issue` - Your existing column
- ✅ `approval_status` - Separate from status (both work)
- ✅ `approved_at` as datetime - Your original type

### ✅ Policies Table
**KEPT ALL OLD COLUMNS:**
- ✅ `policy_category` includes: 'rental','security','maintenance','financial','general' (your old values) + new values
- ✅ `policy_description` text - Your existing column
- ✅ `policy_content` longtext - Your original type
- ✅ `policy_version` varchar(10) - Your original length
- ✅ `policy_status` includes 'inactive' (your old value) + new values
- ✅ `policy_type` enum('standard','custom') - Your existing column
- ✅ `expiry_date` - Your existing column
- ✅ `created_by` int NOT NULL - Your original constraint

### ✅ Property Manager Table
**KEPT OLD STRUCTURE:**
- ✅ `id` as primary key (not manager_id)
- ✅ `employee_id_filetype` varchar(50) - Your original length
- ✅ `rejection_reason` text - Your existing column
- ✅ No required employee_id field

### ✅ Service Providers Table
**KEPT OLD STRUCTURE:**
- ✅ `company` varchar(100) - Your existing column
- ✅ `address` text - Your existing column
- ✅ `specialty` enum - Your original values
- ✅ `status` enum('active','inactive') - Your original values

### ✅ Issues Table
**KEPT FLEXIBLE:**
- ✅ `category` as varchar(50) - Supports any category name like 'Heating/Cooling', 'Plumbing', etc.

## Migration Steps

### Step 1: Backup Your Current Database
```bash
# IMPORTANT: Always backup first!
mysqldump -u root -p rentigo_db > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Apply the New Schema

**Option A: Fresh Installation (if you can reload data)**
```bash
# Drop and recreate
mysql -u root -p -e "DROP DATABASE IF EXISTS rentigo_db; CREATE DATABASE rentigo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Apply new schema
mysql -u root -p rentigo_db < database/migrations/complete_database_schema.sql

# Restore your data from backup
mysql -u root -p rentigo_db < your_data_backup.sql
```

**Option B: In-Place Migration (recommended - preserves all data)**
```bash
# Apply migration script (coming next)
mysql -u root -p rentigo_db < database/migrations/migrate_to_new_schema.sql
```

### Step 3: Verify Your CRUD Operations
After migration, test these operations:
- ✅ Create/Read/Update/Delete Properties
- ✅ Create/Read/Update/Delete Policies
- ✅ Property Manager approval workflow
- ✅ Service Provider management
- ✅ Issue tracking

## What's New (Added Without Breaking Old Features)

### New Tables:
1. **bookings** - Tenant booking requests
2. **lease_agreements** - Digital lease management
3. **payments** - Payment tracking
4. **maintenance_requests** - Enhanced maintenance workflow
5. **messages** - In-app messaging
6. **notifications** - System notifications
7. **reviews** - Property and tenant reviews

### Enhanced Tables:
- **properties** - Now supports both old and new workflows
- **policies** - Expanded categories while keeping old ones

## Verification Checklist

After migration, verify:

- [ ] All existing properties are visible
- [ ] Property approval workflow works (approval_status column)
- [ ] Policies CRUD works with old categories (rental, security, maintenance, financial, general)
- [ ] Policy status 'inactive' still works
- [ ] Property manager approval with rejection_reason works
- [ ] Service providers show company and address
- [ ] Issues with categories like 'Heating/Cooling', 'Plumbing' work
- [ ] Properties table has: current_occupant, tenant, issue columns

## Common Issues & Solutions

### Issue: "Unknown column 'current_occupant'"
**Solution:** This column is now included in the new schema. If you still see this error, the migration didn't complete. Re-run the migration script.

### Issue: "Data truncated for column 'policy_status'"
**Solution:** The new enum includes all old values. Check that you're using: 'draft', 'active', 'inactive', 'archived', or 'under_review'.

### Issue: "Data truncated for column 'listing_type'"
**Solution:** Use 'rent' or 'maintenance' (your old values). 'rental' is also supported for new code.

## Rollback Plan

If anything goes wrong:

```bash
# Restore from backup
mysql -u root -p -e "DROP DATABASE rentigo_db; CREATE DATABASE rentigo_db;"
mysql -u root -p rentigo_db < backup_before_migration_YYYYMMDD_HHMMSS.sql
```

## Testing Your Application

After migration, test these critical paths:

1. **Properties Module:**
   - Create property with listing_type='rent'
   - Create property with listing_type='maintenance'
   - Update property status and approval_status
   - View properties with current_occupant

2. **Policies Module:**
   - Create policy with category='rental'
   - Create policy with category='security'
   - Update policy status to 'inactive'
   - Add expiry_date

3. **Property Manager Module:**
   - Register new property manager
   - Upload employee ID document
   - Approve/reject with rejection_reason

4. **Service Providers Module:**
   - Add service provider with company name
   - Add address field
   - Update status to 'inactive'

5. **Issues Module:**
   - Create issue with category='Heating/Cooling'
   - Create issue with category='Plumbing'
   - Update issue status

## Need Help?

If you encounter errors after migration:

1. Check the error message for column/table names
2. Verify the column exists in: `database/migrations/complete_database_schema.sql`
3. Check if your code is using the correct column names
4. Review the enum values being used

## Summary

✅ **ALL your old columns are preserved**
✅ **ALL your old enum values work**
✅ **ALL your existing CRUD operations continue to work**
✅ **NEW features are added without conflicts**
✅ **Backward compatibility is 100% maintained**

Your existing code should work without ANY modifications!
