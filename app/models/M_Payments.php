<?php

/*
    PAYMENTS MODEL
    Handles rent payment operations and transaction history
*/

class M_Payments
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new payment record
    public function createPayment($data)
    {
        $this->db->query('INSERT INTO payments (tenant_id, landlord_id, property_id, booking_id, amount, payment_method, transaction_id, status, payment_date, due_date, notes)
                         VALUES (:tenant_id, :landlord_id, :property_id, :booking_id, :amount, :payment_method, :transaction_id, :status, :payment_date, :due_date, :notes)');

        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':booking_id', $data['booking_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_method', $data['payment_method']);
        $this->db->bind(':transaction_id', $data['transaction_id']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':payment_date', $data['payment_date']);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':notes', $data['notes'] ?? '');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get payment by ID
    public function getPaymentById($id)
    {
        $this->db->query('SELECT p.*,
                         pr.address as property_address,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM payments p
                         LEFT JOIN properties pr ON p.property_id = pr.id
                         LEFT JOIN users t ON p.tenant_id = t.id
                         LEFT JOIN users l ON p.landlord_id = l.id
                         WHERE p.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all payments by tenant
    public function getPaymentsByTenant($tenant_id)
    {
        $this->db->query('SELECT p.*,
                         pr.address as property_address,
                         l.name as landlord_name
                         FROM payments p
                         LEFT JOIN properties pr ON p.property_id = pr.id
                         LEFT JOIN users l ON p.landlord_id = l.id
                         WHERE p.tenant_id = :tenant_id
                         ORDER BY p.payment_date DESC');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Get all payments by landlord
    public function getPaymentsByLandlord($landlord_id)
    {
        $this->db->query('SELECT p.*,
                         pr.address as property_address,
                         t.name as tenant_name, t.email as tenant_email
                         FROM payments p
                         LEFT JOIN properties pr ON p.property_id = pr.id
                         LEFT JOIN users t ON p.tenant_id = t.id
                         WHERE p.landlord_id = :landlord_id
                         ORDER BY p.payment_date DESC');
        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->resultSet();
    }

    // Get all payments by property
    public function getPaymentsByProperty($property_id)
    {
        $this->db->query('SELECT p.*,
                         t.name as tenant_name
                         FROM payments p
                         LEFT JOIN users t ON p.tenant_id = t.id
                         WHERE p.property_id = :property_id
                         ORDER BY p.payment_date DESC');
        $this->db->bind(':property_id', $property_id);
        return $this->db->resultSet();
    }

    // Get all payments by booking
    public function getPaymentsByBooking($booking_id)
    {
        $this->db->query('SELECT * FROM payments WHERE booking_id = :booking_id ORDER BY payment_date DESC');
        $this->db->bind(':booking_id', $booking_id);
        return $this->db->resultSet();
    }

    // Update payment status
    public function updatePaymentStatus($id, $status)
    {
        $this->db->query('UPDATE payments SET status = :status, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    // Get pending payments for a tenant
    public function getPendingPaymentsByTenant($tenant_id)
    {
        $this->db->query('SELECT p.*,
                         pr.address as property_address,
                         l.name as landlord_name
                         FROM payments p
                         LEFT JOIN properties pr ON p.property_id = pr.id
                         LEFT JOIN users l ON p.landlord_id = l.id
                         WHERE p.tenant_id = :tenant_id AND p.status = "pending"
                         ORDER BY p.due_date ASC');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Get overdue payments
    public function getOverduePayments($tenant_id = null)
    {
        $query = 'SELECT p.*,
                  pr.address as property_address,
                  t.name as tenant_name,
                  l.name as landlord_name
                  FROM payments p
                  LEFT JOIN properties pr ON p.property_id = pr.id
                  LEFT JOIN users t ON p.tenant_id = t.id
                  LEFT JOIN users l ON p.landlord_id = l.id
                  WHERE p.status = "pending" AND p.due_date < CURDATE()';

        if ($tenant_id) {
            $query .= ' AND p.tenant_id = :tenant_id';
        }

        $query .= ' ORDER BY p.due_date ASC';

        $this->db->query($query);

        if ($tenant_id) {
            $this->db->bind(':tenant_id', $tenant_id);
        }

        return $this->db->resultSet();
    }

    // Calculate total payments for a tenant
    public function getTotalPaymentsByTenant($tenant_id)
    {
        $this->db->query('SELECT
                         SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_paid,
                         SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending,
                         COUNT(*) as total_payments
                         FROM payments WHERE tenant_id = :tenant_id');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    // Calculate total income for landlord
    public function getTotalIncomeByLandlord($landlord_id)
    {
        $this->db->query('SELECT
                         SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_income,
                         SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_income,
                         COUNT(*) as total_payments
                         FROM payments WHERE landlord_id = :landlord_id');
        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->single();
    }

    // Get payment statistics for landlord
    public function getPaymentStatsByLandlord($landlord_id, $year = null, $month = null)
    {
        $query = 'SELECT
                  YEAR(payment_date) as year,
                  MONTH(payment_date) as month,
                  COUNT(*) as payment_count,
                  SUM(amount) as total_amount,
                  SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as completed_amount,
                  SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending_amount
                  FROM payments
                  WHERE landlord_id = :landlord_id';

        if ($year) {
            $query .= ' AND YEAR(payment_date) = :year';
        }
        if ($month) {
            $query .= ' AND MONTH(payment_date) = :month';
        }

        $query .= ' GROUP BY YEAR(payment_date), MONTH(payment_date) ORDER BY year DESC, month DESC';

        $this->db->query($query);
        $this->db->bind(':landlord_id', $landlord_id);

        if ($year) {
            $this->db->bind(':year', $year);
        }
        if ($month) {
            $this->db->bind(':month', $month);
        }

        return $this->db->resultSet();
    }

    // Get recent payments (for dashboard)
    public function getRecentPayments($user_id, $user_type, $limit = 10)
    {
        $query = 'SELECT p.*,
                  pr.address as property_address,
                  t.name as tenant_name,
                  l.name as landlord_name
                  FROM payments p
                  LEFT JOIN properties pr ON p.property_id = pr.id
                  LEFT JOIN users t ON p.tenant_id = t.id
                  LEFT JOIN users l ON p.landlord_id = l.id';

        if ($user_type == 'tenant') {
            $query .= ' WHERE p.tenant_id = :user_id';
        } else if ($user_type == 'landlord') {
            $query .= ' WHERE p.landlord_id = :user_id';
        }

        $query .= ' ORDER BY p.payment_date DESC LIMIT :limit';

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit);

        return $this->db->resultSet();
    }

    // Create scheduled rent payments for active booking
    public function createScheduledPayments($booking_id, $tenant_id, $landlord_id, $property_id, $monthly_rent, $start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = new DateInterval('P1M'); // 1 month interval

        $payments_created = 0;

        while ($start <= $end) {
            $due_date = $start->format('Y-m-d');

            $this->db->query('INSERT INTO payments (tenant_id, landlord_id, property_id, booking_id, amount, payment_method, transaction_id, status, due_date, notes)
                             VALUES (:tenant_id, :landlord_id, :property_id, :booking_id, :amount, "pending", "", "pending", :due_date, "Monthly rent payment")');

            $this->db->bind(':tenant_id', $tenant_id);
            $this->db->bind(':landlord_id', $landlord_id);
            $this->db->bind(':property_id', $property_id);
            $this->db->bind(':booking_id', $booking_id);
            $this->db->bind(':amount', $monthly_rent);
            $this->db->bind(':due_date', $due_date);

            if ($this->db->execute()) {
                $payments_created++;
            }

            $start->add($interval);
        }

        return $payments_created;
    }

    // Get all payments (for admin)
    public function getAllPayments()
    {
        $this->db->query('SELECT p.*,
                         pr.address as property_address,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM payments p
                         LEFT JOIN properties pr ON p.property_id = pr.id
                         LEFT JOIN users t ON p.tenant_id = t.id
                         LEFT JOIN users l ON p.landlord_id = l.id
                         ORDER BY p.created_at DESC');
        return $this->db->resultSet();
    }

    // Get system-wide payment statistics (for admin)
    public function getSystemPaymentStats()
    {
        $this->db->query('SELECT
                         COUNT(*) as total_payments,
                         SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_completed,
                         SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending,
                         SUM(CASE WHEN status = "pending" AND due_date < CURDATE() THEN amount ELSE 0 END) as total_overdue
                         FROM payments');
        return $this->db->single();
    }
}
