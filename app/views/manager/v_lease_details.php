<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <div class="header-content">
            <a href="<?php echo URLROOT; ?>/manager/leases" class="btn btn-secondary" style="margin-bottom:1rem;">
                <i class="fas fa-arrow-left"></i> Back to Leases
            </a>
            <h2 class="page-title">
                <i class="fas fa-file-contract"></i> Lease Agreement Details
            </h2>
            <p class="page-subtitle">View complete lease agreement information</p>
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
                <!-- Tenant Information -->
                <div class="info-section">
                    <h4><i class="fas fa-user"></i> Tenant Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($lease->tenant_email ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

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
                            <span class="amount">Rs <?php echo number_format($lease->monthly_rent); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Security Deposit:</label>
                            <span class="amount">Rs <?php echo number_format($lease->deposit_amount); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Total Rent (Full Term):</label>
                            <span class="amount total">Rs <?php echo number_format($lease->monthly_rent * ($lease->lease_duration_months ?? 1)); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Lease Signatures -->
                <div class="info-section">
                    <h4><i class="fas fa-file-signature"></i> Signatures</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Tenant Signed:</label>
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
                            <label>Landlord Signed:</label>
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
                    <div class="payment-schedule">
                        <?php
                            $start = new DateTime($lease->start_date);
                            $end = new DateTime($lease->end_date);
                            $months = $lease->lease_duration_months ?? 1;

                            for ($i = 0; $i < $months; $i++) {
                                $paymentDate = clone $start;
                                $paymentDate->modify("+$i month");
                                echo '<div class="payment-item">';
                                echo '<span class="payment-month">Month ' . ($i + 1) . '</span>';
                                echo '<span class="payment-date">' . $paymentDate->format('F Y') . '</span>';
                                echo '<span class="payment-amount">Rs ' . number_format($lease->monthly_rent) . '</span>';
                                echo '</div>';
                            }
                        ?>
                    </div>
                </div>
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
    }
</style>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
