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

<!-- Filter Section -->
<div class="filter-container">
    <form method="GET" action="<?php echo URLROOT; ?>/manager/bookings" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="all" <?php echo ($data['currentStatusFilter'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                    <option value="pending" <?php echo ($data['currentStatusFilter'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo ($data['currentStatusFilter'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo ($data['currentStatusFilter'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="property_id">Property</label>
                <select name="property_id" id="property_id" class="form-control">
                    <option value="all" <?php echo ($data['currentPropertyFilter'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Properties</option>
                    <?php if (!empty($data['assignedProperties'])): ?>
                        <?php foreach ($data['assignedProperties'] as $property): ?>
                            <option value="<?php echo $property->id; ?>" <?php echo ($data['currentPropertyFilter'] ?? '') == $property->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($property->address); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="date_from">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($data['currentDateFromFilter'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <label for="date_to">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($data['currentDateToFilter'] ?? ''); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="<?php echo URLROOT; ?>/manager/bookings" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="table-container-wrapper">
    <?php if (!empty($data['allBookings'])): ?>
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
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['allBookings'] as $booking): ?>
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
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = ucfirst($booking->status);
                                switch($booking->status) {
                                    case 'pending':
                                        $statusClass = 'badge-warning';
                                        break;
                                    case 'approved':
                                        $statusClass = 'badge-success';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'badge-danger';
                                        break;
                                    default:
                                        $statusClass = 'badge-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($booking->created_at)); ?></td>
                            <td class="actions">
                                <?php if ($booking->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo URLROOT; ?>/bookings/approve/<?php echo $booking->id; ?>" style="display:inline;"
                                          onsubmit="return confirm('Approve this booking request?');">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" onclick="showRejectModal(<?php echo $booking->id; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>
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
            <p>No bookings found<?php echo (isset($data['currentStatusFilter']) && $data['currentStatusFilter'] !== 'all') || (isset($data['currentPropertyFilter']) && $data['currentPropertyFilter'] !== 'all') ? ' matching your filters' : ''; ?></p>
        </div>
    <?php endif; ?>
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

    .filter-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 30px;
    }

    .filter-form {
        width: 100%;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .table-container-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table-container {
        overflow-x: auto;
        padding: 1.5rem;
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

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #374151;
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

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #45a9ea;
        color: white;
    }

    .btn-primary:hover {
        background: #3a8bc7;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
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

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            width: 100%;
        }

        .filter-actions .btn {
            flex: 1;
        }
    }
</style>

<script>
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