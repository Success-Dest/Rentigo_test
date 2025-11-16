<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="dashboard-content">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Here's what's happening with your rentals today.</p>
    </div>

    <?php flash('dashboard_message'); ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['bookingStats']->total ?? 0; ?></h3>
                <p class="stat-label">Total Bookings</p>
                <span class="stat-subtext">
                    <?php echo $data['bookingStats']->active ?? 0; ?> active,
                    <?php echo $data['bookingStats']->pending ?? 0; ?> pending
                </span>
            </div>
        </div>

        <div class="stat-card <?php echo !empty($data['pendingPayments']) ? 'warning' : ''; ?>">
            <div class="stat-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo count($data['pendingPayments'] ?? []); ?></h3>
                <p class="stat-label">Pending Payments</p>
                <?php if (!empty($data['pendingPayments'])): ?>
                    <span class="stat-subtext warning-text">
                        Action required
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo count($data['recentIssues'] ?? []); ?></h3>
                <p class="stat-label">Recent Issues</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['unreadNotifications'] ?? 0; ?></h3>
                <p class="stat-label">Notifications</p>
                <?php if (($data['unreadNotifications'] ?? 0) > 0): ?>
                    <span class="stat-subtext info-text">
                        Unread messages
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Active Booking or Lease -->
    <?php if (isset($data['activeLease']) && $data['activeLease']): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Active Lease</h3>
            <a href="<?php echo URLROOT; ?>/tenant/agreements" class="btn btn-secondary">View Details</a>
        </div>

        <div class="active-rental-card">
            <div class="rental-image">
                <i class="fas fa-home"></i>
            </div>
            <div class="rental-details">
                <h4><?php echo htmlspecialchars($data['activeLease']->property_address ?? 'Property'); ?></h4>
                <div class="rental-info">
                    <span>
                        <i class="fas fa-calendar"></i>
                        <?php echo date('M d, Y', strtotime($data['activeLease']->start_date)); ?> -
                        <?php echo date('M d, Y', strtotime($data['activeLease']->end_date)); ?>
                    </span>
                    <span>
                        <i class="fas fa-money-bill-wave"></i>
                        LKR <?php echo number_format($data['activeLease']->monthly_rent * 1.10, 2); ?>/month
                    </span>
                </div>
                <div class="rental-status">
                    <span class="status-badge approved">Active Lease</span>
                    <span class="lease-days">
                        <?php
                            $daysRemaining = floor((strtotime($data['activeLease']->end_date) - time()) / 86400);
                            echo $daysRemaining > 0 ? $daysRemaining . ' days remaining' : 'Expired';
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php elseif (isset($data['activeBooking']) && $data['activeBooking']): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Active Booking</h3>
            <a href="<?php echo URLROOT; ?>/tenant/bookings" class="btn btn-secondary">View Details</a>
        </div>

        <div class="active-rental-card">
            <div class="rental-image">
                <i class="fas fa-home"></i>
            </div>
            <div class="rental-details">
                <h4><?php echo htmlspecialchars($data['activeBooking']->address ?? 'Property'); ?></h4>
                <div class="rental-info">
                    <span>
                        <i class="fas fa-calendar"></i>
                        Move-in: <?php echo date('M d, Y', strtotime($data['activeBooking']->move_in_date)); ?>
                    </span>
                    <span>
                        <i class="fas fa-money-bill-wave"></i>
                        LKR <?php echo number_format($data['activeBooking']->monthly_rent * 1.10, 2); ?>/month
                    </span>
                </div>
                <div class="rental-status">
                    <?php
                        $statusClass = '';
                        switch($data['activeBooking']->status) {
                            case 'approved':
                                $statusClass = 'approved';
                                break;
                            case 'pending':
                                $statusClass = 'pending';
                                break;
                            default:
                                $statusClass = 'info';
                        }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo ucfirst($data['activeBooking']->status); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Payments Alert -->
    <?php if (!empty($data['pendingPayments'])): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Pending Payments</h3>
            <a href="<?php echo URLROOT; ?>/tenant/pay_rent" class="btn btn-primary">Pay Now</a>
        </div>

        <div class="payments-alert">
            <?php foreach (array_slice($data['pendingPayments'], 0, 3) as $payment): ?>
                <?php
                    $isOverdue = strtotime($payment->due_date) < time();
                ?>
                <div class="payment-alert-item <?php echo $isOverdue ? 'overdue' : ''; ?>">
                    <div class="payment-icon">
                        <i class="fas fa-<?php echo $isOverdue ? 'exclamation-circle' : 'clock'; ?>"></i>
                    </div>
                    <div class="payment-info">
                        <h5><?php echo htmlspecialchars($payment->property_address ?? 'Rent Payment'); ?></h5>
                        <p>
                            LKR <?php echo number_format($payment->amount * 1.10, 2); ?> -
                            Due: <?php echo date('M d, Y', strtotime($payment->due_date)); ?>
                        </p>
                    </div>
                    <a href="<?php echo URLROOT; ?>/tenant/pay_rent" class="btn btn-sm <?php echo $isOverdue ? 'btn-danger' : 'btn-primary'; ?>">
                        <?php echo $isOverdue ? 'Overdue' : 'Pay'; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Issues -->
    <?php if (!empty($data['recentIssues'])): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Recent Issues</h3>
            <a href="<?php echo URLROOT; ?>/tenant/track_issues" class="btn btn-secondary">View All</a>
        </div>

        <div class="issues-list">
            <?php foreach ($data['recentIssues'] as $issue): ?>
                <?php
                    $statusClass = '';
                    switch($issue->status) {
                        case 'resolved':
                            $statusClass = 'approved';
                            break;
                        case 'in_progress':
                            $statusClass = 'info';
                            break;
                        case 'pending':
                            $statusClass = 'pending';
                            break;
                        default:
                            $statusClass = 'secondary';
                    }
                ?>
                <div class="issue-item">
                    <div class="issue-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <div class="issue-content">
                        <h5><?php echo htmlspecialchars($issue->title); ?></h5>
                        <p><?php echo htmlspecialchars(substr($issue->description, 0, 100)); ?>...</p>
                        <span class="issue-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($issue->created_at)); ?>
                        </span>
                    </div>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $issue->status)); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Quick Actions</h3>
        </div>

        <div class="quick-actions-grid">
            <a href="<?php echo URLROOT; ?>/tenant/search_properties" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="action-content">
                    <h4>Search Properties</h4>
                    <p>Find new rental properties</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/pay_rent" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="action-content">
                    <h4>Pay Rent</h4>
                    <p>Make rent payments</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/report_issue" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="action-content">
                    <h4>Report Issue</h4>
                    <p>Report maintenance issues</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/bookings" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="action-content">
                    <h4>My Bookings</h4>
                    <p>View your bookings</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/agreements" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="action-content">
                    <h4>Lease Agreements</h4>
                    <p>View your agreements</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/notifications" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="action-content">
                    <h4>Notifications</h4>
                    <p>View all notifications</p>
                    <?php if (($data['unreadNotifications'] ?? 0) > 0): ?>
                        <span class="badge-count"><?php echo $data['unreadNotifications']; ?></span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.stat-card.warning {
    border-left: 4px solid #f39c12;
}

