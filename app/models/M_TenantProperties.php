<?php
class M_TenantProperties
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get all approved and available properties for tenants
    public function getApprovedProperties()
    {
        $this->db->query(
            "SELECT *
             FROM properties
             WHERE approval_status = 'approved'
               AND status = 'available'
             ORDER BY created_at DESC"
        );
        return $this->db->resultSet();
    }

    // Get a single property by id (only if approved and available)
    public function getPropertyById($id)
    {
        $this->db->query(
            "SELECT *
             FROM properties
             WHERE id = :id
               AND approval_status = 'approved'
               AND status = 'available'
             LIMIT 1"
        );
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Optional: filter/search with more advanced logic
    public function searchProperties($filters)
    {
        $sql = "SELECT * FROM properties WHERE approval_status = 'approved' AND status = 'available'";
        $params = [];

        if (!empty($filters['location'])) {
            $sql .= " AND address LIKE :location";
            $params[':location'] = '%' . $filters['location'] . '%';
        }
        if (!empty($filters['min_price'])) {
            $sql .= " AND rent >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND rent <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND property_type = :type";
            $params[':type'] = $filters['type'];
        }
        $sql .= " ORDER BY created_at DESC";

        $this->db->query($sql);
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }

        return $this->db->resultSet();
    }
}
