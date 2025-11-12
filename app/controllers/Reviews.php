<?php

/*
    REVIEWS CONTROLLER
    Handles property and tenant reviews/ratings
*/

class Reviews extends Controller
{
    private $reviewModel;
    private $bookingModel;
    private $propertyModel;

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->reviewModel = $this->model('M_Reviews');
        $this->bookingModel = $this->model('M_Bookings');
        $this->propertyModel = $this->model('M_Properties');
    }

    // Create a property review (Tenant)
    public function createPropertyReview()
    {
        if (!isTenant()) {
            flash('review_message', 'Only tenants can review properties', 'alert alert-danger');
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $property_id = trim($_POST['property_id']);
            $booking_id = trim($_POST['booking_id']);
            $rating = trim($_POST['rating']);
            $review_text = trim($_POST['review_text']);

            // Validate
            if (empty($rating) || $rating < 1 || $rating > 5) {
                flash('review_message', 'Please provide a rating between 1 and 5', 'alert alert-danger');
                redirect('tenant/my_reviews');
            }

            // Check if user already reviewed this property
            if ($this->reviewModel->hasUserReviewed($_SESSION['user_id'], $property_id, null, 'property')) {
                flash('review_message', 'You have already reviewed this property', 'alert alert-info');
                redirect('tenant/my_reviews');
            }

            $property = $this->propertyModel->getPropertyById($property_id);

            $data = [
                'reviewer_id' => $_SESSION['user_id'],
                'reviewee_id' => $property->landlord_id,
                'property_id' => $property_id,
                'booking_id' => $booking_id,
                'rating' => $rating,
                'review_text' => $review_text,
                'review_type' => 'property',
                'status' => 'approved'
            ];

            if ($this->reviewModel->createReview($data)) {
                flash('review_message', 'Review submitted successfully', 'alert alert-success');
            } else {
                flash('review_message', 'Failed to submit review', 'alert alert-danger');
            }

            redirect('tenant/my_reviews');
        } else {
            redirect('tenant/my_reviews');
        }
    }

    // Create a tenant review (Landlord)
    public function createTenantReview()
    {
        if (!isLandlord()) {
            flash('review_message', 'Only landlords can review tenants', 'alert alert-danger');
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $tenant_id = trim($_POST['tenant_id']);
            $booking_id = trim($_POST['booking_id']);
            $property_id = trim($_POST['property_id']);
            $rating = trim($_POST['rating']);
            $review_text = trim($_POST['review_text']);

            // Validate
            if (empty($rating) || $rating < 1 || $rating > 5) {
                flash('review_message', 'Please provide a rating between 1 and 5', 'alert alert-danger');
                redirect('landlord/feedback');
            }

            $data = [
                'reviewer_id' => $_SESSION['user_id'],
                'reviewee_id' => $tenant_id,
                'property_id' => $property_id,
                'booking_id' => $booking_id,
                'rating' => $rating,
                'review_text' => $review_text,
                'review_type' => 'tenant',
                'status' => 'approved'
            ];

            if ($this->reviewModel->createReview($data)) {
                flash('review_message', 'Tenant review submitted successfully', 'alert alert-success');
            } else {
                flash('review_message', 'Failed to submit review', 'alert alert-danger');
            }

            redirect('landlord/feedback');
        } else {
            redirect('landlord/feedback');
        }
    }

    // Update review
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $review = $this->reviewModel->getReviewById($id);

            if (!$review || $review->reviewer_id != $_SESSION['user_id']) {
                flash('review_message', 'Unauthorized access', 'alert alert-danger');
                redirect('tenant/my_reviews');
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $rating = trim($_POST['rating']);
            $review_text = trim($_POST['review_text']);

            if ($this->reviewModel->updateReview($id, $rating, $review_text)) {
                flash('review_message', 'Review updated successfully', 'alert alert-success');
            } else {
                flash('review_message', 'Failed to update review', 'alert alert-danger');
            }

            if (isTenant()) {
                redirect('tenant/my_reviews');
            } else {
                redirect('landlord/feedback');
            }
        }
    }

    // Delete review
    public function delete($id)
    {
        $review = $this->reviewModel->getReviewById($id);

        if (!$review || $review->reviewer_id != $_SESSION['user_id']) {
            flash('review_message', 'Unauthorized access', 'alert alert-danger');
            redirect('tenant/my_reviews');
        }

        if ($this->reviewModel->deleteReview($id)) {
            flash('review_message', 'Review deleted successfully', 'alert alert-success');
        } else {
            flash('review_message', 'Failed to delete review', 'alert alert-danger');
        }

        if (isTenant()) {
            redirect('tenant/my_reviews');
        } else {
            redirect('landlord/feedback');
        }
    }
}
