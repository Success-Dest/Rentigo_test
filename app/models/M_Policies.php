<?php

class M_Policies
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Get all policies with optional filters
    public function getAllPolicies($filters = [])
    {
        $sql = "SELECT p.*, u.name as created_by_name 
                FROM policies p 
                LEFT JOIN users u ON p.created_by = u.id";

        $conditions = [];
        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $conditions[] = "p.policy_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $conditions[] = "p.policy_category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.policy_name LIKE :search OR p.policy_description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY p.created_at DESC";

        $this->db->query($sql);

        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->resultSet();
    }

    // Get policy by ID
    public function getPolicyById($policyId)
    {
        $this->db->query("SELECT p.*, u.name as created_by_name 
                         FROM policies p 
                         LEFT JOIN users u ON p.created_by = u.id 
                         WHERE p.policy_id = :policy_id");
        $this->db->bind(':policy_id', $policyId);
        return $this->db->single();
    }

    // Create new policy
    public function createPolicy($data)
    {
        $this->db->query("INSERT INTO policies (
            policy_name,
            policy_category,
            policy_content,
            policy_version,
            policy_status,
            effective_date,
            created_by
        ) VALUES (
            :policy_name,
            :policy_category,
            :policy_content,
            :policy_version,
            :policy_status,
            :effective_date,
            :created_by
        )");

        // Bind values
        $this->db->bind(':policy_name', $data['policy_name']);
        $this->db->bind(':policy_category', $data['policy_category']);
        $this->db->bind(':policy_content', $data['policy_content']);
        $this->db->bind(':policy_version', $data['policy_version'] ?? '1.0');
        $this->db->bind(':policy_status', $data['policy_status'] ?? 'draft');
        $this->db->bind(':effective_date', $data['effective_date'] ?? date('Y-m-d'));
        $this->db->bind(':created_by', $data['created_by']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Update policy
    public function updatePolicy($data)
    {
        $this->db->query("UPDATE policies
                         SET policy_name = :policy_name,
                             policy_category = :policy_category,
                             policy_content = :policy_content,
                             policy_version = :policy_version,
                             policy_status = :policy_status,
                             effective_date = :effective_date
                         WHERE policy_id = :policy_id");

        // Bind values - policy_id comes from $data array
        $this->db->bind(':policy_id', $data['policy_id']);
        $this->db->bind(':policy_name', $data['policy_name']);
        $this->db->bind(':policy_category', $data['policy_category']);
        $this->db->bind(':policy_content', $data['policy_content']);
        $this->db->bind(':policy_version', $data['policy_version']);
        $this->db->bind(':policy_status', $data['policy_status']);
        $this->db->bind(':effective_date', $data['effective_date']);

        return $this->db->execute();
    }

    // Update policy status
    public function updatePolicyStatus($policyId, $status)
    {
        $this->db->query("UPDATE policies SET
            policy_status = :status
        WHERE policy_id = :policy_id");

        $this->db->bind(':policy_id', $policyId);
        $this->db->bind(':status', $status);

        return $this->db->execute();
    }

    // Delete policy
    public function deletePolicy($policyId)
    {
        $this->db->query("DELETE FROM policies WHERE policy_id = :policy_id");
        $this->db->bind(':policy_id', $policyId);
        return $this->db->execute();
    }

    // Get policy statistics
    public function getPolicyStats()
    {
        // Total policies
        $this->db->query("SELECT COUNT(*) as total FROM policies");
        $total = $this->db->single()->total;

        // Active policies
        $this->db->query("SELECT COUNT(*) as active FROM policies WHERE policy_status = 'active'");
        $active = $this->db->single()->active;

        // Draft policies
        $this->db->query("SELECT COUNT(*) as draft FROM policies WHERE policy_status = 'draft'");
        $draft = $this->db->single()->draft;

        // Inactive policies
        $this->db->query("SELECT COUNT(*) as inactive FROM policies WHERE policy_status = 'inactive'");
        $inactive = $this->db->single()->inactive;

        // Latest update
        $this->db->query("SELECT MAX(last_updated) as last_updated FROM policies");
        $lastUpdated = $this->db->single()->last_updated;

        return [
            'total' => $total,
            'active' => $active,
            'draft' => $draft,
            'inactive' => $inactive,
            'last_updated' => $lastUpdated
        ];
    }

    // Get policies by category
    public function getPoliciesByCategory($category)
    {
        $this->db->query("SELECT * FROM policies WHERE policy_category = :category ORDER BY policy_name ASC");
        $this->db->bind(':category', $category);
        return $this->db->resultSet();
    }

    // Get active policies only
    public function getActivePolicies()
    {
        $this->db->query("SELECT * FROM policies WHERE policy_status = 'active' ORDER BY policy_name ASC");
        return $this->db->resultSet();
    }

    // Check if policy name exists (for validation)
    public function policyNameExists($policyName, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM policies WHERE policy_name = :policy_name";
        $params = [':policy_name' => $policyName];

        if ($excludeId) {
            $sql .= " AND policy_id != :policy_id";
            $params[':policy_id'] = $excludeId;
        }

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        $result = $this->db->single();
        return $result->count > 0;
    }

    // Search policies
    public function searchPolicies($searchTerm)
    {
        $this->db->query("SELECT p.*, u.name as created_by_name
                         FROM policies p
                         LEFT JOIN users u ON p.created_by = u.id
                         WHERE p.policy_name LIKE :search
                            OR p.policy_content LIKE :search
                         ORDER BY p.last_updated DESC");

        $this->db->bind(':search', '%' . $searchTerm . '%');
        return $this->db->resultSet();
    }

    // Get policy categories
    public function getPolicyCategories()
    {
        return [
            'rental' => 'Rental',
            'security' => 'Security',
            'maintenance' => 'Maintenance',
            'financial' => 'Financial',
            'general' => 'General'
        ];
    }

    // Get policy types
    public function getPolicyTypes()
    {
        return [
            'standard' => 'Standard',
            'custom' => 'Custom'
        ];
    }

    // Get policy statuses
    public function getPolicyStatuses()
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'inactive' => 'Inactive'
        ];
    }
}
