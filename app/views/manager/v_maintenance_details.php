<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="maintenance-details-container">
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URLROOT; ?>/manager/maintenance" class="btn btn-secondary" style="margin-bottom: 1rem;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <h1 class="page-title">Maintenance Request Details</h1>
            <p class="page-subtitle">Manage maintenance request and upload quotations</p>
        </div>
    </div>

    <?php flash('maintenance_message'); ?>

    <?php if (isset($data['maintenance'])): ?>
        <?php $m = $data['maintenance']; ?>

    <!-- Request Information -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Request Information</h2>
            <span class="status-badge status-<?php echo $m->status; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $m->status)); ?>
            </span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Request ID:</label>
                    <span>#<?php echo $m->id; ?></span>
                </div>
                <div class="info-item">
                    <label>Property:</label>
                    <span><?php echo htmlspecialchars($m->property_address); ?></span>
                </div>
                <div class="info-item">
                    <label>Landlord:</label>
                    <span><?php echo htmlspecialchars($m->landlord_name); ?></span>
                </div>
                <div class="info-item">
                    <label>Category:</label>
                    <span><?php echo ucfirst($m->category); ?></span>
                </div>
                <div class="info-item">
                    <label>Priority:</label>
                    <span class="priority-<?php echo $m->priority; ?>">
                        <?php echo ucfirst($m->priority); ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Created:</label>
                    <span><?php echo date('M d, Y', strtotime($m->created_at)); ?></span>
                </div>
            </div>
            <div class="info-section">
                <label>Title:</label>
                <p><?php echo htmlspecialchars($m->title); ?></p>
            </div>
            <div class="info-section">
                <label>Description:</label>
                <p><?php echo nl2br(htmlspecialchars($m->description)); ?></p>
            </div>
            <?php if ($m->notes): ?>
                <div class="info-section">
                    <label>Notes:</label>
                    <p><?php echo nl2br(htmlspecialchars($m->notes)); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Service Provider Assignment -->
    <!-- DEBUG: provider_id=<?php echo $m->provider_id ?? 'NULL'; ?>, status=<?php echo $m->status; ?>, providers count=<?php echo count($data['providers'] ?? []); ?> -->
    <?php if (!$m->provider_id && $m->status === 'pending'): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Assign Service Provider</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo URLROOT; ?>/maintenance/assignProvider/<?php echo $m->id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="provider_id">Service Provider <span class="required">*</span></label>
                        <select name="provider_id" id="provider_id" class="form-control" required>
                            <option value="">Select Provider</option>
                            <?php foreach ($data['providers'] as $provider): ?>
                                <option value="<?php echo $provider->id; ?>">
                                    <?php echo htmlspecialchars($provider->name); ?> - <?php echo $provider->specialty; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scheduled_date">Scheduled Date</label>
                        <input type="date" name="scheduled_date" id="scheduled_date" class="form-control"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Assign Provider
                </button>
            </form>
        </div>
    </div>
    <?php elseif ($m->provider_id): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Assigned Provider</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Provider:</label>
                    <span><?php echo htmlspecialchars($m->provider_name); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($m->provider_phone ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($m->provider_email ?? 'N/A'); ?></span>
                </div>
                <?php if ($m->scheduled_date): ?>
                <div class="info-item">
                    <label>Scheduled Date:</label>
                    <span><?php echo date('M d, Y', strtotime($m->scheduled_date)); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upload Quotation -->
    <?php
    // Show upload form if: provider assigned AND (no quotations OR latest quotation rejected) AND no payment
    $canUploadQuotation = $m->provider_id &&
                          (empty($data['quotations']) ||
                           (isset($data['quotations'][0]) && $data['quotations'][0]->status === 'rejected')) &&
                          (empty($data['payment']) || !is_object($data['payment']));
    ?>
    <!-- DEBUG: provider_id=<?php echo $m->provider_id ?? 'NULL'; ?>, quotations count=<?php echo count($data['quotations'] ?? []); ?>, payment=<?php echo isset($data['payment']) && is_object($data['payment']) ? 'EXISTS' : 'NONE'; ?>, canUpload=<?php echo $canUploadQuotation ? 'YES' : 'NO'; ?> -->
    <?php if ($canUploadQuotation): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Upload Quotation</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo URLROOT; ?>/maintenance/uploadQuotation/<?php echo $m->id; ?>" enctype="multipart/form-data">
                <input type="hidden" name="provider_id" value="<?php echo $m->provider_id; ?>">

                <div class="form-group">
                    <label for="amount">Quotation Amount (LKR) <span class="required">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control"
                           step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="description">Quotation Description <span class="required">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="4" required
                              placeholder="Describe the work to be done, materials needed, timeline, etc."></textarea>
                </div>

                <div class="form-group">
                    <label for="quotation_file">Attach Quotation Document (PDF, DOC, DOCX)</label>
                    <input type="file" name="quotation_file" id="quotation_file" class="form-control"
                           accept=".pdf,.doc,.docx">
                    <small class="form-text">Optional: Upload a detailed quotation document</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Quotation
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quotations List -->
    <?php if (!empty($data['quotations'])): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Quotations</h2>
        </div>
        <div class="card-body">
            <?php foreach ($data['quotations'] as $quotation): ?>
                <div class="quotation-card status-<?php echo $quotation->status; ?>">
                    <div class="quotation-header">
                        <div>
                            <h4>LKR <?php echo number_format($quotation->amount, 2); ?></h4>
                            <p><?php echo htmlspecialchars($quotation->provider_name); ?></p>
                        </div>
                        <span class="status-badge status-<?php echo $quotation->status; ?>">
                            <?php echo ucfirst($quotation->status); ?>
                        </span>
                    </div>
                    <div class="quotation-body">
                        <p><?php echo nl2br(htmlspecialchars($quotation->description)); ?></p>
                        <?php if ($quotation->quotation_file): ?>
                            <a href="<?php echo URLROOT; ?>/public/uploads/quotations/<?php echo $quotation->quotation_file; ?>"
                               target="_blank" class="btn btn-sm btn-secondary">
                                <i class="fas fa-file-pdf"></i> View Document
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="quotation-footer">
                        <small>Uploaded on <?php echo date('M d, Y', strtotime($quotation->created_at)); ?></small>
                        <?php if ($quotation->status === 'approved'): ?>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Approved on <?php echo date('M d, Y', strtotime($quotation->approved_at)); ?>
                            </small>
                        <?php elseif ($quotation->status === 'rejected'): ?>
                            <small class="text-danger">
                                <i class="fas fa-times-circle"></i> Rejected: <?php echo htmlspecialchars($quotation->rejection_reason); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Status -->
    <?php if (!empty($data['payment']) && is_object($data['payment'])): ?>
        <?php $payment = $data['payment']; ?>
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Payment Received</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Payment of LKR <?php echo number_format($payment->amount, 2); ?>
                    received on <?php echo date('M d, Y', strtotime($payment->payment_date)); ?>
                </div>
                <p>You can now coordinate with the service provider to begin the work.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Status Update -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Update Status</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo URLROOT; ?>/maintenance/updateStatus/<?php echo $m->id; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="pending" <?php echo $m->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="scheduled" <?php echo $m->status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="in_progress" <?php echo $m->status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $m->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $m->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Update Status
                </button>
            </form>
        </div>
    </div>

    <?php else: ?>
        <div class="alert alert-danger">Maintenance request not found.</div>
    <?php endif; ?>
</div>

<style>
.maintenance-details-container {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid #e5e7eb;
}

.content-card:last-child {
    margin-bottom: 0;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.card-body {
    color: #374151;
}


.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 14px;
}

.info-section {
    margin-top: 20px;
}

.info-section label {
    font-weight: 600;
    color: #666;
    font-size: 14px;
    display: block;
    margin-bottom: 8px;
}

.priority-low { color: #10b981; }
.priority-medium { color: #f59e0b; }
.priority-high { color: #ef4444; }
.priority-emergency { color: #dc2626; font-weight: 700; }

.quotation-card {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
}

.quotation-card.status-approved {
    border-color: #10b981;
    background: #f0fdf4;
}

.quotation-card.status-pending {
    border-color: #f59e0b;
    background: #fffbeb;
}

.quotation-card.status-rejected {
    border-color: #ef4444;
    background: #fef2f2;
}

.quotation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.quotation-header h4 {
    margin: 0;
    color: #45a9ea;
    font-size: 24px;
}

.quotation-header p {
    margin: 5px 0 0 0;
    color: #666;
}

.quotation-body {
    margin-bottom: 15px;
}

.quotation-footer {
    display: flex;
    justify-content: space-between;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.required {
    color: #ef4444;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-scheduled {
    background: #dbeafe;
    color: #1e40af;
}

.status-in_progress {
    background: #cffafe;
    color: #155e75;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
