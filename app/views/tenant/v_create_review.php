<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <a href="<?php echo URLROOT; ?>/tenant/my_reviews" class="btn btn-secondary" style="margin-bottom: 1rem;">
                <i class="fas fa-arrow-left"></i> Back to My Reviews
            </a>
            <h2>Write a Review</h2>
            <p>Share your experience with this property</p>
        </div>
    </div>

    <?php flash('review_message'); ?>

    <!-- Property Info Card -->
    <div class="dashboard-section">
        <div class="property-info-card">
            <h3><?php echo htmlspecialchars($data['booking']->address ?? 'Property'); ?></h3>
            <p class="property-details">
                <i class="fas fa-calendar"></i>
                Stayed from <?php echo date('M d, Y', strtotime($data['booking']->move_in_date)); ?>
                to <?php echo $data['booking']->move_out_date ? date('M d, Y', strtotime($data['booking']->move_out_date)) : 'Present'; ?>
            </p>
        </div>
    </div>

    <!-- Review Form -->
    <div class="dashboard-section">
        <form method="POST" action="<?php echo URLROOT; ?>/reviews/createPropertyReview" class="review-form">
            <input type="hidden" name="property_id" value="<?php echo $data['booking']->property_id; ?>">
            <input type="hidden" name="booking_id" value="<?php echo $data['booking']->id; ?>">

            <!-- Rating -->
            <div class="form-group">
                <label>Overall Rating <span class="required">*</span></label>
                <div class="star-rating-input">
                    <input type="radio" name="rating" value="5" id="star5" required>
                    <label for="star5" title="5 stars">
                        <i class="fas fa-star"></i>
                    </label>

                    <input type="radio" name="rating" value="4" id="star4">
                    <label for="star4" title="4 stars">
                        <i class="fas fa-star"></i>
                    </label>

                    <input type="radio" name="rating" value="3" id="star3">
                    <label for="star3" title="3 stars">
                        <i class="fas fa-star"></i>
                    </label>

                    <input type="radio" name="rating" value="2" id="star2">
                    <label for="star2" title="2 stars">
                        <i class="fas fa-star"></i>
                    </label>

                    <input type="radio" name="rating" value="1" id="star1">
                    <label for="star1" title="1 star">
                        <i class="fas fa-star"></i>
                    </label>
                </div>
                <p class="form-help">Click on a star to rate this property</p>
            </div>

            <!-- Review Text -->
            <div class="form-group">
                <label for="review_text">Your Review (Optional)</label>
                <textarea class="form-control" id="review_text" name="review_text" rows="6"
                          placeholder="Share details about your experience with this property...&#10;&#10;What did you like? What could be improved? How was the landlord?"></textarea>
                <p class="form-help">Help other tenants by sharing specific details about your rental experience</p>
            </div>

            <!-- Submit Buttons -->
            <div class="form-actions">
                <a href="<?php echo URLROOT; ?>/tenant/my_reviews" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </div>
        </form>
    </div>

    <!-- Review Guidelines -->
    <div class="dashboard-section">
        <div class="guidelines-box">
            <h4><i class="fas fa-info-circle"></i> Review Guidelines</h4>
            <ul>
                <li>Be honest and fair in your assessment</li>
                <li>Focus on your actual experience with the property and landlord</li>
                <li>Include specific details that could help other tenants</li>
                <li>Avoid personal attacks or inappropriate language</li>
                <li>Remember that your review will be public</li>
            </ul>
        </div>
    </div>
</div>

<style>
.property-info-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.property-info-card h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.property-details {
    color: #666;
    margin: 0;
}

.property-details i {
    margin-right: 8px;
    color: #999;
}

.review-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #e74c3c;
}

.star-rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
    font-size: 32px;
}

.star-rating-input input[type="radio"] {
    display: none;
}

.star-rating-input label {
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
    margin: 0;
}

.star-rating-input label:hover,
.star-rating-input label:hover ~ label {
    color: #fbbf24;
}

.star-rating-input input[type="radio"]:checked ~ label {
    color: #fbbf24;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-help {
    font-size: 13px;
    color: #666;
    margin-top: 6px;
    margin-bottom: 0;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.guidelines-box {
    background: #eff6ff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

.guidelines-box h4 {
    margin: 0 0 15px 0;
    color: #1e40af;
}

.guidelines-box h4 i {
    margin-right: 8px;
}

.guidelines-box ul {
    margin: 0;
    padding-left: 20px;
}

.guidelines-box li {
    color: #1e40af;
    margin-bottom: 8px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .review-form {
        padding: 20px;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
