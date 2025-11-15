-- Update all draft lease agreements to active status
-- This fixes existing leases that were created before the status update
UPDATE lease_agreements SET status = 'active' WHERE status = 'draft';

-- Verify the update
SELECT
    COUNT(*) as total_leases,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_leases,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_leases
FROM lease_agreements;
