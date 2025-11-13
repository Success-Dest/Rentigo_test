<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="dashboard-content">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <p>Here's an overview of your rental properties.</p>
    </div>

    <?php flash('dashboard_message'); ?>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Total Properties</h3>
                <div class="stat-value"><?php echo $data['propertyStats']->total_properties ?? 0; ?></div>
                <div class="stat-change">
                    <?php echo ($data['propertyStats']->active_properties ?? 0); ?> active listings
                </div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Active Leases</h3>
                <div class="stat-value"><?php echo $data['activeLeases'] ?? 0; ?></div>
                <div class="stat-change positive">Currently occupied</div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Pending Bookings</h3>
                <div class="stat-value"><?php echo $data['pendingBookings'] ?? 0; ?></div>
                <div class="stat-change">
                    <?php if (($data['pendingBookings'] ?? 0) > 0): ?>
                        Requires attention
                    <?php else: ?>
                        All caught up
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Total Income</h3>
                <div class="stat-value">
                    LKR <?php echo number_format($data['totalIncome'] ?? 0, 0); ?>
                </div>
                <div class="stat-change positive">All time earnings</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Maintenance</h3>
                <div class="stat-value"><?php echo $data['pendingMaintenance'] ?? 0; ?></div>
                <div class="stat-change">Pending requests</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Messages</h3>
                <div class="stat-value"><?php echo $data['unreadMessages'] ?? 0; ?></div>
                <div class="stat-change">Unread inquiries</div>
            </div>
        </div>
    </div>

    <!-- Pending Bookings Alert -->
    <?php if (!empty($data['recentBookings']) && ($data['pendingBookings'] ?? 0) > 0): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Pending Booking Requests</h2>
            <a href="<?php echo URLROOT; ?>/landlord/bookings" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <div class="bookings-list">
                <?php
                $pendingCount = 0;
                foreach ($data['recentBookings'] as $booking):
                    if ($booking->status === 'pending' && $pendingCount < 5):
                        $pendingCount++;
                ?>
                    <div class="booking-request-item">
                        <div class="booking-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="booking-info">
                            <h5><?php echo htmlspecialchars($booking->tenant_name ?? 'Tenant'); ?></h5>
                            <p>
                                <strong><?php echo htmlspecialchars($booking->address ?? 'Property'); ?></strong><br>
                                Move-in: <?php echo date('M d, Y', strtotime($booking->move_in_date)); ?> |
                                LKR <?php echo number_format($booking->monthly_rent, 2); ?>/month
                            </p>
                            <span class="booking-date">
                                <i class="fas fa-clock"></i>
                                Requested <?php echo date('M d, Y', strtotime($booking->created_at)); ?>
                            </span>
                        </div>
                        <div class="booking-actions">
                            <a href="<?php echo URLROOT; ?>/bookings/approve/<?php echo $booking->id; ?>"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Approve
                            </a>
                            <a href="<?php echo URLROOT; ?>/bookings/reject/<?php echo $booking->id; ?>"
                               class="btn btn-danger btn-sm">
                                <i class="fas fa-times"></i> Reject
                            </a>
                        </div>
                    </div>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Payments -->
    <?php if (!empty($data['recentPayments'])): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Recent Payments</h2>
            <a href="<?php echo URLROOT; ?>/landlord/payment_history" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body">
            <div class="activity-list">
                <?php foreach ($data['recentPayments'] as $payment): ?>
                    <?php
                        $statusClass = '';
                        $iconClass = '';
                        switch($payment->status) {
                            case 'completed':
                                $statusClass = 'success';
                                $iconClass = 'check-circle';
                                break;
                            case 'pending':
                                $statusClass = 'warning';
                                $iconClass = 'clock';
                                break;
                            case 'failed':
                                $statusClass = 'danger';
                                $iconClass = 'times-circle';
                                break;
                            default:
                                $statusClass = 'info';
                                $iconClass = 'info-circle';
                        }
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $statusClass; ?>">
                            <i class="fas fa-<?php echo $iconClass; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <strong><?php echo htmlspecialchars($payment->tenant_name ?? 'Tenant'); ?></strong>
                            <div class="activity-meta">
                                <?php echo htmlspecialchars($payment->property_address ?? 'Property'); ?> -
                                LKR <?php echo number_format($payment->amount, 2); ?>
                                <?php if ($payment->payment_date): ?>
                                    - <?php echo date('M d, Y', strtotime($payment->payment_date)); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge badge-<?php echo $statusClass; ?>">
                            <?php echo ucfirst($payment->status); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Bookings Overview -->
    <?php if (!empty($data['recentBookings'])): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Recent Bookings</h2>
            <a href="<?php echo URLROOT; ?>/landlord/bookings" class="btn btn-primary btn-sm">Manage Bookings</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Tenant</th>
                            <th>Move-in Date</th>
                            <th>Rent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recentBookings'] as $booking): ?>
                            <?php
                                $statusClass = '';
                                switch($booking->status) {
                                    case 'approved':
                                    case 'active':
                                        $statusClass = 'success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'warning';
                                        break;
                                    case 'rejected':
                                    case 'cancelled':
                                        $statusClass = 'danger';
                                        break;
                                    default:
                                        $statusClass = 'info';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking->address ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($booking->tenant_name ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking->move_in_date)); ?></td>
                                <td>LKR <?php echo number_format($booking->monthly_rent, 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo URLROOT; ?>/bookings/view/<?php echo $booking->id; ?>"
                                       class="btn btn-outline btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="content-card">
        <div class="card-body">
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <p>No bookings yet</p>
                <span>Booking requests will appear here once tenants start booking your properties.</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="<?php echo URLROOT; ?>/properties/create" class="quick-action-item">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-content">
                        <h4>Add Property</h4>
                        <p>List a new rental property</p>
                    </div>
                </a>

                <a href="<?php echo URLROOT; ?>/landlord/bookings" class="quick-action-item">
                    <div class="action-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="action-content">
                        <h4>Manage Bookings</h4>
                        <p>Review booking requests</p>
                        <?php if (($data['pendingBookings'] ?? 0) > 0): ?>
                            <span class="badge-count"><?php echo $data['pendingBookings']; ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <a href="<?php echo URLROOT; ?>/landlord/payment_history" class="quick-action-item">
                    <div class="action-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="action-content">
                        <h4>Payment History</h4>
                        <p>View income and payments</p>
                    </div>
                </a>

                <a href="<?php echo URLROOT; ?>/landlord/maintenance" class="quick-action-item">
                    <div class="action-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="action-content">
                        <h4>Maintenance</h4>
                        <p>Manage maintenance requests</p>
                        <?php if (($data['pendingMaintenance'] ?? 0) > 0): ?>
                            <span class="badge-count"><?php echo $data['pendingMaintenance']; ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <a href="<?php echo URLROOT; ?>/landlord/inquiries" class="quick-action-item">
                    <div class="action-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="action-content">
                        <h4>Messages</h4>
                        <p>View tenant inquiries</p>
                        <?php if (($data['unreadMessages'] ?? 0) > 0): ?>
                            <span class="badge-count"><?php echo $data['unreadMessages']; ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <a href="<?php echo URLROOT; ?>/landlord/notifications" class="quick-action-item">
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
</div>

<style>
.welcome-section {
    margin-bottom: 30px;
}

.welcome-section h2 {
    margin-bottom: 8px;
}

.welcome-section p {
    color: #666;
}

.bookings-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.booking-request-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f8f9fa;
    align-items: center;
}

