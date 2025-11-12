<?php

/*
    REVIEWS MODEL
    Handles property and tenant reviews/ratings
*/

class M_Reviews
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Create a new review
    public function createReview($data)
    {
        $this->db->query('INSERT INTO reviews (reviewer_id, reviewee_id, property_id, booking_id, rating, review_text, review_type, status)
                         VALUES (:reviewer_id, :reviewee_id, :property_id, :booking_id, :rating, :review_text, :review_type, :status)');

        $this->db->bind(':reviewer_id', $data['reviewer_id']);
        $this->db->bind(':reviewee_id', $data['reviewee_id']);
        $this->db->bind(':property_id', $data['property_id'] ?? null);
        $this->db->bind(':booking_id', $data['booking_id'] ?? null);
        $this->db->bind(':rating', $data['rating']);
        $this->db->bind(':review_text', $data['review_text']);
        $this->db->bind(':review_type', $data['review_type']);
        $this->db->bind(':status', $data['status'] ?? 'approved');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Get review by ID
    public function getReviewById($id)
    {
        $this->db->query('SELECT r.*,
                         reviewer.name as reviewer_name, reviewer.email as reviewer_email,
                         reviewee.name as reviewee_name, reviewee.email as reviewee_email,
                         p.address as property_address
                         FROM reviews r
                         LEFT JOIN users reviewer ON r.reviewer_id = reviewer.id
                         LEFT JOIN users reviewee ON r.reviewee_id = reviewee.id
                         LEFT JOIN properties p ON r.property_id = p.id
                         WHERE r.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get reviews by property
    public function getReviewsByProperty($property_id, $status = 'approved')
    {
        $query = 'SELECT r.*,
                  reviewer.name as reviewer_name,
                  reviewer.user_type as reviewer_type
                  FROM reviews r
                  LEFT JOIN users reviewer ON r.reviewer_id = reviewer.id
                  WHERE r.property_id = :property_id AND r.review_type = "property"';

        if ($status) {
            $query .= ' AND r.status = :status';
        }

        $query .= ' ORDER BY r.created_at DESC';

        $this->db->query($query);
        $this->db->bind(':property_id', $property_id);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    // Get reviews by reviewer
    public function getReviewsByReviewer($reviewer_id)
    {
        $this->db->query('SELECT r.*,
                         reviewee.name as reviewee_name,
                         p.address as property_address
                         FROM reviews r
                         LEFT JOIN users reviewee ON r.reviewee_id = reviewee.id
                         LEFT JOIN properties p ON r.property_id = p.id
                         WHERE r.reviewer_id = :reviewer_id
                         ORDER BY r.created_at DESC');
        $this->db->bind(':reviewer_id', $reviewer_id);
        return $this->db->resultSet();
    }

    // Get reviews about a user (tenant or landlord)
    public function getReviewsAboutUser($reviewee_id, $review_type = null)
    {
        $query = 'SELECT r.*,
                  reviewer.name as reviewer_name,
                  reviewer.user_type as reviewer_type,
                  p.address as property_address
                  FROM reviews r
                  LEFT JOIN users reviewer ON r.reviewer_id = reviewer.id
                  LEFT JOIN properties p ON r.property_id = p.id
                  WHERE r.reviewee_id = :reviewee_id AND r.status = "approved"';

        if ($review_type) {
            $query .= ' AND r.review_type = :review_type';
        }

        $query .= ' ORDER BY r.created_at DESC';

        $this->db->query($query);
        $this->db->bind(':reviewee_id', $reviewee_id);

        if ($review_type) {
            $this->db->bind(':review_type', $review_type);
        }

        return $this->db->resultSet();
    }

    // Get average rating for a property
    public function getPropertyAverageRating($property_id)
    {
        $this->db->query('SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                         FROM reviews
                         WHERE property_id = :property_id AND review_type = "property" AND status = "approved"');
        $this->db->bind(':property_id', $property_id);
        return $this->db->single();
    }

    // Get average rating for a user
    public function getUserAverageRating($user_id, $review_type = 'tenant')
    {
        $this->db->query('SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                         FROM reviews
                         WHERE reviewee_id = :user_id AND review_type = :review_type AND status = "approved"');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':review_type', $review_type);
        return $this->db->single();
    }

    // Check if user already reviewed a property/tenant
    public function hasUserReviewed($reviewer_id, $property_id = null, $reviewee_id = null, $review_type = 'property')
    {
        if ($review_type == 'property' && $property_id) {
            $this->db->query('SELECT COUNT(*) as count FROM reviews WHERE reviewer_id = :reviewer_id AND property_id = :property_id AND review_type = "property"');
            $this->db->bind(':reviewer_id', $reviewer_id);
            $this->db->bind(':property_id', $property_id);
        } else if ($reviewee_id) {
            $this->db->query('SELECT COUNT(*) as count FROM reviews WHERE reviewer_id = :reviewer_id AND reviewee_id = :reviewee_id AND review_type = :review_type');
            $this->db->bind(':reviewer_id', $reviewer_id);
            $this->db->bind(':reviewee_id', $reviewee_id);
            $this->db->bind(':review_type', $review_type);
        }

        $result = $this->db->single();
        return $result->count > 0;
    }

    // Update review status (approve/reject)
    public function updateReviewStatus($id, $status)
    {
        $this->db->query('UPDATE reviews SET status = :status, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    // Update review
    public function updateReview($id, $rating, $review_text)
    {
        $this->db->query('UPDATE reviews SET rating = :rating, review_text = :review_text, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':rating', $rating);
        $this->db->bind(':review_text', $review_text);
        return $this->db->execute();
    }

    // Delete review
    public function deleteReview($id)
    {
        $this->db->query('DELETE FROM reviews WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Get pending reviews (for moderation)
    public function getPendingReviews()
    {
        $this->db->query('SELECT r.*,
                         reviewer.name as reviewer_name, reviewer.email as reviewer_email,
                         reviewee.name as reviewee_name, reviewee.email as reviewee_email,
                         p.address as property_address
                         FROM reviews r
                         LEFT JOIN users reviewer ON r.reviewer_id = reviewer.id
                         LEFT JOIN users reviewee ON r.reviewee_id = reviewee.id
                         LEFT JOIN properties p ON r.property_id = p.id
                         WHERE r.status = "pending"
                         ORDER BY r.created_at DESC');
        return $this->db->resultSet();
    }

    // Get all reviews (for admin)
    public function getAllReviews($status = null)
    {
        $query = 'SELECT r.*,
                  reviewer.name as reviewer_name, reviewer.email as reviewer_email,
                  reviewee.name as reviewee_name, reviewee.email as reviewee_email,
                  p.address as property_address
                  FROM reviews r
                  LEFT JOIN users reviewer ON r.reviewer_id = reviewer.id
                  LEFT JOIN users reviewee ON r.reviewee_id = reviewee.id
                  LEFT JOIN properties p ON r.property_id = p.id';

        if ($status) {
            $query .= ' WHERE r.status = :status';
        }

        $query .= ' ORDER BY r.created_at DESC';

        $this->db->query($query);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    // Get review statistics
    public function getReviewStats()
    {
        $this->db->query('SELECT
                         COUNT(*) as total,
                         SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                         SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                         SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                         SUM(CASE WHEN review_type = "property" THEN 1 ELSE 0 END) as property_reviews,
                         SUM(CASE WHEN review_type = "tenant" THEN 1 ELSE 0 END) as tenant_reviews,
                         AVG(rating) as avg_rating
                         FROM reviews');
        return $this->db->single();
    }

    // Get reviews for tenant's completed bookings
    public function getReviewableBookings($tenant_id)
    {
        $this->db->query('SELECT b.*, p.address, p.id as property_id, l.id as landlord_id, l.name as landlord_name,
                         (SELECT COUNT(*) FROM reviews WHERE booking_id = b.id AND reviewer_id = :tenant_id) as already_reviewed
                         FROM bookings b
                         LEFT JOIN properties p ON b.property_id = p.id
                         LEFT JOIN users l ON b.landlord_id = l.id
                         WHERE b.tenant_id = :tenant_id
                         AND b.status = "completed"
                         ORDER BY b.updated_at DESC');
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }
}
