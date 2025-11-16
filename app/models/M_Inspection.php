<?php
class M_Inspection
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Get properties that have maintenance issues
    public function getPropertiesWithIssues()
    {
        $this->db->query("
        SELECT
            p.id,
            p.address,
            COUNT(i.id) as issue_count
        FROM properties p
        INNER JOIN issues i ON p.id = i.property_id
        WHERE i.status IN ('pending', 'in_progress', 'assigned')
        GROUP BY p.id, p.address
        ORDER BY p.address ASC
    ");

        return $this->db->resultSet();
    }

    // Get all properties for dropdown (for manager)
    public function getAllPropertiesByManager($manager_id)
    {
        $this->db->query("
            SELECT id, address
            FROM properties
            WHERE manager_id = :manager_id
            ORDER BY address ASC
        ");
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet();
    }

    // Get issues by property ID
    public function getIssuesByPropertyId($property_id)
    {
        $this->db->query("
        SELECT 
            i.id,
            i.title,
            i.description,
            i.category,
            i.priority,
            i.status,
            i.created_at,
            u.name as tenant_name
        FROM issues i
        LEFT JOIN users u ON i.tenant_id = u.id
        WHERE i.property_id = :property_id
        AND i.status IN ('pending', 'in_progress', 'assigned')
        ORDER BY 
            FIELD(i.priority, 'emergency', 'high', 'medium', 'low'),
            i.created_at DESC
    ");

        $this->db->bind(':property_id', $property_id);

        return $this->db->resultSet();
    }

    // Get property by ID with landlord and tenant info
    public function getPropertyById($id)
    {
        $this->db->query("
            SELECT p.*,
                   l.name as landlord_name,
                   l.email as landlord_email,
                   t.id as tenant_id,
                   t.name as tenant_name,
                   t.email as tenant_email
            FROM properties p
            LEFT JOIN users l ON p.landlord_id = l.id
            LEFT JOIN lease_agreements la ON p.id = la.property_id AND la.status = 'active'
            LEFT JOIN users t ON la.tenant_id = t.id
            WHERE p.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Insert inspection
    public function addInspection($data)
    {
        try {
            $this->db->query("INSERT INTO inspections
                (property_id, issue_id, `type`, scheduled_date, scheduled_time, notes,
                 manager_id, landlord_id, tenant_id, status)
                VALUES (:property_id, :issue_id, :type, :date, :time, :notes,
                        :manager_id, :landlord_id, :tenant_id, 'scheduled')");

            // Bind parameters
            $this->db->bind(':property_id', $data['property_id']);
            $this->db->bind(':issue_id', $data['issue_id'] ?: null);
            $this->db->bind(':type', $data['type']);
            $this->db->bind(':date', $data['date']);
            $this->db->bind(':time', $data['time'] ?: null);
            $this->db->bind(':notes', $data['notes'] ?: null);
            $this->db->bind(':manager_id', $data['manager_id']);
            $this->db->bind(':landlord_id', $data['landlord_id'] ?: null);
            $this->db->bind(':tenant_id', $data['tenant_id'] ?: null);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            return false;
        }
    }

    // Fetch all inspections for a manager
    public function getInspectionsByManager($manager_id)
    {
        $this->db->query("
            SELECT i.*,
                   p.address as property_address,
                   l.name as landlord_name,
                   t.name as tenant_name
            FROM inspections i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users l ON i.landlord_id = l.id
            LEFT JOIN users t ON i.tenant_id = t.id
            WHERE i.manager_id = :manager_id
            ORDER BY i.scheduled_date DESC, i.scheduled_time DESC
        ");
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet();
    }

    // Fetch all inspections (for admin)
    public function getAllInspections()
    {
        $this->db->query("
            SELECT i.*,
                   p.address as property_address,
                   m.name as manager_name,
                   l.name as landlord_name,
                   t.name as tenant_name
            FROM inspections i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users m ON i.manager_id = m.id
            LEFT JOIN users l ON i.landlord_id = l.id
            LEFT JOIN users t ON i.tenant_id = t.id
            ORDER BY i.scheduled_date DESC, i.scheduled_time DESC
        ");
        return $this->db->resultSet();
    }

    // Fetch inspection by ID
    public function getInspectionById($id)
    {
        $this->db->query("
            SELECT i.*,
                   p.address as property_address,
                   l.name as landlord_name, l.email as landlord_email,
                   t.name as tenant_name, t.email as tenant_email,
                   m.name as manager_name
            FROM inspections i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN users l ON i.landlord_id = l.id
            LEFT JOIN users t ON i.tenant_id = t.id
            LEFT JOIN users m ON i.manager_id = m.id
            WHERE i.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Update inspection
    public function updateInspection($id, $data)
    {
        $this->db->query("UPDATE inspections
                          SET property_id = :property_id,
                              `type` = :type,
                              scheduled_date = :date,
                              scheduled_time = :time,
                              notes = :notes,
                              inspection_notes = :inspection_notes,
                              status = :status,
                              issue_id = :issue_id
                          WHERE id = :id");

        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':time', $data['time'] ?: null);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':inspection_notes', $data['inspection_notes'] ?: null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':issue_id', $data['issue_id'] ?: null);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    // Delete inspection
    public function deleteInspection($id)
    {
        $this->db->query("DELETE FROM inspections WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
