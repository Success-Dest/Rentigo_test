<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <div class="page-header">
        <a href="<?php echo URLROOT; ?>/tenant/bookings" class="btn btn-secondary" style="margin-bottom:1.3em;">
            <i class="fas fa-arrow-left"></i> Back to My Bookings
        </a>
        <h2 style="padding-bottom: 0.1em;">Booking Details</h2>
        <p style="margin-bottom:2.2em;">View complete information about your booking request.</p>
    </div>

    <?php flash('booking_message'); ?>

    <?php if (isset($data['booking'])): ?>
        <?php $booking = $data['booking']; ?>

        <div class="booking-details-card">
            <div class="card-header">
                <h3>Booking #<?php echo $booking->id; ?></h3>
                <span class="status-badge status-<?php echo strtolower($booking->status); ?>">
                    <?php echo ucfirst($booking->status); ?>
                </span>
            </div>

            <div class="card-body">
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
                        <div class="info-item">
                            <label>Booking Date:</label>
                            <span><?php echo date('F d, Y', strtotime($booking->created_at)); ?></span>
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
                        <h4><i class="fas fa-sticky-note"></i> Additional Notes</h4>
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

                <!-- Status Messages -->
                <div class="info-section">
                    <?php if ($booking->status === 'pending'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i>
                            <strong>Pending Approval:</strong> Your booking request is waiting for Property Manager approval.
                        </div>
                    <?php elseif ($booking->status === 'approved'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Booking Approved!</strong> Your booking has been approved. Please proceed with the lease agreement and payment.
                        </div>
                    <?php elseif ($booking->status === 'rejected'): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            <strong>Booking Rejected:</strong> Unfortunately, your booking request has been rejected.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">Booking not found.</div>
    <?php endif; ?>
</div>

<style>
    .booking-details-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #45a9ea 0%, #3b8fd9 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        margin: 0;
        font-size: 1.5rem;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        background: rgba(255, 255, 255, 0.2);
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
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-section h4 i {
        color: #45a9ea;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
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
        font-size: 1.25rem;
        color: #45a9ea;
    }

    .notes {
        color: #4b5563;
        line-height: 1.6;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 4px solid #45a9ea;
    }

    .rejection-section {
        background: #fef2f2;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #ef4444;
    }

    .rejection-reason {
        color: #991b1b;
        line-height: 1.6;
        margin: 0;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .alert i {
        font-size: 1.25rem;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
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
    }
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
