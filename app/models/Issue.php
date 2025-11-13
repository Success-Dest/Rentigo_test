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
            (tenant_id, property_id, title, description, category, priority, status) 
            VALUES (:tenant_id, :property_id, :title, :description, :category, :priority, :status)
        ");

        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
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
            SELECT i.*, p.address AS property_address, u.name AS tenant_name
            FROM issues i
            JOIN properties p ON i.property_id = p.id
            JOIN users u ON i.tenant_id = u.id
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
}
