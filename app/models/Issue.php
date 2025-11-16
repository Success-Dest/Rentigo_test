<?php
class Issue
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function addIssue($data)
    {
        $this->db->query("
            INSERT INTO issues
            (tenant_id, property_id, title, description, category, priority, status, landlord_id)
            VALUES (:tenant_id, :property_id, :title, :description, :category, :priority, :status, :landlord_id)
        ");

        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':landlord_id', $data['landlord_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getIssuesByTenant($tenant_id)
    {
        $this->db->query("
            SELECT i.*, p.address AS property_address    
            FROM issues i
            JOIN properties p ON i.property_id = p.id
            WHERE i.tenant_id = :tenant_id
            ORDER BY i.created_at DESC
        ");

        $this->db->bind(':tenant_id', $tenant_id);

        return $this->db->resultSet();
    }

    public function getIssueById($id)
    {
        $this->db->query("
            SELECT i.*,
                   p.address AS property_address,
                   u.name AS tenant_name,
                   u.email AS tenant_email,
                   l.name AS landlord_name
            FROM issues i
            JOIN properties p ON i.property_id = p.id
            JOIN users u ON i.tenant_id = u.id
            LEFT JOIN users l ON i.landlord_id = l.id
            WHERE i.id = :id
        ");

        $this->db->bind(':id', $id);

        return $this->db->single();
    }

    public function updateIssue($data)
    {
        $this->db->query('UPDATE issues SET 
            property_id = :property_id,
            title = :title,
            description = :description,
            category = :category,
            priority = :priority,
            status = :status
            WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function deleteIssue($id)
    {
        $this->db->query('DELETE FROM issues WHERE id = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function getProperties()
    {
        $this->db->query("SELECT id, address, property_type, bedrooms, bathrooms, rent 
                          FROM properties 
                          ORDER BY address");
        return $this->db->resultSet();
    }

    public function getRecentIssuesByTenant($tenantId, $limit = 2)
    {
        $this->db->query("
            SELECT i.*, p.address as property_address 
            FROM issues i 
            LEFT JOIN properties p ON i.property_id = p.id 
            WHERE i.tenant_id = :tenant_id 
            ORDER BY i.created_at DESC 
            LIMIT :limit
        ");

        $this->db->bind(':tenant_id', $tenantId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getAllIssues()
    {
        $this->db->query("
            SELECT i.*,
                   p.address AS property_address,
                   p.property_type,
                   u.name AS tenant_name,
                   u.email AS tenant_email
            FROM issues i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users u ON i.tenant_id = u.id
            ORDER BY
                CASE
                    WHEN i.priority = 'emergency' THEN 1
                    WHEN i.priority = 'high' THEN 2
                    WHEN i.priority = 'medium' THEN 3
                    WHEN i.priority = 'low' THEN 4
                END,
                i.created_at DESC
        ");

        return $this->db->resultSet();
    }

    // Alias for getRecentIssuesByTenant (used by Tenant controller)
    public function getRecentIssues($tenantId, $limit = 5)
    {
        return $this->getRecentIssuesByTenant($tenantId, $limit);
    }

    // Get issues by property manager (assigned properties)
    public function getIssuesByManager($manager_id)
    {
        $this->db->query("
            SELECT i.*,
                   p.address AS property_address,
                   p.property_type,
                   u.name AS tenant_name,
                   u.email AS tenant_email,
                   l.name AS landlord_name
            FROM issues i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users u ON i.tenant_id = u.id
            LEFT JOIN users l ON i.landlord_id = l.id
            WHERE p.manager_id = :manager_id
            ORDER BY
                CASE
                    WHEN i.priority = 'emergency' THEN 1
                    WHEN i.priority = 'high' THEN 2
                    WHEN i.priority = 'medium' THEN 3
                    WHEN i.priority = 'low' THEN 4
                END,
                i.created_at DESC
        ");

        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet();
    }

    // Get issues by landlord
    public function getIssuesByLandlord($landlord_id)
    {
        $this->db->query("
            SELECT i.*,
                   p.address AS property_address,
                   p.property_type,
                   u.name AS tenant_name,
                   u.email AS tenant_email
            FROM issues i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users u ON i.tenant_id = u.id
            WHERE i.landlord_id = :landlord_id
            ORDER BY i.created_at DESC
        ");

        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->resultSet();
    }

    // Update issue status
    public function updateStatus($issue_id, $status, $resolution_notes = null)
    {
        $query = 'UPDATE issues SET status = :status';

        if ($resolution_notes) {
            $query .= ', resolution_notes = :resolution_notes';
        }

        if ($status === 'resolved') {
            $query .= ', resolved_at = NOW()';
        }

        $query .= ' WHERE id = :id';

        $this->db->query($query);
        $this->db->bind(':id', $issue_id);
        $this->db->bind(':status', $status);

        if ($resolution_notes) {
            $this->db->bind(':resolution_notes', $resolution_notes);
        }

        return $this->db->execute();
    }

    // Link issue to maintenance request
    public function linkToMaintenance($issue_id, $maintenance_request_id)
    {
        $this->db->query('UPDATE issues SET maintenance_request_id = :maintenance_id WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        $this->db->bind(':maintenance_id', $maintenance_request_id);
        return $this->db->execute();
    }

    // Link issue to inspection
    public function linkToInspection($issue_id, $inspection_id)
    {
        $this->db->query('UPDATE issues SET inspection_id = :inspection_id WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        $this->db->bind(':inspection_id', $inspection_id);
        return $this->db->execute();
    }

    // Mark PM as notified
    public function markPMNotified($issue_id)
    {
        $this->db->query('UPDATE issues SET pm_notified = 1 WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        return $this->db->execute();
    }

    // Mark landlord as notified
    public function markLandlordNotified($issue_id)
    {
        $this->db->query('UPDATE issues SET landlord_notified = 1 WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        return $this->db->execute();
    }

    // Get issue statistics
    public function getIssueStats($user_id = null, $user_type = null)
    {
        $whereClause = '';

        if ($user_type === 'manager') {
            $whereClause = 'LEFT JOIN properties p ON i.property_id = p.id WHERE p.manager_id = :user_id';
        } elseif ($user_type === 'landlord') {
            $whereClause = 'WHERE i.landlord_id = :user_id';
        } elseif ($user_type === 'tenant') {
            $whereClause = 'WHERE i.tenant_id = :user_id';
        }

        $this->db->query("
            SELECT
                COUNT(*) as total_issues,
                SUM(CASE WHEN i.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN i.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN i.status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN i.priority = 'emergency' THEN 1 ELSE 0 END) as emergency_count,
                SUM(CASE WHEN i.priority = 'high' THEN 1 ELSE 0 END) as high_count,
                SUM(CASE WHEN i.priority = 'medium' THEN 1 ELSE 0 END) as medium_count,
                SUM(CASE WHEN i.priority = 'low' THEN 1 ELSE 0 END) as low_count
            FROM issues i
            $whereClause
        ");

        if ($user_id && $user_type) {
            $this->db->bind(':user_id', $user_id);
        }

        return $this->db->single();
    }
}
