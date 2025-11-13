<?php
class M_ManagerProperties
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get all properties assigned to a manager WITH OWNER NAME
    public function getAssignedProperties($manager_id)
    {
        $this->db->query(
            "SELECT p.*, u.name AS owner_name
             FROM properties p
             JOIN users u ON p.landlord_id = u.id
             WHERE p.landlord_id = :manager_id
             ORDER BY p.id DESC"
        );
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet();
    }

    // Get a single property by id, only if assigned to this manager
    public function getPropertyById($id, $manager_id)
    {
        $this->db->query(
            "SELECT p.*
             FROM properties p
             WHERE p.id = :id
               AND p.landlord_id = :manager_id
             LIMIT 1"
        );
        $this->db->bind(':id', $id);
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->single();
    }
}
