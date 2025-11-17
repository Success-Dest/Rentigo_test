<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Policy Management</h2>
            <p>Create and manage rental policies and documents</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/policies/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Policy
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php flash('policy_message'); ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-text"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']['total']; ?></h3>
                <p class="stat-label">Total Policies</p>
                <span class="stat-change">All documents</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']['active']; ?></h3>
                <p class="stat-label">Active Policies</p>
                <span class="stat-change positive">Currently enforced</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']['draft']; ?></h3>
                <p class="stat-label">Draft Policies</p>
                <span class="stat-change">Under development</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">
                    <?php
                    if ($data['stats']['last_updated']) {
                        echo date('d/m/Y', strtotime($data['stats']['last_updated']));
                    } else {
                        echo 'Never';
                    }
                    ?>
                </h3>
                <p class="stat-label">Last Updated</p>
                <span class="stat-change">Recent policy update</span>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="<?php echo URLROOT; ?>/policies" id="filterForm">
        <div class="search-filter-content">
            <div class="search-input-wrapper">
                <input type="text"
                    class="form-input"
                    name="search"
                    placeholder="Search policies..."
                    value="<?php echo isset($data['filters']['search']) ? htmlspecialchars($data['filters']['search']) : ''; ?>">
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="draft" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="inactive" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="archived" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                    <option value="under_review" <?php echo (isset($data['filters']['status']) && $data['filters']['status'] == 'under_review') ? 'selected' : ''; ?>>Under Review</option>
                </select>
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($data['categories'] as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($data['filters']['category']) && $data['filters']['category'] == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="<?php echo URLROOT; ?>/policies" class="btn btn-outline">Clear</a>
        </div>
    </form>

    <!-- Policy Documents -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Policy Documents (<?php echo count($data['policies']); ?>)</h3>
        </div>

        <?php if (!empty($data['policies'])): ?>
            <div class="table-container">
                <table class="data-table policies-table">
                    <thead>
                        <tr>
                            <th>Policy</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Version</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['policies'] as $policy): ?>
                            <tr data-status="<?php echo $policy->policy_status; ?>" data-policy-id="<?php echo $policy->policy_id; ?>">
                                <td>
                                    <div class="policy-info">
                                        <div class="policy-icon">
                                            <?php
                                            // Choose icon based on category
                                            $icons = [
                                                'rental' => 'fas fa-file-contract',
                                                'security' => 'fas fa-shield-alt',
                                                'maintenance' => 'fas fa-tools',
                                                'financial' => 'fas fa-dollar-sign',
                                                'general' => 'fas fa-file-text'
                                            ];
                                            $icon = $icons[$policy->policy_category] ?? 'fas fa-file-text';
                                            ?>
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="policy-details">
                                            <div class="policy-name"><?php echo htmlspecialchars($policy->policy_name); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    // Category badges with different colors
                                    $categoryClasses = [
                                        'rental' => 'category-badge rental',
                                        'security' => 'category-badge security',
                                        'maintenance' => 'category-badge maintenance',
                                        'financial' => 'category-badge financial',
                                        'general' => 'category-badge general',
                                        'privacy' => 'category-badge privacy',
                                        'terms_of_service' => 'category-badge terms',
                                        'refund' => 'category-badge refund',
                                        'data_protection' => 'category-badge data-protection'
                                    ];
                                    $categoryClass = $categoryClasses[$policy->policy_category] ?? 'category-badge';
                                    // Format display name
                                    $displayCategory = str_replace('_', ' ', ucwords($policy->policy_category, '_'));
                                    ?>
                                    <span class="<?php echo $categoryClass; ?>"><?php echo $displayCategory; ?></span>
                                </td>
                                <td>
                                    <div class="policy-description"><?php echo htmlspecialchars($policy->policy_description); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($policy->policy_version); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($policy->last_updated)); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $policy->policy_status; ?>">
                                        <?php echo ucfirst($policy->policy_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="policy-actions">
                                        <button class="action-btn view-btn"
                                            onclick="viewPolicy(<?php echo $policy->policy_id; ?>)"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="<?php echo URLROOT; ?>/policies/edit/<?php echo $policy->policy_id; ?>"
                                            class="action-btn edit-btn"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($policy->policy_status === 'draft'): ?>
                                            <button class="action-btn approve-btn"
                                                onclick="activatePolicy(<?php echo $policy->policy_id; ?>)"
                                                title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php elseif ($policy->policy_status === 'active'): ?>
                                            <button class="action-btn status-btn"
                                                onclick="deactivatePolicy(<?php echo $policy->policy_id; ?>)"
                                                title="Deactivate">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="action-btn approve-btn"
                                                onclick="activatePolicy(<?php echo $policy->policy_id; ?>)"
                                                title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="action-btn danger-btn"
                                            onclick="deletePolicy(<?php echo $policy->policy_id; ?>)"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem;">
                <i class="fas fa-file-text" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
                <h3 style="color: #64748b; margin-bottom: 0.5rem;">No Policies Found</h3>
                <p style="color: #94a3b8; margin-bottom: 2rem;">
                    <?php if (!empty($data['filters'])): ?>
                        No policies match your current filters. Try adjusting your search criteria.
                    <?php else: ?>
                        Get started by creating your first policy document.
                    <?php endif; ?>
                </p>
                <a href="<?php echo URLROOT; ?>/policies/add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Policy
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Policy Modal -->
<div class="modal-overlay hidden" id="viewPolicyModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 id="modalPolicyTitle">Policy Details</h3>
            <button class="modal-close" onclick="closeViewPolicyModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalPolicyContent">
            <!-- Policy content will be loaded here -->
        </div>
    </div>
</div>

<script>
    // Policy Management Functions
    function viewPolicy(policyId) {
        const modal = document.getElementById('viewPolicyModal');
        const title = document.getElementById('modalPolicyTitle');
        const content = document.getElementById('modalPolicyContent');

        // Show loading state
        title.textContent = 'Loading...';
        content.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading policy...</div>';
        modal.classList.remove('hidden');

        // Fetch policy data
        fetch(`<?php echo URLROOT; ?>/policies/view_policy/${policyId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const policy = data.policy;
                    title.textContent = policy.policy_name;

                    content.innerHTML = `
                <div class="policy-details">
                    <div class="detail-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Category</label>
                            <span class="category-badge ${policy.policy_category}">${policy.policy_category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Version</label>
                            <span>${policy.policy_version}</span>
                        </div>
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Status</label>
                            <span class="status-badge ${policy.policy_status}">${policy.policy_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Effective Date</label>
                            <span>${new Date(policy.effective_date).toLocaleDateString()}</span>
                        </div>
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Created By</label>
                            <span>${policy.created_by_name}</span>
                        </div>
                        <div class="detail-item">
                            <label style="display: block; font-weight: 600; margin-bottom: 0.25rem; color: #64748b;">Last Updated</label>
                            <span>${new Date(policy.updated_at).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="detail-section">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #64748b;">Description</label>
                        <p style="margin-bottom: 1.5rem; color: #334155;">${policy.policy_description}</p>
                    </div>
                    <div class="detail-section">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #64748b;">Policy Content</label>
                        <div style="border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 1.5rem; background: #f8fafc;">
                            ${policy.policy_content}
                        </div>
                    </div>
                </div>
            `;
                } else {
                    title.textContent = 'Error';
                    content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;">Failed to load policy details.</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                title.textContent = 'Error';
                content.innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;">Failed to load policy details.</div>';
            });
    }

    function closeViewPolicyModal() {
        document.getElementById('viewPolicyModal').classList.add('hidden');
    }

    function activatePolicy(policyId) {
        if (confirm('Are you sure you want to activate this policy?')) {
            updatePolicyStatus(policyId, 'active');
        }
    }

    function deactivatePolicy(policyId) {
        if (confirm('Are you sure you want to deactivate this policy?')) {
            updatePolicyStatus(policyId, 'inactive');
        }
    }

    function updatePolicyStatus(policyId, status) {
        fetch(`<?php echo URLROOT; ?>/policies/updateStatus/${policyId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload the page to reflect changes
                    location.reload();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to update policy status', 'error');
            });
    }

    function deletePolicy(policyId) {
        if (confirm('Are you sure you want to delete this policy? This action cannot be undone.')) {
            fetch(`<?php echo URLROOT; ?>/policies/delete/${policyId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Remove the row from table
                        const row = document.querySelector(`tr[data-policy-id="${policyId}"]`);
                        if (row) {
                            row.remove();
                            updatePolicyCount();
                        }
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Failed to delete policy', 'error');
                });
        }
    }

    function updatePolicyCount() {
        const rows = document.querySelectorAll('.policies-table tbody tr');
        const count = rows.length;
        const header = document.querySelector('.section-header h3');
        if (header) {
            header.textContent = `Policy Documents (${count})`;
        }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('viewPolicyModal');
        if (event.target === modal) {
            closeViewPolicyModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeViewPolicyModal();
        }
    });

    // Auto-submit filter form on change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const selects = filterForm.querySelectorAll('select');
        const searchInput = filterForm.querySelector('input[name="search"]');

        selects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // Debounce search input
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    });
</script>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>