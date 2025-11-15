<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <a href="<?php echo URLROOT; ?>/maintenance/index" class="btn btn-secondary" style="margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <h1 class="page-title">Maintenance Request Details</h1>
        <p class="page-subtitle">Review maintenance request, approve quotations, and make payments</p>
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
                <?php if ($m->scheduled_date): ?>
                <div class="info-item">
                    <label>Scheduled Date:</label>
                    <span><?php echo date('M d, Y', strtotime($m->scheduled_date)); ?></span>
                </div>
                <?php endif; ?>
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

    <!-- Assigned Service Provider -->
    <?php if ($m->provider_id): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Assigned Service Provider</h2>
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
            </div>
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

                    <!-- Action buttons for pending quotations -->
                    <?php if ($quotation->status === 'pending'): ?>
                        <div class="quotation-actions">
                            <button type="button" class="btn btn-success" onclick="approveQuotation(<?php echo $quotation->id; ?>)">
                                <i class="fas fa-check"></i> Approve Quotation
                            </button>
                            <button type="button" class="btn btn-danger" onclick="showRejectModal(<?php echo $quotation->id; ?>)">
                                <i class="fas fa-times"></i> Reject Quotation
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Payment button for approved quotations -->
                    <?php if ($quotation->status === 'approved' && !isset($data['payment'])): ?>
                        <div class="quotation-actions">
                            <button type="button" class="btn btn-primary btn-lg" onclick="showPaymentModal(<?php echo $quotation->id; ?>, <?php echo $quotation->amount; ?>)">
                                <i class="fas fa-credit-card"></i> Make Payment (LKR <?php echo number_format($quotation->amount, 2); ?>)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif ($m->provider_id): ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Quotations</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Waiting for the property manager to upload a quotation...
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Quotations</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Service provider needs to be assigned before quotations can be uploaded.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Status -->
    <?php if (isset($data['payment'])): ?>
        <?php $payment = $data['payment']; ?>
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Payment Status</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Payment of LKR <?php echo number_format($payment->amount, 2); ?>
                    completed on <?php echo date('M d, Y', strtotime($payment->payment_date)); ?>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Payment Method:</label>
                        <span><?php echo ucfirst($payment->payment_method); ?></span>
                    </div>
                    <?php if ($payment->transaction_id): ?>
                    <div class="info-item">
                        <label>Transaction ID:</label>
                        <span><?php echo htmlspecialchars($payment->transaction_id); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($payment->notes): ?>
                    <div class="info-item">
                        <label>Notes:</label>
                        <span><?php echo htmlspecialchars($payment->notes); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <p style="margin-top: 15px;">The property manager has been notified and will coordinate with the service provider to begin the work.</p>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-danger">Maintenance request not found.</div>
<?php endif; ?>

<!-- Reject Quotation Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reject Quotation</h3>
            <span class="close" onclick="closeRejectModal()">&times;</span>
        </div>
        <form id="rejectForm" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="rejection_reason">Reason for Rejection <span class="required">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" required
                              placeholder="Please explain why you are rejecting this quotation..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Reject Quotation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Make Payment</h3>
            <span class="close" onclick="closePaymentModal()">&times;</span>
        </div>
        <form id="paymentForm" method="POST">
            <div class="modal-body">
                <div class="payment-amount">
                    <label>Amount to Pay:</label>
                    <h2 id="paymentAmount">LKR 0.00</h2>
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method <span class="required">*</span></label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">Select Payment Method</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Credit/Debit Card</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_payment">Mobile Payment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transaction_id">Transaction ID / Reference Number</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                           placeholder="Optional: Enter transaction reference">
                </div>

                <div class="form-group">
                    <label for="notes">Payment Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"
                              placeholder="Optional: Add any notes about this payment"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Confirm Payment
                </button>
            </div>
        </form>
    </div>
</div>

<style>
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
    margin-bottom: 15px;
}

.quotation-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
}

.quotation-actions .btn-lg {
    padding: 12px 24px;
    font-size: 16px;
    flex: 1;
    min-width: 250px;
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

.text-success {
    color: #10b981;
}

.text-danger {
    color: #ef4444;
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
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.payment-amount {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}

.payment-amount label {
    display: block;
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.payment-amount h2 {
    margin: 0;
    color: #45a9ea;
    font-size: 32px;
}
</style>

<script>
// Approve quotation
function approveQuotation(quotationId) {
    if (confirm('Are you sure you want to approve this quotation?')) {
        window.location.href = '<?php echo URLROOT; ?>/maintenance/approveQuotation/' + quotationId;
    }
}

// Show reject modal
function showRejectModal(quotationId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '<?php echo URLROOT; ?>/maintenance/rejectQuotation/' + quotationId;
    modal.style.display = 'block';
}

// Close reject modal
function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'none';
    document.getElementById('rejection_reason').value = '';
}

// Show payment modal
function showPaymentModal(quotationId, amount) {
    const modal = document.getElementById('paymentModal');
    const form = document.getElementById('paymentForm');
    const amountDisplay = document.getElementById('paymentAmount');

    form.action = '<?php echo URLROOT; ?>/maintenance/payQuotation/' + quotationId;
    amountDisplay.textContent = 'LKR ' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    modal.style.display = 'block';
}

// Close payment modal
function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    document.getElementById('paymentForm').reset();
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const rejectModal = document.getElementById('rejectModal');
    const paymentModal = document.getElementById('paymentModal');

    if (event.target == rejectModal) {
        closeRejectModal();
    }
    if (event.target == paymentModal) {
        closePaymentModal();
    }
}
</script>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
