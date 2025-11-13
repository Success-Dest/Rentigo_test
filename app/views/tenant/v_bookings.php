<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>My Bookings</h2>
            <p>View and manage your property bookings</p>
        </div>
    </div>

    <!-- Booking Statistics -->
    <?php if (isset($data['bookingStats']) && $data['bookingStats']): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <h3><?php echo $data['bookingStats']->pending ?? 0; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
                <h3><?php echo $data['bookingStats']->approved ?? 0; ?></h3>
                <p>Approved</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-home"></i></div>
            <div class="stat-content">
                <h3><?php echo $data['bookingStats']->active ?? 0; ?></h3>
                <p>Active</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-history"></i></div>
            <div class="stat-content">
                <h3><?php echo $data['bookingStats']->completed ?? 0; ?></h3>
                <p>Completed</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Flash Message -->
    <?php flash('booking_message'); ?>

    <!-- Current/Active Bookings -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Active & Pending Bookings</h3>
        </div>

        <?php if (!empty($data['bookings'])): ?>
            <?php
            $activeBookings = array_filter($data['bookings'], function($booking) {
                return in_array($booking->status, ['pending', 'approved', 'active']);
            });
            ?>

            <?php if (!empty($activeBookings)): ?>
                <div class="bookings-list">
                    <?php foreach ($activeBookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-details">
                                <h4><?php echo htmlspecialchars($booking->address); ?></h4>
                                <p class="booking-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo ucfirst($booking->property_type); ?>
                                </p>
                                <p class="booking-price">LKR <?php echo number_format($booking->monthly_rent, 2); ?>/month</p>
                                <div class="booking-dates">
                                    <span><i class="fas fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($booking->move_in_date)); ?> -
                                        <?php echo date('M d, Y', strtotime($booking->move_out_date)); ?>
                                    </span>
                                </div>
                                <?php if ($booking->notes): ?>
                                    <p class="booking-notes"><small><?php echo htmlspecialchars($booking->notes); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <div class="booking-status">
                                <?php
                                $statusClass = '';
                                switch($booking->status) {
                                    case 'active': $statusClass = 'approved'; break;
                                    case 'approved': $statusClass = 'approved'; break;
                                    case 'pending': $statusClass = 'pending'; break;
                                    case 'rejected': $statusClass = 'rejected'; break;
                                    case 'cancelled': $statusClass = 'cancelled'; break;
                                    default: $statusClass = '';
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking->status); ?></span>
                                <div class="booking-actions">
                                    <a href="<?php echo URLROOT; ?>/bookings/view/<?php echo $booking->id; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    <?php if ($booking->status == 'pending'): ?>
                                        <button onclick="cancelBooking(<?php echo $booking->id; ?>)" class="btn btn-danger btn-sm">Cancel</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No active bookings at the moment</p>
                    <a href="<?php echo URLROOT; ?>/tenantproperties/index" class="btn btn-primary">Browse Properties</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No bookings yet</p>
                <a href="<?php echo URLROOT; ?>/tenantproperties/index" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Booking History -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Booking History</h3>
        </div>

        <?php if (!empty($data['bookings'])): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Monthly Rent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['bookings'] as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($booking->address, 0, 40)) . '...'; ?></td>
                                <td><?php echo ucfirst($booking->property_type); ?></td>
                                <td>
                                    <?php echo date('M Y', strtotime($booking->move_in_date)); ?> -
                                    <?php echo date('M Y', strtotime($booking->move_out_date)); ?>
                                </td>
                                <td>LKR <?php echo number_format($booking->monthly_rent, 0); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch($booking->status) {
                                        case 'active': $statusClass = 'approved'; break;
                                        case 'approved': $statusClass = 'approved'; break;
                                        case 'pending': $statusClass = 'pending'; break;
                                        case 'rejected': $statusClass = 'rejected'; break;
                                        case 'completed': $statusClass = 'completed'; break;
                                        case 'cancelled': $statusClass = 'cancelled'; break;
                                        default: $statusClass = '';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking->status); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo URLROOT; ?>/bookings/view/<?php echo $booking->id; ?>" class="btn btn-secondary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No booking history</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Quick Actions</h3>
        </div>

        <div class="quick-actions-grid">
            <a href="<?php echo URLROOT; ?>/tenantproperties/index" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="action-content">
                    <h4>Search Properties</h4>
                    <p>Find new rental properties</p>
                </div>
            </a>

            <a href="<?php echo URLROOT; ?>/tenant/agreements" class="quick-action-item">
                <div class="action-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="action-content">
                    <h4>View Agreements</h4>
                    <p>Check your lease agreements</p>
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
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelBookingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeCancelModal()">&times;</span>
        <h3>Cancel Booking</h3>
        <form id="cancelBookingForm" method="POST" action="">
            <div class="form-group">
                <label>Reason for Cancellation *</label>
                <textarea name="cancellation_reason" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Close</button>
                <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

<script>
function cancelBooking(bookingId) {
    const modal = document.getElementById('cancelBookingModal');
    const form = document.getElementById('cancelBookingForm');
    form.action = '<?php echo URLROOT; ?>/bookings/cancel/' + bookingId;
    modal.style.display = 'block';
}

function closeCancelModal() {
    document.getElementById('cancelBookingModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('cancelBookingModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
