<?php

class M_MaintenanceQuotations
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new quotation
    public function createQuotation($data)
    {
        $this->db->query('INSERT INTO maintenance_quotations
                         (request_id, provider_id, uploaded_by, amount, description, quotation_file, status)
                         VALUES
                         (:request_id, :provider_id, :uploaded_by, :amount, :description, :quotation_file, :status)');

        $this->db->bind(':request_id', $data['request_id']);
        $this->db->bind(':provider_id', $data['provider_id']);
        $this->db->bind(':uploaded_by', $data['uploaded_by']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':quotation_file', $data['quotation_file'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'pending');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Get quotation by ID
    public function getQuotationById($id)
    {
        $this->db->query('SELECT q.*,
                         m.title as request_title,
                         sp.name as provider_name, sp.company as provider_company,
                         u.name as uploaded_by_name
                         FROM maintenance_quotations q
                         LEFT JOIN maintenance_requests m ON q.request_id = m.id
                         LEFT JOIN service_providers sp ON q.provider_id = sp.id
                         LEFT JOIN users u ON q.uploaded_by = u.id
                         WHERE q.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get quotations by maintenance request
    public function getQuotationsByRequest($request_id)
    {
        $this->db->query('SELECT q.*,
                         sp.name as provider_name, sp.company as provider_company,
                         u.name as uploaded_by_name
                         FROM maintenance_quotations q
                         LEFT JOIN service_providers sp ON q.provider_id = sp.id
                         LEFT JOIN users u ON q.uploaded_by = u.id
                         WHERE q.request_id = :request_id
                         ORDER BY q.created_at DESC');
        $this->db->bind(':request_id', $request_id);
        return $this->db->resultSet();
    }

    // Get pending quotations by landlord
    public function getPendingQuotationsByLandlord($landlord_id)
    {
        $this->db->query('SELECT q.*,
                         m.title as request_title,
                         p.address as property_address,
                         sp.name as provider_name, sp.company as provider_company
                         FROM maintenance_quotations q
                         LEFT JOIN maintenance_requests m ON q.request_id = m.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         LEFT JOIN service_providers sp ON q.provider_id = sp.id
                         WHERE m.landlord_id = :landlord_id AND q.status = "pending"
                         ORDER BY q.created_at DESC');
        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->resultSet();
    }

    // Approve quotation
    public function approveQuotation($id, $approved_by)
    {
        $this->db->query('UPDATE maintenance_quotations
                         SET status = "approved",
                             approved_at = NOW(),
                             approved_by = :approved_by,
                             updated_at = NOW()
                         WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':approved_by', $approved_by);
        return $this->db->execute();
    }

    // Reject quotation
    public function rejectQuotation($id, $rejection_reason)
    {
        $this->db->query('UPDATE maintenance_quotations
                         SET status = "rejected",
                             rejection_reason = :rejection_reason,
                             updated_at = NOW()
                         WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':rejection_reason', $rejection_reason);
        return $this->db->execute();
    }

    // Create payment record
    public function createPayment($data)
    {
        $this->db->query('INSERT INTO maintenance_payments
                         (request_id, quotation_id, landlord_id, amount, payment_method, transaction_id, status, payment_date, notes)
                         VALUES
                         (:request_id, :quotation_id, :landlord_id, :amount, :payment_method, :transaction_id, :status, :payment_date, :notes)');

        $this->db->bind(':request_id', $data['request_id']);
        $this->db->bind(':quotation_id', $data['quotation_id']);
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_method', $data['payment_method']);
        $this->db->bind(':transaction_id', $data['transaction_id'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'completed');
        $this->db->bind(':payment_date', $data['payment_date'] ?? date('Y-m-d H:i:s'));
        $this->db->bind(':notes', $data['notes'] ?? '');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Get payment by quotation
    public function getPaymentByQuotation($quotation_id)
    {
        $this->db->query('SELECT * FROM maintenance_payments WHERE quotation_id = :quotation_id');
        $this->db->bind(':quotation_id', $quotation_id);
        return $this->db->single();
    }

    // Get payment by request
    public function getPaymentByRequest($request_id)
    {
        $this->db->query('SELECT * FROM maintenance_payments WHERE request_id = :request_id');
        $this->db->bind(':request_id', $request_id);
        return $this->db->single();
    }

    // Check if quotation is paid
    public function isQuotationPaid($quotation_id)
    {
        $payment = $this->getPaymentByQuotation($quotation_id);
        return $payment && $payment->status === 'completed';
    }

    // Get total maintenance income (for admin)
    public function getTotalMaintenanceIncome()
    {
        $this->db->query('SELECT SUM(amount) as total_income FROM maintenance_payments WHERE status = "completed"');
        $result = $this->db->single();
        return $result->total_income ?? 0;
    }

    // Delete quotation
    public function deleteQuotation($id)
    {
        $this->db->query('DELETE FROM maintenance_quotations WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Get all maintenance payments with details for dashboard
    public function getAllMaintenancePayments()
    {
        $this->db->query('SELECT
            mp.*,
            mr.title as maintenance_title,
            p.address as property_address,
            u.name as landlord_name,
            "maintenance" as payment_type
            FROM maintenance_payments mp
            LEFT JOIN maintenance_quotations mq ON mp.quotation_id = mq.id
            LEFT JOIN maintenance_requests mr ON mq.request_id = mr.id
            LEFT JOIN properties p ON mr.property_id = p.id
            LEFT JOIN users u ON mr.landlord_id = u.id
            ORDER BY mp.payment_date DESC');
        return $this->db->resultSet();
    }
}
