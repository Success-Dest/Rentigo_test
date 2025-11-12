<?php

/*
    LEASE AGREEMENTS MODEL
    Handles rental lease/contract operations
*/

class M_LeaseAgreements
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new lease agreement
    public function createLeaseAgreement($data)
    {
        $this->db->query('INSERT INTO lease_agreements (tenant_id, landlord_id, property_id, booking_id, start_date, end_date, monthly_rent, deposit_amount, terms_and_conditions, status, lease_duration_months)
                         VALUES (:tenant_id, :landlord_id, :property_id, :booking_id, :start_date, :end_date, :monthly_rent, :deposit_amount, :terms_and_conditions, :status, :lease_duration_months)');

        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':booking_id', $data['booking_id']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        $this->db->bind(':monthly_rent', $data['monthly_rent']);
        $this->db->bind(':deposit_amount', $data['deposit_amount']);
        $this->db->bind(':terms_and_conditions', $data['terms_and_conditions']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':lease_duration_months', $data['lease_duration_months']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get lease agreement by ID
    public function getLeaseById($id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users t ON la.tenant_id = t.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         WHERE la.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get lease agreement by booking ID
    public function getLeaseByBookingId($booking_id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users t ON la.tenant_id = t.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         WHERE la.booking_id = :booking_id');
        $this->db->bind(':booking_id', $booking_id);
        return $this->db->single();
    }

    // Get all lease agreements by tenant
    public function getLeasesByTenant($tenant_id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         l.name as landlord_name, l.email as landlord_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         WHERE la.tenant_id = :tenant_id
                         ORDER BY la.created_at DESC');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Get all lease agreements by landlord
    public function getLeasesByLandlord($landlord_id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users t ON la.tenant_id = t.id
                         WHERE la.landlord_id = :landlord_id
                         ORDER BY la.created_at DESC');
        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->resultSet();
    }

    // Get all lease agreements by property
    public function getLeasesByProperty($property_id)
    {
        $this->db->query('SELECT la.*,
                         t.name as tenant_name, t.email as tenant_email
                         FROM lease_agreements la
                         LEFT JOIN users t ON la.tenant_id = t.id
                         WHERE la.property_id = :property_id
                         ORDER BY la.created_at DESC');
        $this->db->bind(':property_id', $property_id);
        return $this->db->resultSet();
    }

    // Update lease agreement status
    public function updateLeaseStatus($id, $status)
    {
        $this->db->query('UPDATE lease_agreements SET status = :status, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    // Sign lease agreement by tenant
    public function signLeaseByTenant($id, $signature_data = null)
    {
        $this->db->query('UPDATE lease_agreements SET signed_by_tenant = 1, tenant_signature_date = NOW(), updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Sign lease agreement by landlord
    public function signLeaseByLandlord($id, $signature_data = null)
    {
        $this->db->query('UPDATE lease_agreements SET signed_by_landlord = 1, landlord_signature_date = NOW(), updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Terminate lease agreement
    public function terminateLease($id, $termination_reason, $termination_date)
    {
        $this->db->query('UPDATE lease_agreements SET status = "terminated", termination_reason = :termination_reason, termination_date = :termination_date, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':termination_reason', $termination_reason);
        $this->db->bind(':termination_date', $termination_date);
        return $this->db->execute();
    }

    // Get active lease for a tenant
    public function getActiveLeaseByTenant($tenant_id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         l.name as landlord_name, l.email as landlord_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         WHERE la.tenant_id = :tenant_id AND la.status = "active"
                         ORDER BY la.created_at DESC LIMIT 1');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    // Get active leases for a landlord
    public function getActiveLeasesCount($landlord_id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM lease_agreements WHERE landlord_id = :landlord_id AND status = "active"');
        $this->db->bind(':landlord_id', $landlord_id);
        $result = $this->db->single();
        return $result->count;
    }

    // Get expiring leases (within X days)
    public function getExpiringLeases($days = 30, $landlord_id = null)
    {
        $query = 'SELECT la.*,
                  p.address, p.property_type,
                  t.name as tenant_name, t.email as tenant_email,
                  l.name as landlord_name
                  FROM lease_agreements la
                  LEFT JOIN properties p ON la.property_id = p.id
                  LEFT JOIN users t ON la.tenant_id = t.id
                  LEFT JOIN users l ON la.landlord_id = l.id
                  WHERE la.status = "active" AND la.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)';

        if ($landlord_id) {
            $query .= ' AND la.landlord_id = :landlord_id';
        }

        $query .= ' ORDER BY la.end_date ASC';

        $this->db->query($query);
        $this->db->bind(':days', $days);

        if ($landlord_id) {
            $this->db->bind(':landlord_id', $landlord_id);
        }

        return $this->db->resultSet();
    }

    // Get lease statistics
    public function getLeaseStats($user_id, $user_type)
    {
        $stats = [];

        if ($user_type == 'tenant') {
            $this->db->query('SELECT
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
                            SUM(CASE WHEN status = "pending_signatures" THEN 1 ELSE 0 END) as pending_signatures,
                            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = "terminated" THEN 1 ELSE 0 END) as terminated
                            FROM lease_agreements WHERE tenant_id = :user_id');
        } else if ($user_type == 'landlord') {
            $this->db->query('SELECT
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
                            SUM(CASE WHEN status = "pending_signatures" THEN 1 ELSE 0 END) as pending_signatures,
                            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = "terminated" THEN 1 ELSE 0 END) as terminated
                            FROM lease_agreements WHERE landlord_id = :user_id');
        }

        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    // Update lease agreement
    public function updateLease($id, $data)
    {
        $this->db->query('UPDATE lease_agreements SET
                         start_date = :start_date,
                         end_date = :end_date,
                         monthly_rent = :monthly_rent,
                         deposit_amount = :deposit_amount,
                         terms_and_conditions = :terms_and_conditions,
                         lease_duration_months = :lease_duration_months,
                         updated_at = NOW()
                         WHERE id = :id');

        $this->db->bind(':id', $id);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        $this->db->bind(':monthly_rent', $data['monthly_rent']);
        $this->db->bind(':deposit_amount', $data['deposit_amount']);
        $this->db->bind(':terms_and_conditions', $data['terms_and_conditions']);
        $this->db->bind(':lease_duration_months', $data['lease_duration_months']);

        return $this->db->execute();
    }

    // Get all lease agreements (for admin or property manager)
    public function getAllLeases()
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users t ON la.tenant_id = t.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         ORDER BY la.created_at DESC');
        return $this->db->resultSet();
    }

    // Get leases by property manager
    public function getLeasesByManager($manager_id)
    {
        $this->db->query('SELECT la.*,
                         p.address, p.property_type,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name
                         FROM lease_agreements la
                         LEFT JOIN properties p ON la.property_id = p.id
                         LEFT JOIN users t ON la.tenant_id = t.id
                         LEFT JOIN users l ON la.landlord_id = l.id
                         WHERE p.manager_id = :manager_id
                         ORDER BY la.created_at DESC');
        $this->db->bind(':manager_id', $manager_id);
        return $this->db->resultSet();
    }
}
