<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <div class="page-header">
        <div class="header-content">
            <a href="<?php echo URLROOT; ?>/tenant/agreements" class="btn btn-secondary" style="margin-bottom:1rem;">
                <i class="fas fa-arrow-left"></i> Back to Agreements
            </a>
            <h2 class="page-title">
                <i class="fas fa-file-contract"></i> Lease Agreement Details
            </h2>
            <p class="page-subtitle">View your complete lease agreement information</p>
        </div>
    </div>

    <?php flash('lease_message'); ?>

    <?php if (isset($data['lease'])): ?>
        <?php $lease = $data['lease']; ?>

        <div class="lease-details-card">
            <div class="card-header">
                <div>
                    <h3>Lease Agreement #<?php echo $lease->id; ?></h3>
                    <p class="lease-date">Created: <?php echo date('F d, Y', strtotime($lease->created_at)); ?></p>
                </div>
                <span class="status-badge status-<?php echo strtolower($lease->status); ?>">
                    <?php echo ucfirst($lease->status); ?>
                </span>
            </div>

            <div class="card-body">
                <!-- Property Information -->
                <div class="info-section">
                    <h4><i class="fas fa-home"></i> Property Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Address:</label>
                            <span><?php echo htmlspecialchars($lease->address ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Type:</label>
                            <span><?php echo ucfirst($lease->property_type ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Bedrooms:</label>
                            <span><?php echo $lease->bedrooms ?? 'N/A'; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Bathrooms:</label>
                            <span><?php echo $lease->bathrooms ?? 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Landlord Information -->
                <div class="info-section">
                    <h4><i class="fas fa-user-tie"></i> Landlord Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($lease->landlord_name ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($lease->landlord_email ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Lease Period -->
                <div class="info-section">
                    <h4><i class="fas fa-calendar-alt"></i> Lease Period</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Start Date:</label>
                            <span><?php echo date('F d, Y', strtotime($lease->start_date)); ?></span>
                        </div>
                        <div class="info-item">
                            <label>End Date:</label>
                            <span><?php echo date('F d, Y', strtotime($lease->end_date)); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Duration:</label>
                            <span><?php echo $lease->lease_duration_months ?? 'N/A'; ?> months</span>
                        </div>
                        <div class="info-item">
                            <label>Days Remaining:</label>
                            <span>
                                <?php
                                    $today = new DateTime();
                                    $end = new DateTime($lease->end_date);
                                    $diff = $today->diff($end);
                                    if ($diff->invert) {
                                        echo 'Expired';
                                    } else {
                                        echo $diff->days . ' days';
                                    }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="info-section">
                    <h4><i class="fas fa-dollar-sign"></i> Financial Details</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Monthly Rent:</label>
                            <span class="amount">LKR <?php echo number_format($lease->monthly_rent, 2); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Security Deposit:</label>
                            <span class="amount">LKR <?php echo number_format($lease->deposit_amount, 2); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Total Rent (Full Term):</label>
                            <span class="amount total">LKR <?php echo number_format($lease->monthly_rent * ($lease->lease_duration_months ?? 1), 2); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Payment Day:</label>
                            <span>Day <?php echo $lease->payment_day ?? 1; ?> of each month</span>
                        </div>
                    </div>
                </div>

                <!-- Lease Signatures -->
                <div class="info-section">
                    <h4><i class="fas fa-file-signature"></i> Signatures</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Your Signature:</label>
                            <span>
                                <?php if ($lease->signed_by_tenant): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Signed on <?php echo date('M d, Y', strtotime($lease->tenant_signature_date)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Landlord Signature:</label>
                            <span>
                                <?php if ($lease->signed_by_landlord): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Signed on <?php echo date('M d, Y', strtotime($lease->landlord_signature_date)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!$lease->signed_by_tenant && $lease->status === 'pending_signatures'): ?>
                        <div style="margin-top: 1.5rem;">
                            <a href="<?php echo URLROOT; ?>/leaseagreements/signTenant/<?php echo $lease->id; ?>"
                               class="btn btn-primary btn-lg"
                               onclick="return confirm('Are you sure you want to sign this lease agreement? This action cannot be undone.');">
                                <i class="fas fa-pen"></i> Sign Lease Agreement
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Terms and Conditions -->
                <?php if (!empty($lease->terms_and_conditions)): ?>
                    <div class="info-section">
                        <h4><i class="fas fa-file-alt"></i> Terms and Conditions</h4>
                        <div class="terms-box">
                            <?php echo nl2br(htmlspecialchars($lease->terms_and_conditions)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Payment Schedule -->
                <div class="info-section">
                    <h4><i class="fas fa-calendar-check"></i> Payment Schedule</h4>
                    <p style="color: #6b7280; margin-bottom: 1rem;">
                        Your monthly rent payments are due on day <?php echo $lease->payment_day ?? 1; ?> of each month.
                    </p>
                    <div class="payment-schedule">
                        <?php
                            $start = new DateTime($lease->start_date);
                            $end = new DateTime($lease->end_date);
                            $months = $lease->lease_duration_months ?? 1;

                            for ($i = 0; $i < $months; $i++) {
                                $paymentDate = clone $start;
                                $paymentDate->modify("+$i month");

                                // Determine if this payment is in the past
                                $today = new DateTime();
                                $isPast = $paymentDate < $today;
                                $isCurrent = $paymentDate->format('Y-m') === $today->format('Y-m');

                                echo '<div class="payment-item' . ($isCurrent ? ' current-month' : '') . '">';
                                echo '<span class="payment-month">Month ' . ($i + 1) . '</span>';
                                echo '<span class="payment-date">' . $paymentDate->format('F Y') . '</span>';
                                echo '<span class="payment-amount">LKR ' . number_format($lease->monthly_rent, 2) . '</span>';
                                echo '</div>';
                            }
                        ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <?php if ($lease->status === 'active' || $lease->status === 'completed'): ?>
                    <div class="action-buttons">
                        <a href="<?php echo URLROOT; ?>/leaseagreements/download/<?php echo $lease->id; ?>"
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">Lease agreement not found.</div>
    <?php endif; ?>
</div>

<style>
    .lease-details-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
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

    .lease-date {
        margin: 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .status-badge {
        padding: 0.625rem 1.25rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-draft {
        background: #fef3c7;
        color: #92400e;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-pending_signatures {
        background: #fde68a;
        color: #78350f;
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

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
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

    .terms-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        color: #4b5563;
        line-height: 1.6;
        border-left: 4px solid #45a9ea;
    }

    .payment-schedule {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .payment-item {
        display: grid;
        grid-template-columns: 100px 1fr auto;
        gap: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 3px solid #45a9ea;
        transition: all 0.2s;
    }

    .payment-item.current-month {
        background: #fef3c7;
        border-left-color: #f59e0b;
    }

    .payment-month {
        font-weight: 600;
        color: #6b7280;
    }

    .payment-date {
        color: #1f2937;
    }

    .payment-amount {
        font-weight: 600;
        color: #059669;
        font-size: 1.125rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn-lg {
        padding: 0.875rem 1.75rem;
        font-size: 1.125rem;
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

        .payment-item {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
