<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Landlord Reviews</h2>
            <p>See what your landlords have said about you as a tenant</p>
        </div>
    </div>

    <?php flash('review_message'); ?>

    <!-- Review Stats -->
    <div class="dashboard-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number"><?php echo count($data['reviewsAboutMe'] ?? []); ?></h3>
                    <p class="stat-label">Reviews About You</p>
                    <span class="stat-change">From landlords</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">
                        <?php
                            if (!empty($data['reviewsAboutMe'])) {
                                $totalRating = 0;
                                foreach ($data['reviewsAboutMe'] as $review) {
                                    $totalRating += $review->rating;
                                }
                                echo number_format($totalRating / count($data['reviewsAboutMe']), 1);
                            } else {
                                echo '0.0';
                            }
                        ?>
                    </h3>
                    <p class="stat-label">Average Rating</p>
                    <span class="stat-change">As a tenant</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-pen"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">
                        <a href="<?php echo URLROOT; ?>/tenant/my_reviews" style="color: inherit; text-decoration: none;">
                            View <i class="fas fa-arrow-right" style="font-size: 0.8em;"></i>
                        </a>
                    </h3>
                    <p class="stat-label">Your Property Reviews</p>
                    <span class="stat-change">Reviews you've written</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews About You (From Landlords) -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Reviews About You</h3>
            <p>Landlords' feedback on your tenancy</p>
        </div>

        <?php if (!empty($data['reviewsAboutMe'])): ?>
            <div class="reviews-container">
                <?php foreach ($data['reviewsAboutMe'] as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="landlord-info">
                                <div class="landlord-avatar">
                                    <?php
                                        $name = $review->reviewer_name ?? 'L';
                                        $initials = strtoupper(substr($name, 0, 1));
                                        if (strpos($name, ' ') !== false) {
                                            $parts = explode(' ', $name);
                                            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                        }
                                        echo $initials;
                                    ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($review->reviewer_name ?? 'Landlord'); ?></h4>
                                    <p class="property-address">
                                        <i class="fas fa-home"></i>
                                        <?php echo htmlspecialchars($review->property_address ?? 'Property'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="review-meta">
                                <div class="rating">
                                    <div class="stars">
                                        <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $review->rating) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <span class="rating-number"><?php echo number_format($review->rating, 1); ?>/5</span>
                                </div>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review->created_at)); ?></span>
                            </div>
                        </div>
                        <?php if (!empty($review->review_text)): ?>
                            <div class="review-content">
                                <p><?php echo nl2br(htmlspecialchars($review->review_text)); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="review-content">
                                <p class="text-muted"><em>No written review</em></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-star-half-alt"></i>
                <h4>No Reviews Yet</h4>
                <p>You haven't received any reviews from landlords yet. Reviews from landlords help build your tenant reputation and will appear here after you complete your rental period.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Info Box -->
    <div class="dashboard-section">
        <div class="info-box">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
                <h4>About Landlord Reviews</h4>
                <p>Landlords can review you after your rental period ends. These reviews help build your tenant reputation and may be visible to future landlords. Good tenant reviews can help you secure better rental opportunities.</p>
                <p><strong>Building a Good Reputation:</strong></p>
                <ul>
                    <li>Pay rent on time</li>
                    <li>Maintain the property well</li>
                    <li>Communicate promptly with your landlord</li>
                    <li>Follow lease terms and property rules</li>
                    <li>Report issues early</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.reviews-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    gap: 20px;
    flex-wrap: wrap;
}

.landlord-info {
    display: flex;
    gap: 12px;
    align-items: center;
}

.landlord-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}

.landlord-info h4 {
    margin: 0 0 4px 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}

.property-address {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.property-address i {
    margin-right: 6px;
    color: #9ca3af;
}

.review-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    display: flex;
    gap: 2px;
    color: #fbbf24;
    font-size: 16px;
}

.rating-number {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.review-date {
    color: #9ca3af;
    font-size: 13px;
}

.review-content {
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.review-content p {
    color: #4b5563;
    line-height: 1.7;
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.empty-state i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h4 {
    font-size: 20px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px 0;
}

.empty-state p {
    font-size: 15px;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.info-box {
    background: #eff6ff;
    padding: 24px;
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
    display: flex;
    gap: 20px;
}

.info-icon {
    flex-shrink: 0;
}

.info-icon i {
    font-size: 32px;
    color: #3b82f6;
}

.info-content h4 {
    margin: 0 0 12px 0;
    color: #1e40af;
    font-size: 18px;
}

.info-content p {
    color: #1e40af;
    line-height: 1.6;
    margin-bottom: 12px;
}

.info-content ul {
    margin: 8px 0 0 0;
    padding-left: 24px;
    color: #1e40af;
}

.info-content li {
    margin-bottom: 6px;
    line-height: 1.5;
}

.text-muted {
    color: #9ca3af !important;
    font-style: italic;
}

@media (max-width: 768px) {
    .review-header {
        flex-direction: column;
    }

    .review-meta {
        align-items: flex-start;
        width: 100%;
    }

    .info-box {
        flex-direction: column;
    }
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
