<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Issue Details</h2>
            <p>Complete lifecycle and information for Issue #<?php echo $data['issue']->id; ?></p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/issues/track" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Issues
            </a>
        </div>
    </div>

    <?php flash('issue_message'); ?>

    <!-- Issue Details Card -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-info-circle"></i> Issue Details</h3>
        </div>
        <div class="details-grid">
            <div class="detail-item">
                <label>Issue ID</label>
                <div class="detail-value"><strong>#<?php echo $data['issue']->id; ?></strong></div>
            </div>
            <div class="detail-item">
                <label>Title</label>
                <div class="detail-value"><?php echo htmlspecialchars($data['issue']->title); ?></div>
            </div>
            <div class="detail-item full-width">
                <label>Description</label>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($data['issue']->description)); ?></div>
            </div>
            <div class="detail-item">
                <label>Category</label>
                <div class="detail-value">
                    <span class="badge badge-info"><?php echo ucfirst($data['issue']->category ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="detail-item">
                <label>Priority</label>
                <div class="detail-value">
                    <span class="priority-badge <?php echo $data['issue']->priority; ?>">
                        <?php echo ucfirst($data['issue']->priority); ?>
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <label>Property</label>
                <div class="detail-value"><?php echo htmlspecialchars($data['issue']->property_address ?? 'N/A'); ?></div>
            </div>
            <div class="detail-item">
                <label>Created Date</label>
                <div class="detail-value">
                    <i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($data['issue']->created_at)); ?>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($data['issue']->created_at)); ?>
                    </small>
                </div>
            </div>
            <div class="detail-item">
                <label>Current Status</label>
                <div class="detail-value">
                    <span class="status-badge <?php echo $data['issue']->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $data['issue']->status)); ?>
                    </span>
                </div>
            </div>
            <?php if (!empty($data['issue']->resolution_notes)): ?>
                <div class="detail-item full-width">
                    <label>Resolution Notes</label>
                    <div class="detail-value"><?php echo nl2br(htmlspecialchars($data['issue']->resolution_notes)); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quotation Details Card (if available) -->
    <?php if (!empty($data['maintenanceRequest']) && !empty($data['quotations'])): ?>
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-file-invoice-dollar"></i> Quotation Details</h3>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Quotation #</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['quotations'] as $quotation): ?>
                            <tr>
                                <td><strong>#<?php echo $quotation->id; ?></strong></td>
                                <td>LKR <?php echo number_format($quotation->amount, 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $quotation->status === 'approved' ? 'success' : 
                                            ($quotation->status === 'rejected' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($quotation->status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('F j, Y g:i A', strtotime($quotation->created_at)); ?></td>
                                <td><?php echo htmlspecialchars($quotation->description ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Service Provider Card (if assigned) -->
    <?php if (!empty($data['serviceProvider'])): ?>
        <div class="dashboard-section">
            <div class="section-header">
                <h3><i class="fas fa-tools"></i> Assigned Service Provider</h3>
            </div>
            <div class="details-grid">
                <div class="detail-item">
                    <label>Provider Name</label>
                    <div class="detail-value"><?php echo htmlspecialchars($data['serviceProvider']->name ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <label>Company</label>
                    <div class="detail-value"><?php echo htmlspecialchars($data['serviceProvider']->company ?? 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <label>Phone</label>
                    <div class="detail-value">
                        <a href="tel:<?php echo htmlspecialchars($data['serviceProvider']->phone ?? ''); ?>">
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($data['serviceProvider']->phone ?? 'N/A'); ?>
                        </a>
                    </div>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <div class="detail-value">
                        <a href="mailto:<?php echo htmlspecialchars($data['serviceProvider']->email ?? ''); ?>">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($data['serviceProvider']->email ?? 'N/A'); ?>
                        </a>
                    </div>
                </div>
                <?php if (!empty($data['serviceProvider']->specialty)): ?>
                    <div class="detail-item">
                        <label>Specialty</label>
                        <div class="detail-value"><?php echo htmlspecialchars($data['serviceProvider']->specialty); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Status History Timeline -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Status History / Updates</h3>
        </div>
        <div class="timeline-container">
            <?php if (!empty($data['statusHistory'])): ?>
                <div class="timeline">
                    <?php foreach ($data['statusHistory'] as $index => $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h4>
                                        <span class="status-badge <?php echo $history['status']; ?>">
                                            <?php echo $history['status_text']; ?>
                                        </span>
                                    </h4>
                                    <span class="timeline-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('F j, Y', strtotime($history['date_time'])); ?>
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($history['date_time'])); ?>
                                    </span>
                                </div>
                                <div class="timeline-body">
                                    <p class="timeline-updater">
                                        <strong>Updated by:</strong> 
                                        <?php echo htmlspecialchars($history['updated_by_name']); ?> 
                                        <span class="badge badge-secondary"><?php echo $history['updated_by']; ?></span>
                                    </p>
                                    <?php if (!empty($history['notes'])): ?>
                                        <p class="timeline-notes"><?php echo htmlspecialchars($history['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No status history available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .header-content h2 {
        margin: 0 0 0.5rem 0;
        color: #1f2937;
        font-size: 1.75rem;
    }

    .header-content p {
        margin: 0;
        color: #6b7280;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .dashboard-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .section-header {
        padding: 1.5rem;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .section-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-item label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .detail-value {
        font-size: 1rem;
        color: #1f2937;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
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

    .priority-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .priority-badge.emergency {
        background: #fee2e2;
        color: #991b1b;
    }

    .priority-badge.high {
        background: #fef3c7;
        color: #92400e;
    }

    .priority-badge.medium {
        background: #dbeafe;
        color: #1e40af;
    }

    .priority-badge.low {
        background: #e5e7eb;
        color: #374151;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.in_progress {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-badge.resolved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .text-muted {
        color: #6b7280;
    }

    .table-container {
        padding: 1.5rem;
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

    .timeline-container {
        padding: 1.5rem;
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }

    .timeline-marker {
        position: absolute;
        left: -1.75rem;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        background: #45a9ea;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    .timeline-marker i {
        font-size: 0.5rem;
        color: white;
    }

    .timeline-content {
        background: #f9fafb;
        border-radius: 8px;
        padding: 1rem;
        border-left: 3px solid #45a9ea;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .timeline-header h4 {
        margin: 0;
    }

    .timeline-date {
        font-size: 0.875rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .timeline-body {
        color: #374151;
    }

    .timeline-updater {
        margin: 0 0 0.5rem 0;
        font-size: 0.875rem;
    }

    .timeline-notes {
        margin: 0;
        padding: 0.75rem;
        background: white;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
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

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-actions {
            width: 100%;
            margin-top: 1rem;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }

        .timeline-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>