.booking-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 8px;
    color: #667eea;
    font-size: 24px;
    flex-shrink: 0;
}

.booking-info {
    flex: 1;
}

.booking-info h5 {
    margin: 0 0 8px 0;
    font-size: 16px;
}

.booking-info p {
    margin: 0 0 8px 0;
    color: #666;
    font-size: 14px;
}

.booking-date {
    font-size: 12px;
    color: #999;
}

.booking-actions {
    display: flex;
    gap: 8px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
    align-items: center;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.activity-icon.success {
    background: #d4edda;
    color: #2ecc71;
}

.activity-icon.warning {
    background: #fff3cd;
    color: #f39c12;
}

.activity-icon.danger {
    background: #f8d7da;
    color: #e74c3c;
}

.activity-icon.info {
    background: #d1ecf1;
    color: #3498db;
}

.activity-content {
    flex: 1;
}

.activity-content strong {
    font-size: 15px;
}

.activity-meta {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.quick-action-item {
    position: relative;
    display: flex;
    gap: 15px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.quick-action-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #667eea;
    font-size: 24px;
    flex-shrink: 0;
}

.action-content h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.action-content p {
    margin: 0;
    font-size: 14px;
    color: #666;
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

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #ddd;
}

.empty-state p {
    font-size: 18px;
    margin-bottom: 8px;
    color: #666;
}

.empty-state span {
    font-size: 14px;
}
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
