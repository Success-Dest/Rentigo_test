<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="issue-details-content">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URLROOT; ?>/manager/issues" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Issues
            </a>
            <h1 class="page-title">Issue Details</h1>
            <p class="page-subtitle">ISS-<?php echo $data['issue']->id; ?></p>
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

            <!-- Action Buttons -->
            <div class="action-section">
                <h3>Actions</h3>
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="openStatusModal()">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
                    <button class="btn btn-warning" onclick="openNotifyLandlordModal()">
                        <i class="fas fa-bell"></i> Notify Landlord
                    </button>
                    <?php if ($data['issue']->status !== 'resolved'): ?>
                        <button class="btn btn-success" onclick="openResolveModal()">
                            <i class="fas fa-check"></i> Mark as Resolved
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="sidebar-section">
            <div class="info-card">
                <h3>Landlord Information</h3>
                <div class="landlord-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($data['issue']->landlord_name ?? 'N/A'); ?></p>
                    <p><strong>Notified:</strong>
                        <?php if ($data['issue']->landlord_notified ?? false): ?>
                            <span class="badge badge-success">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-warning">No</span>
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
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Issue Status</h3>
            <span class="close" onclick="closeStatusModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusForm">
                <input type="hidden" name="issue_id" value="<?php echo $data['issue']->id; ?>">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="pending" <?php echo $data['issue']->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $data['issue']->status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $data['issue']->status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="cancelled" <?php echo $data['issue']->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="resolution_notes">Notes (Optional)</label>
                    <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="4"><?php echo htmlspecialchars($data['issue']->resolution_notes ?? ''); ?></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notify Landlord Modal -->
<div id="notifyLandlordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Notify Landlord</h3>
            <span class="close" onclick="closeNotifyLandlordModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="notifyLandlordForm">
                <input type="hidden" name="issue_id" value="<?php echo $data['issue']->id; ?>">
                <div class="form-group">
                    <label for="landlord_message">Message to Landlord</label>
                    <textarea name="message" id="landlord_message" class="form-control" rows="5" placeholder="Explain why this issue requires the landlord's attention..."></textarea>
                    <small class="text-muted">Leave blank to send default notification</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeNotifyLandlordModal()">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Issue Modal -->
<div id="resolveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Mark Issue as Resolved</h3>
            <span class="close" onclick="closeResolveModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="resolveForm">
                <input type="hidden" name="issue_id" value="<?php echo $data['issue']->id; ?>">
                <input type="hidden" name="status" value="resolved">
                <div class="form-group">
                    <label for="resolve_notes">Resolution Notes *</label>
                    <textarea name="resolution_notes" id="resolve_notes" class="form-control" rows="5" required placeholder="Describe how this issue was resolved..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeResolveModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Resolved</button>
                </div>
            </form>
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
}

.action-section h3 {
    margin: 0 0 1rem 0;
    font-size: 1.125rem;
    color: #1f2937;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
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

.landlord-info p {
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

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

.btn-warning {
    background-color: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background-color: #d97706;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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
    font-size: 1.25rem;
    color: #1f2937;
}

.close {
    color: #9ca3af;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover {
    color: #1f2937;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 0.625rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: #45a9ea;
    box-shadow: 0 0 0 3px rgba(69, 169, 234, 0.1);
}

.modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.modal-footer .btn {
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

<script>
// Modal functions
function openStatusModal() {
    document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function openNotifyLandlordModal() {
    document.getElementById('notifyLandlordModal').style.display = 'block';
}

function closeNotifyLandlordModal() {
    document.getElementById('notifyLandlordModal').style.display = 'none';
}

function openResolveModal() {
    document.getElementById('resolveModal').style.display = 'block';
}

function closeResolveModal() {
    document.getElementById('resolveModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}

// Update Status Form
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('<?php echo URLROOT; ?>/manager/updateIssueStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    });
});

// Notify Landlord Form
document.getElementById('notifyLandlordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('<?php echo URLROOT; ?>/manager/notifyLandlordAboutIssue', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeNotifyLandlordModal();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending notification');
    });
});

// Resolve Issue Form
document.getElementById('resolveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('<?php echo URLROOT; ?>/manager/updateIssueStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while resolving the issue');
    });
});
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
