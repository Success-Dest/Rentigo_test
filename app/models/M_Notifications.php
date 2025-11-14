<?php

/*
    NOTIFICATIONS MODEL
    Handles user notification operations
*/

class M_Notifications
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new notification
    public function createNotification($data)
    {
        $this->db->query('INSERT INTO notifications (user_id, type, title, message, link, is_read)
                         VALUES (:user_id, :type, :title, :message, :link, 0)');

        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':link', $data['link'] ?? '');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get all notifications for a user
    public function getNotificationsByUser($user_id, $limit = null)
    {
        $query = 'SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC';

        if ($limit) {
            $query .= ' LIMIT :limit';
        }

        $this->db->query($query);
        $this->db->bind(':user_id', $user_id);

        if ($limit) {
            $this->db->bind(':limit', $limit);
        }

        return $this->db->resultSet();
    }

    // Get unread notifications for a user
    public function getUnreadNotifications($user_id)
    {
        $this->db->query('SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    // Get unread notifications count
    public function getUnreadCount($user_id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result->count;
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $this->db->query('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Mark all notifications as read for a user
    public function markAllAsRead($user_id)
    {
        $this->db->query('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    // Delete notification
    public function deleteNotification($id)
    {
        $this->db->query('DELETE FROM notifications WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Delete all read notifications for a user
    public function deleteReadNotifications($user_id)
    {
        $this->db->query('DELETE FROM notifications WHERE user_id = :user_id AND is_read = 1');
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    // Delete old notifications (older than X days)
    public function deleteOldNotifications($days = 90)
    {
        $this->db->query('DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $this->db->bind(':days', $days);
        return $this->db->execute();
    }

    // Helper methods to create specific notification types

    // Booking notification
    public function notifyBookingCreated($landlord_id, $tenant_name, $property_address, $booking_id)
    {
        return $this->createNotification([
            'user_id' => $landlord_id,
            'type' => 'booking',
            'title' => 'New Booking Request',
            'message' => "$tenant_name has requested to book your property at $property_address",
            'link' => 'landlord/view_booking/' . $booking_id
        ]);
    }

    public function notifyBookingApproved($tenant_id, $property_address, $booking_id)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'booking',
            'title' => 'Booking Approved',
            'message' => "Your booking request for $property_address has been approved",
            'link' => 'bookings/details/' . $booking_id
        ]);
    }

    public function notifyBookingRejected($tenant_id, $property_address, $reason)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'booking',
            'title' => 'Booking Rejected',
            'message' => "Your booking request for $property_address has been rejected. Reason: $reason",
            'link' => 'tenant/bookings'
        ]);
    }

    // Payment notifications
    public function notifyPaymentDue($tenant_id, $amount, $due_date, $property_address)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'payment',
            'title' => 'Rent Payment Due',
            'message' => "Your rent payment of LKR $amount for $property_address is due on $due_date",
            'link' => 'tenant/pay_rent'
        ]);
    }

    public function notifyPaymentReceived($landlord_id, $amount, $tenant_name, $property_address)
    {
        return $this->createNotification([
            'user_id' => $landlord_id,
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => "Received payment of LKR $amount from $tenant_name for $property_address",
            'link' => 'landlord/payment_history'
        ]);
    }

    // Issue notifications
    public function notifyIssueReported($landlord_id, $issue_title, $property_address, $issue_id)
    {
        return $this->createNotification([
            'user_id' => $landlord_id,
            'type' => 'issue',
            'title' => 'New Issue Reported',
            'message' => "A tenant has reported an issue: $issue_title at $property_address",
            'link' => 'issues/track_issues'
        ]);
    }

    public function notifyIssueStatusChanged($tenant_id, $issue_title, $new_status)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'issue',
            'title' => 'Issue Status Updated',
            'message' => "Your issue '$issue_title' status has been updated to: $new_status",
            'link' => 'issues/track_issues'
        ]);
    }

    // Inspection notifications
    public function notifyInspectionScheduled($tenant_id, $property_address, $inspection_date)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'inspection',
            'title' => 'Inspection Scheduled',
            'message' => "An inspection has been scheduled for $property_address on $inspection_date",
            'link' => 'tenant/dashboard'
        ]);
    }

    // Lease notifications
    public function notifyLeaseExpiring($tenant_id, $property_address, $days_remaining)
    {
        return $this->createNotification([
            'user_id' => $tenant_id,
            'type' => 'lease',
            'title' => 'Lease Expiring Soon',
            'message' => "Your lease for $property_address will expire in $days_remaining days",
            'link' => 'tenant/agreements'
        ]);
    }

    // Property approval notifications (for landlords)
    public function notifyPropertyApproved($landlord_id, $property_address)
    {
        return $this->createNotification([
            'user_id' => $landlord_id,
            'type' => 'property',
            'title' => 'Property Approved',
            'message' => "Your property at $property_address has been approved and is now visible to tenants",
            'link' => 'properties/index'
        ]);
    }

    public function notifyPropertyRejected($landlord_id, $property_address, $reason)
    {
        return $this->createNotification([
            'user_id' => $landlord_id,
            'type' => 'property',
            'title' => 'Property Rejected',
            'message' => "Your property at $property_address has been rejected. Reason: $reason",
            'link' => 'properties/index'
        ]);
    }

    // Get all admin-related notifications (for admin dashboard)
    public function getAllAdminNotifications()
    {
        // Get all notifications grouped by type with aggregated statistics
        $this->db->query("SELECT
                            type,
                            COUNT(*) as recipient_count,
                            MAX(created_at) as created_at,
                            MIN(title) as title,
                            MIN(message) as message,
                            'sent' as status
                         FROM notifications
                         GROUP BY type
                         ORDER BY MAX(created_at) DESC");

        return $this->db->resultSet();
    }

    // Get notification statistics for admin
    public function getNotificationStats()
    {
        $this->db->query("SELECT
                            COUNT(*) as total_sent,
                            COUNT(DISTINCT user_id) as total_recipients,
                            COUNT(CASE WHEN is_read = 1 THEN 1 END) as read_count,
                            COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread_count
                         FROM notifications");

        return $this->db->single();
    }
}
