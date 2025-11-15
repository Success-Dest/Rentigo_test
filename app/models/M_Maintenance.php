<?php

/*
    MAINTENANCE MODEL
    Handles maintenance request operations
*/

class M_Maintenance
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new maintenance request
    public function createMaintenanceRequest($data)
    {
        $this->db->query('INSERT INTO maintenance_requests (property_id, landlord_id, issue_id, requester_id, title, description, category, priority, status, estimated_cost, notes)
                         VALUES (:property_id, :landlord_id, :issue_id, :requester_id, :title, :description, :category, :priority, :status, :estimated_cost, :notes)');

        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':issue_id', $data['issue_id'] ?? null);
        $this->db->bind(':requester_id', $data['requester_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':estimated_cost', $data['estimated_cost'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? '');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get maintenance request by ID
    public function getMaintenanceById($id)
    {
        $this->db->query('SELECT m.*,
                         p.address as property_address, p.property_type,
                         l.name as landlord_name, l.email as landlord_email,
                         r.name as requester_name, r.email as requester_email, r.user_type as requester_type,
                         sp.name as provider_name, sp.email as provider_email, sp.phone as provider_phone
                         FROM maintenance_requests m
                         LEFT JOIN properties p ON m.property_id = p.id
                         LEFT JOIN users l ON m.landlord_id = l.id
                         LEFT JOIN users r ON m.requester_id = r.id
                         LEFT JOIN service_providers sp ON m.provider_id = sp.id
                         WHERE m.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all maintenance requests by property
    public function getMaintenanceByProperty($property_id)
    {
        $this->db->query('SELECT m.*,
                         r.name as requester_name,
                         sp.name as provider_name
                         FROM maintenance_requests m
                         LEFT JOIN users r ON m.requester_id = r.id
                         LEFT JOIN service_providers sp ON m.provider_id = sp.id
                         WHERE m.property_id = :property_id
                         ORDER BY m.created_at DESC');
        $this->db->bind(':property_id', $property_id);
        return $this->db->resultSet();
    }

    // Get all maintenance requests by landlord
    public function getMaintenanceByLandlord($landlord_id, $status = null)
    {
        $query = 'SELECT m.*,
                  p.address as property_address,
                  r.name as requester_name,
                  sp.name as provider_name,
                  (SELECT mq2.status FROM maintenance_quotations mq2 WHERE mq2.request_id = m.id AND mq2.status != "rejected" ORDER BY mq2.created_at DESC LIMIT 1) as quotation_status,
                  (SELECT mq2.amount FROM maintenance_quotations mq2 WHERE mq2.request_id = m.id AND mq2.status != "rejected" ORDER BY mq2.created_at DESC LIMIT 1) as quotation_amount,
                  (SELECT mp2.status FROM maintenance_payments mp2
                   INNER JOIN maintenance_quotations mq3 ON mp2.quotation_id = mq3.id
                   WHERE mq3.request_id = m.id
                   ORDER BY mp2.payment_date DESC LIMIT 1) as payment_status
                  FROM maintenance_requests m
                  LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users r ON m.requester_id = r.id
                  LEFT JOIN service_providers sp ON m.provider_id = sp.id
                  WHERE m.landlord_id = :landlord_id';

        if ($status) {
            $query .= ' AND m.status = :status';
        }

        $query .= ' ORDER BY m.created_at DESC';

        $this->db->query($query);
        $this->db->bind(':landlord_id', $landlord_id);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    // Get all maintenance requests by property manager
    public function getMaintenanceByManager($manager_id, $status = null)
    {
        $query = 'SELECT m.*,
                  p.address as property_address,
                  r.name as requester_name,
                  sp.name as provider_name,
                  l.name as landlord_name,
                  (SELECT mq2.status FROM maintenance_quotations mq2 WHERE mq2.request_id = m.id AND mq2.status != "rejected" ORDER BY mq2.created_at DESC LIMIT 1) as quotation_status,
                  (SELECT mq2.amount FROM maintenance_quotations mq2 WHERE mq2.request_id = m.id AND mq2.status != "rejected" ORDER BY mq2.created_at DESC LIMIT 1) as quotation_amount,
                  (SELECT mp2.status FROM maintenance_payments mp2
                   INNER JOIN maintenance_quotations mq3 ON mp2.quotation_id = mq3.id
                   WHERE mq3.request_id = m.id
                   ORDER BY mp2.payment_date DESC LIMIT 1) as payment_status
                  FROM maintenance_requests m
                  INNER JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users r ON m.requester_id = r.id
                  LEFT JOIN service_providers sp ON m.provider_id = sp.id
                  LEFT JOIN users l ON m.landlord_id = l.id
                  WHERE p.manager_id = :manager_id';

        if ($status) {
            $query .= ' AND m.status = :status';
        }

        $query .= ' ORDER BY m.created_at DESC';

        $this->db->query($query);
        $this->db->bind(':manager_id', $manager_id);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    // Update maintenance request status
    public function updateMaintenanceStatus($id, $status)
    {
        $this->db->query('UPDATE maintenance_requests SET status = :status, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    // Assign service provider to maintenance request
    public function assignProvider($id, $provider_id, $scheduled_date = null)
    {
        $this->db->query('UPDATE maintenance_requests SET provider_id = :provider_id, scheduled_date = :scheduled_date, status = "scheduled", updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':provider_id', $provider_id);
        $this->db->bind(':scheduled_date', $scheduled_date);
        return $this->db->execute();
    }

    // Update maintenance cost
    public function updateMaintenanceCost($id, $estimated_cost, $actual_cost = null)
    {
        $this->db->query('UPDATE maintenance_requests SET estimated_cost = :estimated_cost, actual_cost = :actual_cost, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':estimated_cost', $estimated_cost);
        $this->db->bind(':actual_cost', $actual_cost);
        return $this->db->execute();
    }

    // Complete maintenance request
    public function completeMaintenance($id, $actual_cost, $completion_notes = '')
    {
        $this->db->query('UPDATE maintenance_requests SET
                         status = "completed",
                         actual_cost = :actual_cost,
                         completion_date = NOW(),
                         completion_notes = :completion_notes,
                         updated_at = NOW()
                         WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':actual_cost', $actual_cost);
        $this->db->bind(':completion_notes', $completion_notes);
        return $this->db->execute();
    }

    // Cancel maintenance request
    public function cancelMaintenance($id, $cancellation_reason)
    {
        $this->db->query('UPDATE maintenance_requests SET status = "cancelled", cancellation_reason = :cancellation_reason, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':cancellation_reason', $cancellation_reason);
        return $this->db->execute();
    }

    // Get maintenance statistics
    public function getMaintenanceStats($landlord_id = null, $manager_id = null)
    {
        if ($manager_id) {
            $query = 'SELECT
                      COUNT(*) as total,
                      SUM(CASE WHEN m.status = "pending" THEN 1 ELSE 0 END) as pending,
                      SUM(CASE WHEN m.status = "scheduled" THEN 1 ELSE 0 END) as scheduled,
                      SUM(CASE WHEN m.status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                      SUM(CASE WHEN m.status = "completed" THEN 1 ELSE 0 END) as completed,
                      SUM(CASE WHEN m.status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                      SUM(CASE WHEN m.priority = "emergency" THEN 1 ELSE 0 END) as emergency,
                      SUM(CASE WHEN m.provider_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM maintenance_quotations mq WHERE mq.request_id = m.id AND mq.status != "rejected") THEN 1 ELSE 0 END) as quotation_needed,
                      SUM(mp.amount) as total_cost,
                      AVG(mp.amount) as avg_cost
                      FROM maintenance_requests m
                      LEFT JOIN properties p ON m.property_id = p.id
                      LEFT JOIN maintenance_payments mp ON m.id = mp.request_id
                      WHERE p.manager_id = :manager_id';
        } else {
            $query = 'SELECT
                      COUNT(*) as total,
                      SUM(CASE WHEN m.status = "pending" THEN 1 ELSE 0 END) as pending,
                      SUM(CASE WHEN m.status = "scheduled" THEN 1 ELSE 0 END) as scheduled,
                      SUM(CASE WHEN m.status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                      SUM(CASE WHEN m.status = "completed" THEN 1 ELSE 0 END) as completed,
                      SUM(CASE WHEN m.status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                      SUM(CASE WHEN m.priority = "emergency" THEN 1 ELSE 0 END) as emergency,
                      SUM(mp.amount) as total_cost,
                      AVG(mp.amount) as avg_cost
                      FROM maintenance_requests m
                      LEFT JOIN maintenance_payments mp ON m.id = mp.request_id';

            if ($landlord_id) {
                $query .= ' WHERE m.landlord_id = :landlord_id';
            }
        }

        $this->db->query($query);

        if ($landlord_id) {
            $this->db->bind(':landlord_id', $landlord_id);
        } else if ($manager_id) {
            $this->db->bind(':manager_id', $manager_id);
        }

        return $this->db->single();
    }

    // Get pending maintenance count
    public function getPendingMaintenanceCount($landlord_id = null, $manager_id = null)
    {
        if ($manager_id) {
            $query = 'SELECT COUNT(*) as count
                      FROM maintenance_requests m
                      LEFT JOIN properties p ON m.property_id = p.id
                      WHERE p.manager_id = :manager_id AND m.status = "pending"';
        } else {
            $query = 'SELECT COUNT(*) as count FROM maintenance_requests';
            if ($landlord_id) {
                $query .= ' WHERE landlord_id = :landlord_id AND status = "pending"';
            } else {
                $query .= ' WHERE status = "pending"';
            }
        }

        $this->db->query($query);

        if ($landlord_id) {
            $this->db->bind(':landlord_id', $landlord_id);
        } else if ($manager_id) {
            $this->db->bind(':manager_id', $manager_id);
        }

        $result = $this->db->single();
        return $result->count;
    }

    // Get recent maintenance requests (for dashboard)
    public function getRecentMaintenance($landlord_id = null, $manager_id = null, $limit = 10)
    {
        $query = 'SELECT m.*,
                  p.address as property_address,
                  r.name as requester_name,
                  sp.name as provider_name
                  FROM maintenance_requests m
                  LEFT JOIN properties p ON m.property_id = p.id
                  LEFT JOIN users r ON m.requester_id = r.id
                  LEFT JOIN service_providers sp ON m.provider_id = sp.id';

        if ($landlord_id) {
            $query .= ' WHERE m.landlord_id = :landlord_id';
        } else if ($manager_id) {
            $query .= ' WHERE p.manager_id = :manager_id';
        }

        $query .= ' ORDER BY m.created_at DESC LIMIT :limit';

        $this->db->query($query);

        if ($landlord_id) {
            $this->db->bind(':landlord_id', $landlord_id);
        } else if ($manager_id) {
            $this->db->bind(':manager_id', $manager_id);
        }

        $this->db->bind(':limit', $limit);

        return $this->db->resultSet();
    }

    // Update maintenance request
    public function updateMaintenance($id, $data)
    {
        $this->db->query('UPDATE maintenance_requests SET
                         title = :title,
                         description = :description,
                         category = :category,
                         priority = :priority,
                         estimated_cost = :estimated_cost,
                         notes = :notes,
                         updated_at = NOW()
                         WHERE id = :id');

        $this->db->bind(':id', $id);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':estimated_cost', $data['estimated_cost'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? '');

        return $this->db->execute();
    }

    // Get all maintenance requests (for admin)
    public function getAllMaintenance()
    {
        $this->db->query('SELECT m.*,
                         p.address as property_address,
                         l.name as landlord_name,
                         r.name as requester_name,
                         sp.name as provider_name
                         FROM maintenance_requests m
                         LEFT JOIN properties p ON m.property_id = p.id
                         LEFT JOIN users l ON m.landlord_id = l.id
                         LEFT JOIN users r ON m.requester_id = r.id
                         LEFT JOIN service_providers sp ON m.provider_id = sp.id
                         ORDER BY m.created_at DESC');
        return $this->db->resultSet();
    }

    // Delete maintenance request
    public function deleteMaintenance($id)
    {
        $this->db->query('DELETE FROM maintenance_requests WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Alias for getAllMaintenance (used by Manager controller)
    public function getAllMaintenanceRequests()
    {
        return $this->getAllMaintenance();
    }
}
