<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="header-content">
            <h2 class="page-title">
                <i class="fas fa-file-contract"></i> Lease Agreements
            </h2>
            <p class="page-subtitle">Manage lease agreements for your assigned properties</p>
        </div>
    </div>

    <?php flash('lease_message'); ?>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo $data['draftCount'] ?? 0; ?></span>
                <span class="stat-label">Draft Leases</span>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo $data['activeCount'] ?? 0; ?></span>
                <span class="stat-label">Active Leases</span>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-archive"></i>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo $data['completedCount'] ?? 0; ?></span>
                <span class="stat-label">Completed Leases</span>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" data-tab="all">
                <i class="fas fa-list"></i> All (<?php echo count($data['allLeases'] ?? []); ?>)
            </button>
            <button class="tab-btn" data-tab="draft">
                <i class="fas fa-file-alt"></i> Draft (<?php echo $data['draftCount'] ?? 0; ?>)
            </button>
            <button class="tab-btn" data-tab="active">
                <i class="fas fa-check-circle"></i> Active (<?php echo $data['activeCount'] ?? 0; ?>)
            </button>
            <button class="tab-btn" data-tab="completed">
                <i class="fas fa-archive"></i> Completed (<?php echo $data['completedCount'] ?? 0; ?>)
            </button>
        </div>

        <!-- All Leases Tab -->
        <div id="all" class="tab-content active">
            <?php if (!empty($data['allLeases'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['allLeases'] as $lease): ?>
                                <tr>
                                    <td>#<?php echo $lease->id; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->address ?? 'N/A'); ?></strong><br>
                                        <small><?php echo ucfirst($lease->property_type ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($lease->tenant_email ?? ''); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($lease->start_date)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($lease->end_date)); ?></td>
                                    <td>Rs <?php echo number_format($lease->monthly_rent); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($lease->status); ?>">
                                            <?php echo ucfirst($lease->status); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $lease->id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No lease agreements found</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Draft Leases Tab -->
        <div id="draft" class="tab-content">
            <?php if (!empty($data['draftLeases'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Start Date</th>
                                <th>Monthly Rent</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['draftLeases'] as $lease): ?>
                                <tr>
                                    <td>#<?php echo $lease->id; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->address ?? 'N/A'); ?></strong><br>
                                        <small><?php echo ucfirst($lease->property_type ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($lease->start_date)); ?></td>
                                    <td>Rs <?php echo number_format($lease->monthly_rent); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($lease->created_at)); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $lease->id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No draft leases</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Leases Tab -->
        <div id="active" class="tab-content">
            <?php if (!empty($data['activeLeases'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Monthly Rent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['activeLeases'] as $lease): ?>
                                <tr class="active-row">
                                    <td>#<?php echo $lease->id; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->address ?? 'N/A'); ?></strong><br>
                                        <small><?php echo ucfirst($lease->property_type ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($lease->tenant_email ?? ''); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($lease->start_date)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($lease->end_date)); ?></td>
                                    <td>Rs <?php echo number_format($lease->monthly_rent); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $lease->id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No active leases</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Completed Leases Tab -->
        <div id="completed" class="tab-content">
            <?php if (!empty($data['completedLeases'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Period</th>
                                <th>Total Rent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['completedLeases'] as $lease): ?>
                                <tr>
                                    <td>#<?php echo $lease->id; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lease->address ?? 'N/A'); ?></strong><br>
                                        <small><?php echo ucfirst($lease->property_type ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($lease->start_date)); ?> -
                                        <?php echo date('M d, Y', strtotime($lease->end_date)); ?>
                                    </td>
                                    <td>Rs <?php echo number_format($lease->monthly_rent * ($lease->lease_duration_months ?? 1)); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $lease->id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No completed leases</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #3b82f6;
    }

    .stat-card.stat-warning {
        border-left-color: #f59e0b;
    }

    .stat-card.stat-success {
        border-left-color: #10b981;
    }

    .stat-card.stat-info {
        border-left-color: #45a9ea;
    }

    .stat-icon {
        font-size: 2.5rem;
        color: #6b7280;
    }

    .stat-warning .stat-icon {
        color: #f59e0b;
    }

    .stat-success .stat-icon {
        color: #10b981;
    }

    .stat-info .stat-icon {
        color: #45a9ea;
    }

    .stat-details {
        display: flex;
        flex-direction: column;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .tabs-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        background: #f9fafb;
    }

    .tab-btn {
        flex: 1;
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }

    .tab-btn:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .tab-btn.active {
        color: #45a9ea;
        border-bottom-color: #45a9ea;
        background: white;
    }

    .tab-content {
        display: none;
        padding: 1.5rem;
    }

    .tab-content.active {
        display: block;
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #f9fafb;
    }

    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .data-table td {
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .data-table tbody tr:hover {
        background: #f9fafb;
    }

    .data-table tbody tr.active-row {
        background: #f0fdf4;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge-draft {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    .actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>

<script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;

            // Update button states
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Update content visibility
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(targetTab).classList.add('active');
        });
    });
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
