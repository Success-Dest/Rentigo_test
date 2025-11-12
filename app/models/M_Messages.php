<?php

/*
    MESSAGES MODEL
    Handles messaging/inquiries between users
*/

class M_Messages
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new message
    public function createMessage($data)
    {
        $this->db->query('INSERT INTO messages (sender_id, recipient_id, property_id, subject, message, parent_message_id, is_read)
                         VALUES (:sender_id, :recipient_id, :property_id, :subject, :message, :parent_message_id, 0)');

        $this->db->bind(':sender_id', $data['sender_id']);
        $this->db->bind(':recipient_id', $data['recipient_id']);
        $this->db->bind(':property_id', $data['property_id'] ?? null);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':parent_message_id', $data['parent_message_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get message by ID
    public function getMessageById($id)
    {
        $this->db->query('SELECT m.*,
                         s.name as sender_name, s.email as sender_email, s.user_type as sender_type,
                         r.name as recipient_name, r.email as recipient_email, r.user_type as recipient_type,
                         p.address as property_address
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN users r ON m.recipient_id = r.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         WHERE m.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get all messages for a user (inbox)
    public function getMessagesByUser($user_id, $type = 'received')
    {
        if ($type == 'received') {
            $this->db->query('SELECT m.*,
                             s.name as sender_name, s.email as sender_email,
                             p.address as property_address
                             FROM messages m
                             LEFT JOIN users s ON m.sender_id = s.id
                             LEFT JOIN properties p ON m.property_id = p.id
                             WHERE m.recipient_id = :user_id AND m.parent_message_id IS NULL
                             ORDER BY m.created_at DESC');
        } else { // sent
            $this->db->query('SELECT m.*,
                             r.name as recipient_name, r.email as recipient_email,
                             p.address as property_address
                             FROM messages m
                             LEFT JOIN users r ON m.recipient_id = r.id
                             LEFT JOIN properties p ON m.property_id = p.id
                             WHERE m.sender_id = :user_id AND m.parent_message_id IS NULL
                             ORDER BY m.created_at DESC');
        }

        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    // Get conversation thread
    public function getConversationThread($message_id)
    {
        // First get the root message
        $this->db->query('SELECT parent_message_id FROM messages WHERE id = :id');
        $this->db->bind(':id', $message_id);
        $result = $this->db->single();

        $root_id = $result->parent_message_id ? $result->parent_message_id : $message_id;

        // Get all messages in the thread
        $this->db->query('SELECT m.*,
                         s.name as sender_name, s.email as sender_email, s.user_type as sender_type,
                         r.name as recipient_name, r.email as recipient_email,
                         p.address as property_address
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN users r ON m.recipient_id = r.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         WHERE m.id = :root_id OR m.parent_message_id = :root_id
                         ORDER BY m.created_at ASC');
        $this->db->bind(':root_id', $root_id);
        return $this->db->resultSet();
    }

    // Get unread messages count
    public function getUnreadCount($user_id)
    {
        $this->db->query('SELECT COUNT(*) as count FROM messages WHERE recipient_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result->count;
    }

    // Mark message as read
    public function markAsRead($id)
    {
        $this->db->query('UPDATE messages SET is_read = 1, read_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Mark all messages as read for a user
    public function markAllAsRead($user_id)
    {
        $this->db->query('UPDATE messages SET is_read = 1, read_at = NOW() WHERE recipient_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    // Delete message (soft delete or archive)
    public function deleteMessage($id, $user_id)
    {
        // Mark as deleted for the user instead of actually deleting
        $this->db->query('UPDATE messages SET is_deleted = 1 WHERE id = :id AND (sender_id = :user_id OR recipient_id = :user_id)');
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    // Get messages about a specific property
    public function getMessagesByProperty($property_id, $user_id)
    {
        $this->db->query('SELECT m.*,
                         s.name as sender_name, s.email as sender_email,
                         r.name as recipient_name, r.email as recipient_email
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN users r ON m.recipient_id = r.id
                         WHERE m.property_id = :property_id
                         AND (m.sender_id = :user_id OR m.recipient_id = :user_id)
                         ORDER BY m.created_at DESC');
        $this->db->bind(':property_id', $property_id);
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    // Get recent messages (for dashboard)
    public function getRecentMessages($user_id, $limit = 5)
    {
        $this->db->query('SELECT m.*,
                         s.name as sender_name, s.email as sender_email,
                         p.address as property_address
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         WHERE m.recipient_id = :user_id
                         ORDER BY m.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // Search messages
    public function searchMessages($user_id, $search_term)
    {
        $this->db->query('SELECT m.*,
                         s.name as sender_name, s.email as sender_email,
                         r.name as recipient_name, r.email as recipient_email,
                         p.address as property_address
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN users r ON m.recipient_id = r.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         WHERE (m.sender_id = :user_id OR m.recipient_id = :user_id)
                         AND (m.subject LIKE :search OR m.message LIKE :search)
                         ORDER BY m.created_at DESC');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':search', '%' . $search_term . '%');
        return $this->db->resultSet();
    }

    // Get conversation between two users
    public function getConversationBetweenUsers($user1_id, $user2_id)
    {
        $this->db->query('SELECT m.*,
                         s.name as sender_name,
                         r.name as recipient_name,
                         p.address as property_address
                         FROM messages m
                         LEFT JOIN users s ON m.sender_id = s.id
                         LEFT JOIN users r ON m.recipient_id = r.id
                         LEFT JOIN properties p ON m.property_id = p.id
                         WHERE (m.sender_id = :user1_id AND m.recipient_id = :user2_id)
                         OR (m.sender_id = :user2_id AND m.recipient_id = :user1_id)
                         ORDER BY m.created_at ASC');
        $this->db->bind(':user1_id', $user1_id);
        $this->db->bind(':user2_id', $user2_id);
        return $this->db->resultSet();
    }
}
