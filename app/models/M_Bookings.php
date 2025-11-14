<?php

/*
    BOOKINGS MODEL
    Handles property booking/reservation operations
*/

class M_Bookings
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new booking request
    public function createBooking($data)
    {
        $this->db->query('INSERT INTO bookings (tenant_id, property_id, landlord_id, move_in_date, move_out_date, monthly_rent, deposit_amount, total_amount, status, notes)
                         VALUES (:tenant_id, :property_id, :landlord_id, :move_in_date, :move_out_date, :monthly_rent, :deposit_amount, :total_amount, :status, :notes)');

        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':property_id', $data['property_id']);
        $this->db->bind(':landlord_id', $data['landlord_id']);
        $this->db->bind(':move_in_date', $data['move_in_date']);
        $this->db->bind(':move_out_date', $data['move_out_date']);
        $this->db->bind(':monthly_rent', $data['monthly_rent']);
        $this->db->bind(':deposit_amount', $data['deposit_amount']);
        $this->db->bind(':total_amount', $data['total_amount']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get booking by ID
    public function getBookingById($id)
    {
        $this->db->query('SELECT b.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users t ON b.tenant_id = t.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         WHERE b.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all bookings by tenant
    public function getBookingsByTenant($tenant_id)
    {
        $this->db->query('SELECT b.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms, p.rent,
                         l.name as landlord_name, l.email as landlord_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         WHERE b.tenant_id = :tenant_id
                         ORDER BY b.created_at DESC');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }

    // Get all bookings by landlord
    public function getBookingsByLandlord($landlord_id)
    {
        $this->db->query('SELECT b.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users t ON b.tenant_id = t.id
                         WHERE b.landlord_id = :landlord_id
                         ORDER BY b.created_at DESC');
        $this->db->bind(':landlord_id', $landlord_id);
        return $this->db->resultSet();
    }

    // Get all bookings by property
    public function getBookingsByProperty($property_id)
    {
        $this->db->query('SELECT b.*,
                         t.name as tenant_name, t.email as tenant_email
                         FROM bookings b
                         LEFT JOIN users t ON b.tenant_id = t.id
                         WHERE b.property_id = :property_id
                         ORDER BY b.created_at DESC');
        $this->db->bind(':property_id', $property_id);
        return $this->db->resultSet();
    }

    // Update booking status
    public function updateBookingStatus($id, $status, $rejection_reason = null)
    {
        $this->db->query('UPDATE bookings SET status = :status, rejection_reason = :rejection_reason, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->db->bind(':rejection_reason', $rejection_reason);
        return $this->db->execute();
    }

    // Cancel booking
    public function cancelBooking($id, $cancellation_reason)
    {
        $this->db->query('UPDATE bookings SET status = :status, cancellation_reason = :cancellation_reason, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', 'cancelled');
        $this->db->bind(':cancellation_reason', $cancellation_reason);
        return $this->db->execute();
    }

    // Check if property has active booking
    public function hasActiveBooking($property_id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM bookings WHERE property_id = :property_id AND status IN ("approved", "active")');
        $this->db->bind(':property_id', $property_id);
        $result = $this->db->single();
        return $result->count > 0;
    }

    // Check if dates conflict with existing bookings
    public function checkDateConflict($property_id, $move_in_date, $move_out_date, $exclude_booking_id = null)
    {
        $query = 'SELECT COUNT(*) as count FROM bookings
                  WHERE property_id = :property_id
                  AND status IN ("approved", "active")
                  AND (
                      (:move_in_date BETWEEN move_in_date AND move_out_date) OR
                      (:move_out_date BETWEEN move_in_date AND move_out_date) OR
                      (move_in_date BETWEEN :move_in_date AND :move_out_date) OR
                      (move_out_date BETWEEN :move_in_date AND :move_out_date)
                  )';

        if ($exclude_booking_id) {
            $query .= ' AND id != :exclude_booking_id';
        }

        $this->db->query($query);
        $this->db->bind(':property_id', $property_id);
        $this->db->bind(':move_in_date', $move_in_date);
        $this->db->bind(':move_out_date', $move_out_date);

        if ($exclude_booking_id) {
            $this->db->bind(':exclude_booking_id', $exclude_booking_id);
        }

        $result = $this->db->single();
        return $result->count > 0;
    }

    // Get active booking for a tenant
    public function getActiveBookingByTenant($tenant_id)
    {
        $this->db->query('SELECT b.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         l.name as landlord_name, l.email as landlord_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         WHERE b.tenant_id = :tenant_id AND b.status = "active"
                         ORDER BY b.created_at DESC LIMIT 1');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->single();
    }

    // Get pending bookings count for landlord
    public function getPendingBookingsCount($landlord_id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM bookings WHERE landlord_id = :landlord_id AND status = "pending"');
        $this->db->bind(':landlord_id', $landlord_id);
        $result = $this->db->single();
        return $result->count;
    }

    // Get booking statistics
    public function getBookingStats($user_id, $user_type)
    {
        $stats = [];

        if ($user_type == 'tenant') {
            $this->db->query('SELECT
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
                            FROM bookings WHERE tenant_id = :user_id');
        } else if ($user_type == 'landlord') {
            $this->db->query('SELECT
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
                            FROM bookings WHERE landlord_id = :user_id');
        }

        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    // Update booking to active (after lease agreement)
    public function activateBooking($id)
    {
        $this->db->query('UPDATE bookings SET status = "active", updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Complete booking (tenant moved out)
    public function completeBooking($id)
    {
        $this->db->query('UPDATE bookings SET status = "completed", updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Get all bookings (for admin)
    public function getAllBookings()
    {
        $this->db->query('SELECT b.*,
                         p.address, p.property_type,
                         t.name as tenant_name, t.email as tenant_email,
                         l.name as landlord_name, l.email as landlord_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users t ON b.tenant_id = t.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         ORDER BY b.created_at DESC');
        return $this->db->resultSet();
    }

    // Get all bookings for multiple properties (for property manager)
    public function getBookingsByProperties($propertyIds)
    {
        if (empty($propertyIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));

        $this->db->query("SELECT b.*,
                         p.address, p.property_type, p.bedrooms, p.bathrooms,
                         t.name as tenant_name, t.email as tenant_email, t.phone as tenant_phone,
                         l.name as landlord_name, l.email as landlord_email
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users t ON b.tenant_id = t.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         WHERE b.property_id IN ($placeholders)
                         ORDER BY b.created_at DESC");

        // Bind property IDs
        foreach ($propertyIds as $index => $propertyId) {
            $this->db->bind($index + 1, $propertyId);
        }

        return $this->db->resultSet();
    }
}
