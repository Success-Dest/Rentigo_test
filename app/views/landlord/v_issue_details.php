<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="issue-details-content">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URLROOT; ?>/landlord/inquiries" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Inquiries
            </a>
            <h1 class="page-title">Inquiry Details</h1>
            <p class="page-subtitle">INQ-<?php echo $data['issue']->id; ?></p>
        </div>
        <div class="header-right">
            <span class="priority-badge-large <?php echo $data['issue']->priority; ?>">
                <?php echo strtoupper($data['issue']->priority); ?> PRIORITY
            </span>
        </div>
    </div>

    <?php flash('issue_message'); ?>
    <?php flash('issue_error'); ?>

    <div class="details-grid">
        <!-- Main Issue Information -->
        <div class="main-section">
            <div class="content-card">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($data['issue']->title); ?></h2>
                    <span class="status-badge-large <?php echo $data['issue']->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $data['issue']->status)); ?>
                    </span>
                </div>

                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label><i class="fas fa-building"></i> Property</label>
                            <p><?php echo htmlspecialchars($data['issue']->property_address); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fas fa-user"></i> Reported By</label>
                            <p><?php echo htmlspecialchars($data['issue']->tenant_name); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fas fa-tag"></i> Category</label>
                            <p><?php echo ucfirst($data['issue']->category); ?></p>
                        </div>
                        <div class="info-item">
                            <label><i class="fas fa-calendar"></i> Reported On</label>
                            <p><?php echo date('M d, Y - H:i', strtotime($data['issue']->created_at)); ?></p>
                        </div>
                    </div>

                    <div class="description-section">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <p class="description-text"><?php echo nl2br(htmlspecialchars($data['issue']->description)); ?></p>
                    </div>

                    <?php if ($data['issue']->resolution_notes): ?>
                        <div class="resolution-section">
                            <label><i class="fas fa-check-circle"></i> Resolution Notes</label>
                            <p class="resolution-text"><?php echo nl2br(htmlspecialchars($data['issue']->resolution_notes)); ?></p>
                            <?php if ($data['issue']->resolved_at): ?>
                                <small class="text-muted">Resolved on: <?php echo date('M d, Y - H:i', strtotime($data['issue']->resolved_at)); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions for Landlord -->
            <?php if ($data['issue']->status !== 'resolved' && !$data['issue']->maintenance_request_id): ?>
                <div class="action-section">
                    <h3>Actions</h3>
                    <p class="action-description">If this issue requires maintenance work, you can create a maintenance request.</p>
                    <div class="action-buttons">
                        <a href="<?php echo URLROOT; ?>/maintenance/create?property_id=<?php echo $data['issue']->property_id; ?>&issue_id=<?php echo $data['issue']->id; ?>" class="btn btn-primary">
                            <i class="fas fa-wrench"></i> Create Maintenance Request
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($data['issue']->maintenance_request_id): ?>
                <div class="info-box info-box-success">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Linked to Maintenance Request</strong>
                        <p>This issue has been linked to a maintenance request.</p>
                        <a href="<?php echo URLROOT; ?>/maintenance/view/<?php echo $data['issue']->maintenance_request_id; ?>" class="btn btn-sm btn-secondary" style="margin-top: 0.5rem;">
                            View Maintenance Request
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Information -->
        <div class="sidebar-section">
            <div class="info-card">
                <h3>Issue Status</h3>
                <div class="status-info">
                    <p><strong>Current Status:</strong></p>
                    <span class="status-badge-lg <?php echo $data['issue']->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $data['issue']->status)); ?>
                    </span>
                    <p style="margin-top: 1rem; font-size: 0.875rem; color: #6b7280;">
                        <?php if ($data['issue']->status === 'pending'): ?>
                            This issue is awaiting review by your property manager.
                        <?php elseif ($data['issue']->status === 'in_progress'): ?>
                            This issue is currently being worked on.
                        <?php elseif ($data['issue']->status === 'resolved'): ?>
                            This issue has been resolved.
                        <?php elseif ($data['issue']->status === 'cancelled'): ?>
                            This issue has been cancelled.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="info-card">
                <h3>Timeline</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <i class="fas fa-plus-circle"></i>
                        <div>
                            <strong>Created</strong>
                            <small><?php echo date('M d, Y - H:i', strtotime($data['issue']->created_at)); ?></small>
                        </div>
                    </div>
                    <?php if ($data['issue']->updated_at != $data['issue']->created_at): ?>
                        <div class="timeline-item">
                            <i class="fas fa-edit"></i>
                            <div>
                                <strong>Last Updated</strong>
                                <small><?php echo date('M d, Y - H:i', strtotime($data['issue']->updated_at)); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($data['issue']->resolved_at): ?>
                        <div class="timeline-item">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Resolved</strong>
                                <small><?php echo date('M d, Y - H:i', strtotime($data['issue']->resolved_at)); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-card">
                <h3>Tenant Contact</h3>
                <div class="contact-info">
                    <p><strong><?php echo htmlspecialchars($data['issue']->tenant_name); ?></strong></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($data['issue']->tenant_email); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.issue-details-content {
    padding: 0;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    text-decoration: none;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.back-link:hover {
    color: #45a9ea;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 0;
    font-size: 0.875rem;
}

.priority-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.priority-badge-large.emergency {
    background: #fee2e2;
    color: #991b1b;
}

.priority-badge-large.high {
    background: #fed7aa;
    color: #9a3412;
}

.priority-badge-large.medium {
    background: #fef3c7;
    color: #92400e;
}

.priority-badge-large.low {
    background: #dbeafe;
    color: #1e40af;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 24px;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.card-header h2 {
    font-size: 1.5rem;
    color: #1f2937;
    margin: 0;
    flex: 1;
}

.status-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-badge-large.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge-large.in_progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge-large.resolved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge-large.cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge-lg {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge-lg.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge-lg.in_progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge-lg.resolved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge-lg.cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-item label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.info-item p {
    margin: 0;
    color: #1f2937;
    font-size: 0.938rem;
}

.description-section,
.resolution-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.description-section label,
.resolution-section label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.938rem;
    color: #374151;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.description-text,
.resolution-text {
    color: #4b5563;
    line-height: 1.6;
    margin: 0;
}

.action-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 24px;
    border: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.action-section h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    color: #1f2937;
}

.action-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 20px;
    border: 1px solid #e5e7eb;
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.info-box-success {
    background: #f0fdf4;
    border-color: #86efac;
}

.info-box i {
    font-size: 1.5rem;
    color: #10b981;
}

.info-box strong {
    display: block;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.info-box p {
    margin: 0;
    font-size: 0.875rem;
    color: #4b5563;
}

.sidebar-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 20px;
    border: 1px solid #e5e7eb;
}

.info-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    color: #1f2937;
    font-weight: 600;
}

.status-info p {
    margin: 0.5rem 0;
    font-size: 0.875rem;
    color: #4b5563;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.timeline-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.timeline-item i {
    color: #45a9ea;
    margin-top: 0.25rem;
}

.timeline-item strong {
    display: block;
    font-size: 0.875rem;
    color: #1f2937;
}

.timeline-item small {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
}

.contact-info p {
    margin: 0.5rem 0;
    font-size: 0.875rem;
    color: #4b5563;
}

.contact-info i {
    color: #9ca3af;
    margin-right: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    width: 100%;
}

.btn-primary {
    background-color: #45a9ea;
    color: white;
}

.btn-primary:hover {
    background-color: #3b8dc4;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.813rem;
    width: auto;
}

.text-muted {
    color: #6b7280;
    font-size: 0.813rem;
}

@media (max-width: 1024px) {
    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
