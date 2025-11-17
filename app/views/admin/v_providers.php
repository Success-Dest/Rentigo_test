<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Flash Messages -->
    <?php
    $flashMessage = flash('provider_message');
    if (!empty($flashMessage)):
    ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $flashMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Service Provider Management</h2>
            <p>Manage your network of trusted service providers</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/providers/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Provider
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['counts']->total ?? 0; ?></h3>
                <p class="stat-label">Total Providers</p>
                <span class="stat-change">All registered providers</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['counts']->active ?? 0; ?></h3>
                <p class="stat-label">Active Providers</p>
                <span class="stat-change positive">Currently available</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['counts']->inactive ?? 0; ?></h3>
                <p class="stat-label">Inactive Providers</p>
                <span class="stat-change">Temporarily unavailable</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">
                    <?php
                    if (isset($data['counts']->average_rating) && $data['counts']->average_rating > 0) {
                        echo number_format($data['counts']->average_rating, 1);
                    } else {
                        echo "N/A";
                    }
                    ?>
                </h3>
                <p class="stat-label">Average Rating</p>
                <span class="stat-change positive">
                    <?php
                    if (isset($data['counts']->rated_providers) && $data['counts']->rated_providers > 0) {
                        echo "From " . $data['counts']->rated_providers . " providers";
                    } else {
                        echo "No ratings yet";
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="<?php echo URLROOT; ?>/providers/index">
        <div class="search-filter-content">
            <div class="search-input-wrapper">
                <input type="text"
                    class="form-input"
                    name="search"
                    placeholder="Search providers..."
                    value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="specialty">
                    <option value="">All Specialties</option>
                    <option value="plumbing" <?php echo ($data['specialty_filter'] ?? '') === 'plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                    <option value="electrical" <?php echo ($data['specialty_filter'] ?? '') === 'electrical' ? 'selected' : ''; ?>>Electrical</option>
                    <option value="hvac" <?php echo ($data['specialty_filter'] ?? '') === 'hvac' ? 'selected' : ''; ?>>HVAC</option>
                    <option value="carpentry" <?php echo ($data['specialty_filter'] ?? '') === 'carpentry' ? 'selected' : ''; ?>>Carpentry</option>
                    <option value="painting" <?php echo ($data['specialty_filter'] ?? '') === 'painting' ? 'selected' : ''; ?>>Painting</option>
                    <option value="landscaping" <?php echo ($data['specialty_filter'] ?? '') === 'landscaping' ? 'selected' : ''; ?>>Landscaping</option>
                    <option value="cleaning" <?php echo ($data['specialty_filter'] ?? '') === 'cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                    <option value="pest_control" <?php echo ($data['specialty_filter'] ?? '') === 'pest_control' ? 'selected' : ''; ?>>Pest Control</option>
                    <option value="general" <?php echo ($data['specialty_filter'] ?? '') === 'general' ? 'selected' : ''; ?>>General Maintenance</option>
                </select>
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo ($data['status_filter'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($data['status_filter'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo ($data['status_filter'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="<?php echo URLROOT; ?>/providers/index" class="btn btn-outline">
                <i class="fas fa-refresh"></i> Clear
            </a>
        </div>
    </form>

    <!-- Service Providers Table -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Service Providers (<?php echo count($data['providers'] ?? []); ?>)</h3>
        </div>

        <div class="table-container">
            <?php if (!empty($data['providers'])): ?>
                <table class="data-table providers-table">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Specialty</th>
                            <th>Contact</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['providers'] as $provider): ?>
                            <tr data-provider-id="<?php echo $provider->id; ?>">
                                <td>
                                    <div class="provider-info">
                                        <div class="provider-icon">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="provider-details">
                                            <div class="provider-name"><?php echo htmlspecialchars($provider->name); ?></div>
                                            <?php if (!empty($provider->company)): ?>
                                                <div class="provider-company"><?php echo htmlspecialchars($provider->company); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="specialty-badge <?php echo strtolower($provider->specialty); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $provider->specialty)); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <?php if (!empty($provider->phone)): ?>
                                            <div class="contact-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($provider->phone); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($provider->email)): ?>
                                            <div class="contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <span><?php echo htmlspecialchars($provider->email); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating">
                                        <?php if ($provider->rating > 0): ?>
                                            <span class="rating-stars">
                                                <?php
                                                $fullStars = floor($provider->rating);
                                                $hasHalfStar = ($provider->rating - $fullStars) >= 0.5;

                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $fullStars) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                                        echo '<i class="fas fa-star-half-alt"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </span>
                                            <span class="rating-value"><?php echo number_format($provider->rating, 1); ?></span>
                                        <?php else: ?>
                                            <span class="no-rating">No rating</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $provider->status; ?>">
                                        <?php echo ucfirst($provider->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="provider-actions">
                                        <a href="<?php echo URLROOT; ?>/providers/edit/<?php echo $provider->id; ?>"
                                            class="action-btn edit-btn"
                                            title="Edit Provider">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="action-btn status-btn"
                                            onclick="toggleProviderStatus(<?php echo $provider->id; ?>, '<?php echo $provider->status; ?>')"
                                            title="Toggle Status">
                                            <?php if ($provider->status === 'active'): ?>
                                                <i class="fas fa-pause"></i>
                                            <?php else: ?>
                                                <i class="fas fa-play"></i>
                                            <?php endif; ?>
                                        </button>
                                        <button class="action-btn danger-btn"
                                            onclick="deleteProvider(<?php echo $provider->id; ?>)"
                                            title="Delete Provider">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>No Service Providers Found</h3>
                    <p>Start by adding your first service provider to the system.</p>
                    <a href="<?php echo URLROOT; ?>/providers/add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Provider
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Provider management functions
    function toggleProviderStatus(providerId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const confirmation = confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this provider?`);

        if (confirmation) {
            fetch(`<?php echo URLROOT; ?>/providers/updateStatus/${providerId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the provider status.');
                });
        }
    }

    function deleteProvider(providerId) {
        const confirmation = confirm('Are you sure you want to delete this provider? This action cannot be undone.');

        if (confirmation) {
            fetch(`<?php echo URLROOT; ?>/providers/delete/${providerId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the provider.');
                });
        }
    }

    // Auto-hide flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });
</script>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>