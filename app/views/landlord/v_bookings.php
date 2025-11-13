<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Property Bookings</h1>
        <p class="page-subtitle">Manage booking requests for your properties</p>
    </div>
</div>

<?php flash('booking_message'); ?>

<!-- Booking Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Bookings</h3>
            <div class="stat-value"><?php echo $data['bookingStats']->total_bookings ?? 0; ?></div>
            <div class="stat-change">All time</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Pending</h3>
            <div class="stat-value"><?php echo $data['bookingStats']->pending_bookings ?? 0; ?></div>
            <div class="stat-change">Awaiting response</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Approved</h3>
            <div class="stat-value"><?php echo $data['bookingStats']->approved_bookings ?? 0; ?></div>
            <div class="stat-change">Accepted bookings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-home"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Active</h3>
            <div class="stat-value"><?php echo $data['bookingStats']->active_bookings ?? 0; ?></div>
            <div class="stat-change">Currently occupied</div>
        </div>
    </div>
</div>

<!-- Bookings List -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">All Bookings</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($data['bookings'])): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Tenant</th>
                            <th>Move-in Date</th>
                            <th>Move-out Date</th>
                            <th>Monthly Rent</th>
                            <th>Deposit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['bookings'] as $booking): ?>
                            <?php
                                $statusClass = '';
                                switch($booking->status) {
                                    case 'pending':
                                        $statusClass = 'warning';
                                        break;
                                    case 'approved':
                                        $statusClass = 'success';
                                        break;
                                    case 'active':
                                        $statusClass = 'info';
                                        break;
                                    case 'rejected':
                                    case 'cancelled':
                                        $statusClass = 'danger';
                                        break;
                                    case 'completed':
                                        $statusClass = 'secondary';
                                        break;
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking->address ?? 'N/A'); ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking->tenant_name ?? 'N/A'); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars($booking->tenant_email ?? ''); ?></small>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking->move_in_date)); ?></td>
                                <td>
                                    <?php
                                        echo $booking->move_out_date
                                            ? date('M d, Y', strtotime($booking->move_out_date))
                                            : 'Ongoing';
                                    ?>
                                </td>
                                <td>LKR <?php echo number_format($booking->monthly_rent, 2); ?></td>
                                <td>LKR <?php echo number_format($booking->deposit_amount, 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo URLROOT; ?>/bookings/details/<?php echo $booking->id; ?>"
                                           class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($booking->status === 'pending'): ?>
                                            <a href="<?php echo URLROOT; ?>/bookings/approve/<?php echo $booking->id; ?>"
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Approve this booking request?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="<?php echo URLROOT; ?>/bookings/reject/<?php echo $booking->id; ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Reject this booking request?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <p>No bookings yet</p>
                <span>Booking requests will appear here when tenants book your properties.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.btn-group {
    display: flex;
    gap: 5px;
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