.stat-subtext {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.warning-text {
    color: #f39c12;
    font-weight: 600;
}

.info-text {
    color: #3498db;
    font-weight: 600;
}

.active-rental-card {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #45a9ea 0%, #3b8fd9 100%);
    color: white;
    border-radius: 12px;
}

.rental-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-size: 32px;
}

.rental-details {
    flex: 1;
}

.rental-details h4 {
    margin-bottom: 10px;
    font-size: 20px;
}

.rental-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.rental-info span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rental-status {
    display: flex;
    align-items: center;
    gap: 15px;
}

.lease-days {
    font-size: 14px;
    opacity: 0.9;
}

.payments-alert {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-alert-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #fff3cd;
    border-left: 4px solid #f39c12;
    border-radius: 6px;
}

.payment-alert-item.overdue {
    background: #f8d7da;
    border-left-color: #e74c3c;
}

.payment-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    font-size: 20px;
    color: #856404;
}

.payment-alert-item.overdue .payment-icon {
    color: #721c24;
}

.payment-info {
    flex: 1;
}

.payment-info h5 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #856404;
}

.payment-alert-item.overdue .payment-info h5 {
    color: #721c24;
}

.payment-info p {
    margin: 0;
    font-size: 14px;
    color: #856404;
}

.payment-alert-item.overdue .payment-info p {
    color: #721c24;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.issues-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.issue-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
}

.issue-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #45a9ea;
    font-size: 18px;
    flex-shrink: 0;
}

.issue-content {
    flex: 1;
}

.issue-content h5 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.issue-content p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 14px;
}

.issue-date {
    font-size: 12px;
    color: #999;
}

.quick-action-item {
    position: relative;
}

.badge-count {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #e74c3c;
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
