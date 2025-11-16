<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>My Reviews</h2>
            <p>Rate and review your past rental experiences</p>
        </div>
    </div>

    <?php flash('review_message'); ?>

    <!-- Review Stats -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Your Review Activity</h3>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number"><?php echo count($data['myReviews'] ?? []); ?></h3>
                    <p class="stat-label">Reviews Written</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">
                        <?php
                            if (!empty($data['myReviews'])) {
                                $totalRating = 0;
                                foreach ($data['myReviews'] as $review) {
                                    $totalRating += $review->rating;
                                }
                                echo number_format($totalRating / count($data['myReviews']), 1);
                            } else {
                                echo '0.0';
                            }
                        ?>
                    </h3>
                    <p class="stat-label">Average Rating Given</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number"><?php echo count($data['reviewableBookings'] ?? []); ?></h3>
                    <p class="stat-label">Properties to Review</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties to Review -->
    <?php if (!empty($data['reviewableBookings'])): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Properties Awaiting Your Review</h3>
        </div>

        <div class="reviews-list">
            <?php foreach ($data['reviewableBookings'] as $booking): ?>
                <div class="review-card unreviewed">
                    <div class="property-info">
                        <h4><?php echo htmlspecialchars($booking->address ?? 'Property'); ?></h4>
                        <p class="property-location"><?php echo htmlspecialchars($booking->city ?? ''); ?></p>
                        <p class="rental-period">
                            <?php echo date('M d, Y', strtotime($booking->move_in_date)); ?> -
                            <?php echo $booking->move_out_date ? date('M d, Y', strtotime($booking->move_out_date)) : 'Present'; ?>
                        </p>
                    </div>
                    <div class="review-content">
                        <span class="status-badge pending">Not Reviewed</span>
                        <div class="review-actions">
                            <a href="<?php echo URLROOT; ?>/reviews/createPropertyReview?booking_id=<?php echo $booking->id; ?>"
                               class="btn btn-primary">
                                <i class="fas fa-star"></i> Write Review
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Past Reviews -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Your Published Reviews</h3>
        </div>

        <?php if (!empty($data['myReviews'])): ?>
            <div class="reviews-list">
                <?php foreach ($data['myReviews'] as $review): ?>
                    <div class="review-card">
                        <div class="property-info">
                            <h4><?php echo htmlspecialchars($review->property_address ?? 'Property'); ?></h4>
                            <p class="property-location"><?php echo htmlspecialchars($review->property_city ?? ''); ?></p>
                            <p class="review-date-info">Reviewed on <?php echo date('M d, Y', strtotime($review->created_at)); ?></p>
                        </div>
                        <div class="review-content">
                            <span class="status-badge approved">Published</span>
                            <div class="review-rating">
                                <div class="stars">
                                    <?php
                                        $rating = $review->rating;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                    ?>
                                </div>
                                <span class="rating-text"><?php echo $review->rating; ?>/5 stars</span>
                            </div>
                            <div class="review-text">
                                <?php if (!empty($review->review_text)): ?>
                                    <p>"<?php echo nl2br(htmlspecialchars($review->review_text)); ?>"</p>
                                <?php else: ?>
                                    <p class="text-muted"><em>No written review</em></p>
                                <?php endif; ?>
                            </div>
                            <div class="review-actions">
                                <a href="<?php echo URLROOT; ?>/reviews/update/<?php echo $review->id; ?>"
                                   class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?php echo URLROOT; ?>/reviews/delete/<?php echo $review->id; ?>"
                                   onclick="return confirm('Are you sure you want to delete this review?')"
                                   class="btn btn-sm btn-outline">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-star-half-alt"></i>
                <p>No reviews yet</p>
                <span>Share your rental experiences by reviewing properties you've lived in.</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Review Guidelines -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Review Guidelines</h3>
        </div>

        <div class="guidelines-content">
            <div class="guideline-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h4>Be Honest and Fair</h4>
                    <p>Share your genuine experience to help other tenants make informed decisions.</p>
                </div>
            </div>

            <div class="guideline-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h4>Be Specific</h4>
                    <p>Include details about the property condition, location, and landlord responsiveness.</p>
                </div>
            </div>

            <div class="guideline-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h4>Keep it Professional</h4>
                    <p>Avoid personal attacks and focus on factual information about your rental experience.</p>
                </div>
            </div>

            <div class="guideline-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h4>Review Timeline</h4>
                    <p>Reviews can be submitted up to 6 months after your lease ends.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.review-card.unreviewed {
    background: #f8f9fa;
    border-left: 4px solid #f39c12;
}

.property-info h4 {
    margin-bottom: 8px;
    color: #333;
}

.property-location {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.rental-period,
.review-date-info {
    color: #999;
    font-size: 13px;
}

.review-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stars {
    display: flex;
    gap: 4px;
    color: #fbbf24;
    font-size: 18px;
}

.rating-text {
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.review-text {
    color: #444;
    line-height: 1.6;
}

.review-text p {
    font-style: italic;
    margin-bottom: 10px;
}

.review-actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.guidelines-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.guideline-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.guideline-item i {
    font-size: 24px;
    color: #2ecc71;
    flex-shrink: 0;
}

.guideline-item h4 {
    margin-bottom: 5px;
    color: #333;
}

.guideline-item p {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
}

.empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 20px;
    font-weight: 600;
    color: #666;
    margin-bottom: 10px;
}

.empty-state span {
    font-size: 14px;
    color: #999;
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
