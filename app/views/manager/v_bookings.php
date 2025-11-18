<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Booking Management</h1>
        <p class="page-subtitle">Manage booking requests for your assigned properties</p>
    </div>
</div>

<?php flash('booking_message'); ?>

<!-- Booking Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Pending</h3>
            <div class="stat-value"><?php echo $data['pendingCount'] ?? 0; ?></div>
            <div class="stat-change">Awaiting response</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Approved</h3>
            <div class="stat-value"><?php echo $data['approvedCount'] ?? 0; ?></div>
            <div class="stat-change">Accepted bookings</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Rejected</h3>
            <div class="stat-value"><?php echo $data['rejectedCount'] ?? 0; ?></div>
            <div class="stat-change">Declined requests</div>
        </div>
    </div>
</div>

    <!-- Tabs Navigation -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" data-tab="pending">
                <i class="fas fa-clock"></i> Pending (<?php echo $data['pendingCount'] ?? 0; ?>)
            </button>
            <button class="tab-btn" data-tab="approved">
                <i class="fas fa-check-circle"></i> Approved (<?php echo $data['approvedCount'] ?? 0; ?>)
            </button>
            <button class="tab-btn" data-tab="rejected">
                <i class="fas fa-times-circle"></i> Rejected (<?php echo $data['rejectedCount'] ?? 0; ?>)
            </button>
        </div>

        <!-- Pending Bookings Tab -->
        <div id="pending" class="tab-content active">
            <?php if (!empty($data['pendingBookings'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Move-in Date</th>
                                <th>Move-out Date</th>
                                <th>Monthly Rent</th>
                                <th>Deposit</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['pendingBookings'] as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->address); ?></strong><br>
                                        <small><?php echo ucfirst($booking->property_type); ?> - <?php echo $booking->bedrooms; ?>BR</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->tenant_name); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking->tenant_email); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking->move_in_date)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking->move_out_date)); ?></td>
                                    <td>Rs <?php echo number_format($booking->monthly_rent * 1.10); ?></td>
                                    <td>Rs <?php echo number_format($booking->deposit_amount); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking->created_at)); ?></td>
                                    <td class="actions">
                                        <form method="POST" action="<?php echo URLROOT; ?>/bookings/approve/<?php echo $booking->id; ?>" style="display:inline;"
                                              onsubmit="return confirm('Approve this booking request?');">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <a href="<?php echo URLROOT; ?>/bookings/details/<?php echo $booking->id; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $booking->id; ?>)">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No pending booking requests</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Approved Bookings Tab -->
        <div id="approved" class="tab-content">
            <?php if (!empty($data['approvedBookings'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Move-in Date</th>
                                <th>Move-out Date</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['approvedBookings'] as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->address); ?></strong><br>
                                        <small><?php echo ucfirst($booking->property_type); ?> - <?php echo $booking->bedrooms; ?>BR</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->tenant_name); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking->tenant_email); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking->move_in_date)); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking->move_out_date)); ?></td>
                                    <td>Rs <?php echo number_format($booking->monthly_rent * 1.10); ?></td>
                                    <td><span class="badge badge-success">Approved</span></td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/bookings/details/<?php echo $booking->id; ?>" class="btn btn-sm btn-primary">
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
                    <p>No approved bookings</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rejected Bookings Tab -->
        <div id="rejected" class="tab-content">
            <?php if (!empty($data['rejectedBookings'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Move-in Date</th>
                                <th>Rejection Reason</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['rejectedBookings'] as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->address); ?></strong><br>
                                        <small><?php echo ucfirst($booking->property_type); ?> - <?php echo $booking->bedrooms; ?>BR</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking->tenant_name); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking->tenant_email); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking->move_in_date)); ?></td>
                                    <td><?php echo htmlspecialchars($booking->rejection_reason ?? 'No reason provided'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking->created_at)); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo URLROOT; ?>/bookings/details/<?php echo $booking->id; ?>" class="btn btn-sm btn-primary">
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
                    <p>No rejected bookings</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Reject Booking Modal -->
<div id="rejectModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Reject Booking</h3>
            <button class="modal-close" onclick="closeRejectModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejection_reason">Rejection Reason <span style="color: red;">*</span></label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required
                              placeholder="Please provide a reason for rejecting this booking..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Reject Booking
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: #45a9ea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 2px;
    }

    .stat-change {
        font-size: 12px;
        color: #999;
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

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
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

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
    }

    .modal-close:hover {
        color: #1f2937;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #374151;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #45a9ea;
        box-shadow: 0 0 0 3px rgba(69, 169, 234, 0.1);
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

    // Reject booking modal
    function showRejectModal(bookingId) {
        const form = document.getElementById('rejectForm');
        form.action = '<?php echo URLROOT; ?>/bookings/reject/' + bookingId;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('rejectForm').reset();
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            closeRejectModal();
        }
    });
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
