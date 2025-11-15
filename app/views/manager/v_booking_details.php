<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="header-content">
            <a href="<?php echo URLROOT; ?>/manager/bookings" class="btn btn-secondary" style="margin-bottom:1rem;">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
            <h2 class="page-title">
                <i class="fas fa-file-alt"></i> Booking Details
            </h2>
            <p class="page-subtitle">Review and manage booking request</p>
        </div>
    </div>

    <?php flash('booking_message'); ?>

    <?php if (isset($data['booking'])): ?>
        <?php $booking = $data['booking']; ?>

        <div class="booking-details-card">
            <div class="card-header">
                <div>
                    <h3>Booking #<?php echo $booking->id; ?></h3>
                    <p class="booking-date">Created: <?php echo date('F d, Y', strtotime($booking->created_at)); ?></p>
                </div>
                <span class="status-badge status-<?php echo strtolower($booking->status); ?>">
                    <?php echo ucfirst($booking->status); ?>
                </span>
            </div>

            <div class="card-body">
                <!-- Tenant Information -->
                <div class="info-section">
                    <h4><i class="fas fa-user"></i> Tenant Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($booking->tenant_name ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($booking->tenant_email ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Property Information -->
                <div class="info-section">
                    <h4><i class="fas fa-home"></i> Property Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Address:</label>
                            <span><?php echo htmlspecialchars($booking->address ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Type:</label>
                            <span><?php echo ucfirst($booking->property_type ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Bedrooms:</label>
                            <span><?php echo $booking->bedrooms ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Bathrooms:</label>
                            <span><?php echo $booking->bathrooms ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Booking Dates -->
                <div class="info-section">
                    <h4><i class="fas fa-calendar"></i> Booking Period</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Move-in Date:</label>
                            <span><?php echo date('F d, Y', strtotime($booking->move_in_date)); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Move-out Date:</label>
                            <span><?php echo date('F d, Y', strtotime($booking->move_out_date)); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Duration:</label>
                            <span>
                                <?php
                                    $start = new DateTime($booking->move_in_date);
                                    $end = new DateTime($booking->move_out_date);
                                    $interval = $start->diff($end);
                                    echo $interval->days . ' days';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="info-section">
                    <h4><i class="fas fa-dollar-sign"></i> Financial Details</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Monthly Rent:</label>
                            <span class="amount">Rs <?php echo number_format($booking->monthly_rent * 1.10); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Deposit Amount:</label>
                            <span class="amount">Rs <?php echo number_format($booking->deposit_amount); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Total Amount:</label>
                            <span class="amount total">Rs <?php echo number_format($booking->total_amount * 1.10); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <?php if (!empty($booking->notes)): ?>
                    <div class="info-section">
                        <h4><i class="fas fa-sticky-note"></i> Tenant Notes</h4>
                        <p class="notes"><?php echo nl2br(htmlspecialchars($booking->notes)); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Rejection Reason (if applicable) -->
                <?php if ($booking->status === 'rejected' && !empty($booking->rejection_reason)): ?>
                    <div class="info-section rejection-section">
                        <h4><i class="fas fa-exclamation-circle"></i> Rejection Reason</h4>
                        <p class="rejection-reason"><?php echo nl2br(htmlspecialchars($booking->rejection_reason)); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <?php if ($booking->status === 'pending'): ?>
                    <div class="action-section">
                        <h4><i class="fas fa-tasks"></i> Actions</h4>
                        <div class="action-buttons">
                            <form method="POST" action="<?php echo URLROOT; ?>/bookings/approve/<?php echo $booking->id; ?>" style="display:inline;"
                                  onsubmit="return confirm('Are you sure you want to approve this booking request? This will create a lease agreement.');">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve Booking
                                </button>
                            </form>
                            <button class="btn btn-danger" onclick="showRejectModal()">
                                <i class="fas fa-times"></i> Reject Booking
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">Booking not found.</div>
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
        <form method="POST" action="<?php echo URLROOT; ?>/bookings/reject/<?php echo $booking->id ?? ''; ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejection_reason">Rejection Reason <span style="color: red;">*</span></label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required
                              placeholder="Please provide a clear reason for rejecting this booking..."></textarea>
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
    .booking-details-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #45a9ea 0%, #3b8fd9 100%);
        color: white;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.75rem;
    }

    .booking-date {
        margin: 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .status-badge {
        padding: 0.625rem 1.25rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.875rem;
        background: rgba(255, 255, 255, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-pending {
        background: #fbbf24;
        color: #78350f;
    }

    .status-approved {
        background: #10b981;
        color: white;
    }

    .status-rejected {
        background: #ef4444;
        color: white;
    }

    .card-body {
        padding: 2rem;
    }

    .info-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-section:last-child {
        border-bottom: none;
    }

    .info-section h4 {
        color: #1f2937;
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-weight: 600;
    }

    .info-section h4 i {
        color: #45a9ea;
        font-size: 1.25rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .info-item label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-item span {
        color: #1f2937;
        font-size: 1rem;
    }

    .amount {
        font-weight: 600;
        color: #059669;
        font-size: 1.125rem;
    }

    .amount.total {
        font-size: 1.375rem;
        color: #45a9ea;
    }

    .notes {
        color: #4b5563;
        line-height: 1.6;
        padding: 1.25rem;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 4px solid #45a9ea;
        margin: 0;
    }

    .rejection-section {
        background: #fef2f2;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #ef4444;
        border-bottom: none;
    }

    .rejection-reason {
        color: #991b1b;
        line-height: 1.6;
        margin: 0;
    }

    .action-section {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 8px;
        border: 2px dashed #d1d5db;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
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
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<script>
    function showRejectModal() {
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.id === 'rejectModal') {
            closeRejectModal();
        }
    });
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
