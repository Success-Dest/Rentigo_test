#!/bin/bash
echo "========================================"
echo "Importing Rentigo Database with Fixes"
echo "========================================"

# Set database credentials
DB_USER="root"
DB_PASS="root"
DB_NAME="rentigo_db"

# Import the main database
echo "Step 1: Importing main database schema..."
mysql -u $DB_USER -p$DB_PASS -e "SET FOREIGN_KEY_CHECKS = 0; SOURCE /Applications/MAMP/htdocs/Rentigo_test/dev/rentigo_final_db.sql; SET FOREIGN_KEY_CHECKS = 1;" 2>&1

if [ $? -eq 0 ]; then
    echo "✓ Main database imported successfully"
else
    echo "✗ Error importing main database"
    exit 1
fi

# Apply schema fixes
echo "Step 2: Applying schema fixes..."
mysql -u $DB_USER -p$DB_PASS $DB_NAME < /Applications/MAMP/htdocs/Rentigo_test/dev/fix_schema.sql 2>&1

if [ $? -eq 0 ]; then
    echo "✓ Schema fixes applied successfully"
else
    echo "✗ Error applying schema fixes"
    exit 1
fi

echo "========================================"
echo "Database import and fix completed!"
echo "========================================"
