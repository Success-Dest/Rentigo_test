<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back! Here's what's happening with your properties.</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--primary-color);">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Properties</div>
                <h3><?php echo $data['totalProperties'] ?? 0; ?></h3>
                <div class="stat-change">Assigned to you</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-color);">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Units</div>
                <h3><?php echo $data['totalUnits'] ?? 0; ?></h3>
                <div class="stat-change"><?php echo $data['occupiedUnits'] ?? 0; ?> occupied</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--success-color);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Income</div>
                <h3>LKR <?php echo number_format($data['totalIncome'] ?? 0, 0); ?></h3>
                <div class="stat-change">From completed payments</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--warning-color);">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Expenses</div>
                <h3>LKR <?php echo number_format($data['totalExpenses'] ?? 0, 0); ?></h3>
                <div class="stat-change">Maintenance costs</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Payment History -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Payments</h2>
                <a href="#" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['recentPayments'])): ?>
                            <?php foreach ($data['recentPayments'] as $payment): ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($payment->tenant_name ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($payment->property_address ?? 'N/A'); ?></td>
                                    <td>LKR <?php echo number_format($payment->amount, 0); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($payment->payment_date ?? $payment->due_date)); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $payment->status === 'completed' ? 'approved' : ($payment->status === 'pending' ? 'pending' : 'rejected'); ?>">
                                            <?php echo ucfirst($payment->status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No payment records</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Maintenance Status -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Maintenance Status</h2>
                <a href="<?php echo URLROOT; ?>/manager/maintenance" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="maintenance-list">
                <?php if (!empty($data['recentMaintenance'])): ?>
                    <?php foreach ($data['recentMaintenance'] as $maintenance): ?>
                        <div class="maintenance-item">
                            <div class="maintenance-info">
                                <h4><?php echo htmlspecialchars($maintenance->title ?? 'Maintenance Request'); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($maintenance->property_address ?? 'N/A'); ?></p>
                            </div>
                            <div class="maintenance-status">
                                <span class="status-badge <?php
                                    echo $maintenance->status === 'completed' ? 'approved' :
                                        ($maintenance->status === 'in_progress' || $maintenance->status === 'approved' ? 'pending' :
                                        ($maintenance->status === 'urgent' ? 'rejected' : 'pending'));
                                ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $maintenance->status)); ?>
                                </span>
                                <small class="text-muted">
                                    <?php
                                        if ($maintenance->status === 'completed' && !empty($maintenance->completed_at)) {
                                            echo 'Completed: ' . date('M d', strtotime($maintenance->completed_at));
                                        } else {
                                            echo 'Reported: ' . date('M d', strtotime($maintenance->created_at));
                                        }
                                    ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 2rem;">No maintenance requests</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section" style="margin-top: 2rem;">
        <div class="section-header">
            <h2>Quick Actions</h2>
        </div>
        <div style="padding: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo URLROOT; ?>/manager/maintenance" class="btn btn-primary">
                <i class="fas fa-tools"></i>
                View Maintenance
            </a>
            <a href="<?php echo URLROOT; ?>/manager/inspections" class="btn btn-primary">
                <i class="fas fa-clipboard-check"></i>
                Schedule Inspection
            </a>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>