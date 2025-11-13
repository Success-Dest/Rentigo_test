<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Lease Agreements</h2>
            <p>View and manage your rental agreements</p>
        </div>
    </div>

    <?php flash('lease_message'); ?>

    <!-- Active Lease Summary -->
    <?php if (isset($data['activeLease']) && $data['activeLease']): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Active Lease</h3>
        </div>

        <div class="active-lease-card">
            <div class="lease-header">
                <div class="lease-property">
                    <i class="fas fa-home"></i>
                    <h4><?php echo htmlspecialchars($data['activeLease']->property_address ?? 'Property'); ?></h4>
                </div>
                <span class="status-badge approved">Active</span>
            </div>
            <div class="lease-details-grid">
                <div class="lease-detail">
                    <span class="label">Start Date:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($data['activeLease']->start_date)); ?></span>
                </div>
                <div class="lease-detail">
                    <span class="label">End Date:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($data['activeLease']->end_date)); ?></span>
                </div>
                <div class="lease-detail">
                    <span class="label">Monthly Rent:</span>
                    <span class="value">LKR <?php echo number_format($data['activeLease']->monthly_rent, 2); ?></span>
                </div>
                <div class="lease-detail">
                    <span class="label">Security Deposit:</span>
                    <span class="value">LKR <?php echo number_format($data['activeLease']->deposit_amount, 2); ?></span>
                </div>
                <div class="lease-detail">
                    <span class="label">Days Remaining:</span>
                    <span class="value">
                        <?php
                            $daysRemaining = floor((strtotime($data['activeLease']->end_date) - time()) / 86400);
                            echo $daysRemaining > 0 ? $daysRemaining . ' days' : 'Expired';
                        ?>
                    </span>
                </div>
                <div class="lease-detail">
                    <span class="label">Payment Day:</span>
                    <span class="value"><?php echo $data['activeLease']->payment_day ?? 1; ?> of each month</span>
                </div>
            </div>
            <div class="lease-actions">
                <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $data['activeLease']->id; ?>"
                   class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Full Agreement
                </a>
                <a href="<?php echo URLROOT; ?>/leaseagreements/download/<?php echo $data['activeLease']->id; ?>"
                   class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lease Statistics -->
    <?php if (isset($data['leaseStats'])): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo $data['leaseStats']->total_leases ?? 0; ?></h4>
                <p>Total Agreements</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo $data['leaseStats']->active_leases ?? 0; ?></h4>
                <p>Active Leases</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo $data['leaseStats']->completed_leases ?? 0; ?></h4>
                <p>Completed</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Agreements -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>All Lease Agreements</h3>
        </div>

        <?php if (!empty($data['leases'])): ?>
            <div class="agreements-list">
                <?php foreach ($data['leases'] as $lease): ?>
                    <?php
                        $statusClass = '';
                        switch($lease->status) {
                            case 'active':
                                $statusClass = 'approved';
                                break;
                            case 'pending_signatures':
                                $statusClass = 'pending';
                                break;
                            case 'draft':
                                $statusClass = 'info';
                                break;
                            case 'completed':
                            case 'expired':
                                $statusClass = 'secondary';
                                break;
                            case 'terminated':
                            case 'cancelled':
                                $statusClass = 'rejected';
                                break;
                        }

                        $needsSignature = ($lease->status === 'pending_signatures' && !$lease->tenant_signed_at);
                    ?>
                    <div class="agreement-card">
                        <div class="agreement-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="agreement-details">
                            <h4><?php echo htmlspecialchars($lease->property_address ?? 'Rental Agreement'); ?></h4>
                            <p class="agreement-property">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($lease->property_address ?? 'N/A'); ?>
                            </p>
                            <div class="agreement-info">
                                <span>
                                    <strong>Period:</strong>
                                    <?php echo date('M d, Y', strtotime($lease->start_date)); ?> -
                                    <?php echo date('M d, Y', strtotime($lease->end_date)); ?>
                                </span>
                                <span><strong>Rent:</strong> LKR <?php echo number_format($lease->monthly_rent, 2); ?>/mo</span>
                                <span><strong>Deposit:</strong> LKR <?php echo number_format($lease->deposit_amount, 2); ?></span>
                            </div>

                            <!-- Signature Status -->
                            <div class="signature-status">
                                <span class="signature-item <?php echo $lease->tenant_signed_at ? 'signed' : 'pending'; ?>">
                                    <i class="fas fa-<?php echo $lease->tenant_signed_at ? 'check-circle' : 'clock'; ?>"></i>
                                    Tenant: <?php echo $lease->tenant_signed_at ? 'Signed' : 'Pending'; ?>
                                </span>
                                <span class="signature-item <?php echo $lease->landlord_signed_at ? 'signed' : 'pending'; ?>">
                                    <i class="fas fa-<?php echo $lease->landlord_signed_at ? 'check-circle' : 'clock'; ?>"></i>
                                    Landlord: <?php echo $lease->landlord_signed_at ? 'Signed' : 'Pending'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="agreement-status">
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $lease->status)); ?>
                            </span>
                            <div class="agreement-actions">
                                <?php if ($needsSignature): ?>
                                    <a href="<?php echo URLROOT; ?>/leaseagreements/sign/<?php echo $lease->id; ?>"
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-pen"></i> Sign Agreement
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo URLROOT; ?>/leaseagreements/details/<?php echo $lease->id; ?>"
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($lease->status === 'active' || $lease->status === 'completed'): ?>
                                    <a href="<?php echo URLROOT; ?>/leaseagreements/download/<?php echo $lease->id; ?>"
                                       class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-contract"></i>
                <p>No lease agreements yet</p>
                <span>Your lease agreements will appear here once you have an approved booking.</span>
                <a href="<?php echo URLROOT; ?>/tenantproperties" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Agreement Terms Summary (for active lease) -->
    <?php if (isset($data['activeLease']) && $data['activeLease']): ?>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Agreement Terms Summary</h3>
        </div>

        <div class="terms-grid">
            <div class="term-card">
                <div class="term-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="term-content">
                    <h4>Property</h4>
                    <p><?php echo htmlspecialchars($data['activeLease']->property_address ?? 'N/A'); ?></p>
                </div>
            </div>

            <div class="term-card">
                <div class="term-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="term-content">
                    <h4>Lease Duration</h4>
                    <p>
                        <?php
                            $start = new DateTime($data['activeLease']->start_date);
                            $end = new DateTime($data['activeLease']->end_date);
                            $interval = $start->diff($end);
                            echo $interval->m + ($interval->y * 12) . ' Months';
                        ?>
                    </p>
                </div>
            </div>

            <div class="term-card">
                <div class="term-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="term-content">
                    <h4>Security Deposit</h4>
                    <p>LKR <?php echo number_format($data['activeLease']->deposit_amount, 2); ?></p>
                </div>
            </div>

            <?php if (!empty($data['activeLease']->terms)): ?>
                <?php
                    $terms = json_decode($data['activeLease']->terms, true);
                    if ($terms && is_array($terms)):
                ?>
                    <?php if (isset($terms['pets_allowed'])): ?>
                    <div class="term-card">
                        <div class="term-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div class="term-content">
                            <h4>Pet Policy</h4>
                            <p><?php echo $terms['pets_allowed'] ? 'Pets allowed' : 'No pets allowed'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($terms['smoking_allowed'])): ?>
                    <div class="term-card">
                        <div class="term-icon">
                            <i class="fas fa-smoking-ban"></i>
                        </div>
                        <div class="term-content">
                            <h4>Smoking Policy</h4>
                            <p><?php echo $terms['smoking_allowed'] ? 'Smoking allowed' : 'No smoking'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($terms['maintenance_responsibility'])): ?>
                    <div class="term-card">
                        <div class="term-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="term-content">
                            <h4>Maintenance</h4>
                            <p><?php echo htmlspecialchars($terms['maintenance_responsibility']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>

            <div class="term-card">
                <div class="term-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="term-content">
                    <h4>Payment Day</h4>
                    <p>Day <?php echo $data['activeLease']->payment_day ?? 1; ?> of each month</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.active-lease-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.lease-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.lease-property {
    display: flex;
    align-items: center;
    gap: 10px;
}

.lease-property i {
    font-size: 24px;
}

.lease-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.lease-detail {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.lease-detail .label {
    font-size: 14px;
    opacity: 0.9;
}

.lease-detail .value {
    font-size: 16px;
    font-weight: 600;
}

.lease-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-icon.active {
    background: #2ecc71;
}

.stat-icon.completed {
    background: #95a5a6;
}

.agreements-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.agreement-card {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 20px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
}

.agreement-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #667eea;
    font-size: 24px;
}

.agreement-details h4 {
    margin-bottom: 8px;
}

.agreement-property {
    color: #666;
    margin-bottom: 12px;
}

.agreement-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.signature-status {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.signature-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}

.signature-item.signed {
    color: #2ecc71;
}

.signature-item.pending {
    color: #f39c12;
}

.agreement-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 15px;
}

.agreement-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.terms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.term-card {
    display: flex;
    gap: 15px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
}

.term-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #667eea;
    font-size: 20px;
    flex-shrink: 0;
}

.term-content h4 {
    margin-bottom: 5px;
    font-size: 14px;
    color: #666;
}

.term-content p {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
