<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Tenant Feedback</h1>
        <p class="page-subtitle">View tenant reviews and manage your reviews</p>
    </div>
</div>

<?php flash('review_message'); ?>

<!-- Feedback Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: var(--primary-color);">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Reviews About You</h3>
            <div class="stat-value"><?php echo count($data['reviewsAboutMe'] ?? []); ?></div>
            <div class="stat-change">From tenants</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: var(--success-color);">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Average Rating</h3>
            <div class="stat-value">
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
            </div>
            <div class="stat-change">Tenant ratings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: var(--info-color);">
            <i class="fas fa-pen"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Your Reviews</h3>
            <div class="stat-value"><?php echo count($data['myReviews'] ?? []); ?></div>
            <div class="stat-change">Reviews written</div>
        </div>
    </div>
</div>

<!-- Reviews About You (From Tenants) -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Reviews About You</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($data['reviewsAboutMe'])): ?>
            <div class="feedback-container">
                <?php foreach ($data['reviewsAboutMe'] as $review): ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="tenant-info">
                                <div class="tenant-avatar">
                                    <?php
                                        $name = $review->reviewer_name ?? 'T';
                                        $initials = strtoupper(substr($name, 0, 1));
                                        if (strpos($name, ' ') !== false) {
                                            $parts = explode(' ', $name);
                                            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                        }
                                        echo $initials;
                                    ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($review->reviewer_name ?? 'Tenant'); ?></h4>
                                    <p><?php echo htmlspecialchars($review->property_address ?? 'Property'); ?></p>
                                </div>
                            </div>
                            <div class="feedback-meta">
                                <div class="rating">
                                    <span class="stars">
                                        <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review->rating ? '★' : '☆';
                                            }
                                        ?>
                                    </span>
                                    <span class="rating-number"><?php echo number_format($review->rating, 1); ?></span>
                                </div>
                                <span class="feedback-date"><?php echo date('M d, Y', strtotime($review->created_at)); ?></span>
                            </div>
                        </div>
                        <div class="feedback-content">
                            <p>"<?php echo htmlspecialchars($review->comment); ?>"</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-star-half-alt"></i>
                <p>No reviews yet</p>
                <span>Tenant reviews about your properties and management will appear here.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Your Reviews (About Tenants) -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Your Reviews About Tenants</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($data['myReviews'])): ?>
            <div class="feedback-container">
                <?php foreach ($data['myReviews'] as $review): ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="tenant-info">
                                <div class="tenant-avatar">
                                    <?php
                                        $name = $review->reviewee_name ?? 'T';
                                        $initials = strtoupper(substr($name, 0, 1));
                                        if (strpos($name, ' ') !== false) {
                                            $parts = explode(' ', $name);
                                            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                        }
                                        echo $initials;
                                    ?>
                                </div>
                                <div>
                                    <h4><?php echo htmlspecialchars($review->reviewee_name ?? 'Tenant'); ?></h4>
                                    <p><?php echo htmlspecialchars($review->property_address ?? 'Property'); ?></p>
                                </div>
                            </div>
                            <div class="feedback-meta">
                                <div class="rating">
                                    <span class="stars">
                                        <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review->rating ? '★' : '☆';
                                            }
                                        ?>
                                    </span>
                                    <span class="rating-number"><?php echo number_format($review->rating, 1); ?></span>
                                </div>
                                <span class="feedback-date"><?php echo date('M d, Y', strtotime($review->created_at)); ?></span>
                            </div>
                        </div>
                        <div class="feedback-content">
                            <p>"<?php echo htmlspecialchars($review->comment); ?>"</p>
                        </div>
                        <div class="feedback-actions">
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
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-pen"></i>
                <p>No reviews written</p>
                <span>You haven't written any reviews about your tenants yet.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.feedback-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.feedback-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.tenant-info {
    display: flex;
    gap: 12px;
    align-items: center;
}

.tenant-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 18px;
    flex-shrink: 0;
}

.tenant-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    color: #333;
}

.tenant-info p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.feedback-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    color: #fbbf24;
    font-size: 18px;
    letter-spacing: 2px;
}

.rating-number {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.feedback-date {
    font-size: 13px;
    color: #999;
}

.feedback-content {
    margin-bottom: 15px;
}

.feedback-content p {
    font-size: 15px;
    line-height: 1.6;
    color: #444;
    font-style: italic;
    margin: 0;
}

.feedback-tags {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.tag {
    padding: 4px 12px;
    background: white;
    border-radius: 16px;
    font-size: 12px;
    color: #667eea;
    border: 1px solid #667eea;
}

.feedback-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
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

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
