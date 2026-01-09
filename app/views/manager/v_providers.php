<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<?php
// ADD PAGINATION
require_once APPROOT . '/../app/helpers/AutoPaginate.php';
AutoPaginate::init($data, 5);
?>

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
    <div class="dashboard-section" style="max-width: 400px; margin-left: 7px">
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
                            <h3 class="provider-name"><?php echo htmlspecialchars($provider->name ?? 'N/A'); ?></h3>
                            <p class="provider-company"><?php echo htmlspecialchars($provider->company ?? 'Independent'); ?></p>
                            <p class="provider-specialty">
                                <i class="fas fa-tools"></i>
                                <?php echo htmlspecialchars(ucfirst($provider->specialty ?? 'General')); ?>
                            </p>
                            <div class="provider-rating">
                                <div class="stars">
                                    <?php
                                    $rating = $provider->rating ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-text"><?php echo $rating > 0 ? number_format($rating, 1) . '/5.0' : 'Not rated'; ?></span>
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

                    <div class="provider-actions">
                        <a href="<?php echo URLROOT; ?>/manager/maintenance" class="btn btn-primary">
                            <i class="fas fa-tasks"></i>
                            View Maintenance Requests
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted" style="text-align: center; padding: 2rem; width: 100%;">No service providers registered</p>
        <?php endif; ?>
    </div>
</div>

<!-- ADD PAGINATION HERE - Render at bottom -->
<?php echo AutoPaginate::render($data['_pagination']); ?>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>