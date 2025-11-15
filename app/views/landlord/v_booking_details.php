<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="header-content">
            <a href="<?php echo URLROOT; ?>/landlord/bookings" class="btn btn-secondary" style="margin-bottom:1rem;">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
            <h2 class="page-title">
                <i class="fas fa-file-alt"></i> Booking Details
            </h2>
            <p class="page-subtitle">Review booking information</p>
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
                            <label>Monthly Rent (Your Income):</label>
                            <span class="amount">LKR <?php echo number_format($booking->monthly_rent, 2); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Deposit Amount:</label>
                            <span class="amount">LKR <?php echo number_format($booking->deposit_amount, 2); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Total Amount:</label>
                            <span class="amount total">LKR <?php echo number_format($booking->total_amount, 2); ?></span>
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

    .status-active {
        background: #3b82f6;
        color: white;
    }

    .status-rejected {
        background: #ef4444;
        color: white;
    }

    .status-completed {
        background: #6b7280;
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

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
