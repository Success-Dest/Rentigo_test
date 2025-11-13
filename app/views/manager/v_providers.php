<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="providers-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Service Provider Assignment</h1>
            <p class="page-subtitle">Manage service providers and assign them to maintenance requests.</p>
        </div>
        <div class="header-right">
        </div>
    </div>

    <!-- Search Bar -->
    <div class="dashboard-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search providers..." id="providerSearch">
        </div>
    </div>

    <!-- Service Provider Cards -->
    <div class="providers-grid">
        <?php if (!empty($data['providers'])): ?>
            <?php foreach ($data['providers'] as $provider): ?>
                <div class="provider-card">
                    <div class="provider-header">
                        <div class="provider-info">
                            <h3 class="provider-name"><?php echo htmlspecialchars($provider->company_name ?? $provider->name ?? 'N/A'); ?></h3>
                            <p class="provider-specialty"><?php echo htmlspecialchars($provider->service_type ?? $provider->specialty ?? 'General'); ?></p>
                            <div class="provider-rating">
                                <div class="stars">
                                    <?php
                                        $rating = $provider->rating ?? 5;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                    ?>
                                </div>
                                <span class="rating-text"><?php echo number_format($rating, 1); ?> (<?php echo $provider->review_count ?? 0; ?> reviews)</span>
                            </div>
                        </div>
                        <span class="status-badge <?php echo ($provider->status ?? 'active') === 'active' ? 'approved' : 'pending'; ?>">
                            <?php echo ucfirst($provider->status ?? 'Active'); ?>
                        </span>
                    </div>

                    <div class="provider-contact">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($provider->phone ?? 'N/A'); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($provider->email ?? 'N/A'); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($provider->address ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="provider-stats">
                        <span><strong>Completed Jobs:</strong> <?php echo $provider->completed_jobs ?? 0; ?></span>
                        <span class="status-badge approved">Available</span>
                    </div>

                    <div class="provider-actions">
                        <button class="btn btn-secondary">View Profile</button>
                        <button class="btn btn-primary">
                            <i class="fas fa-user-check"></i>
                            Assign
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; padding: 2rem; width: 100%;">No service providers registered</p>
        <?php endif; ?>
    </div>

    <!-- Manual Status Update -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Manual Status Update</h3>
        </div>
        <form class="status-update-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="requestId">Maintenance Request ID</label>
                    <select id="requestId" name="requestId" required>
                        <option value="">Select Request</option>
                        <option value="MNT-001">MNT-001</option>
                        <option value="MNT-002">MNT-002</option>
                        <option value="MNT-003">MNT-003</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="newStatus">New Status</label>
                    <select id="newStatus" name="newStatus" required>
                        <option value="">Select Status</option>
                        <option value="requested">Requested</option>
                        <option value="quoted">Quoted</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary full-width">Update Status</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>