<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Pay Rent</h2>
            <p>Make your rent payments securely</p>
        </div>
    </div>

    <?php flash('payment_message'); ?>

    <!-- Payment Statistics -->
    <?php if (isset($data['totalPayments'])): ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h4>LKR <?php echo number_format(($data['totalPayments']->total_paid ?? 0) * 1.10, 2); ?></h4>
                <p>Total Paid</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo $data['totalPayments']->total_payments ?? 0; ?></h4>
                <p>Payments Made</p>
            </div>
        </div>
        <div class="stat-card <?php echo !empty($data['pendingPayments']) ? 'warning' : ''; ?>">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo count($data['pendingPayments'] ?? []); ?></h4>
                <p>Pending Payments</p>
            </div>
        </div>
        <div class="stat-card <?php echo !empty($data['overduePayments']) ? 'danger' : ''; ?>">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-details">
                <h4><?php echo count($data['overduePayments'] ?? []); ?></h4>
                <p>Overdue</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Current Rent Due -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Pending Payments</h3>
            <?php if (!empty($data['pendingPayments'])): ?>
                <span class="badge"><?php echo count($data['pendingPayments']); ?> Pending</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($data['pendingPayments'])): ?>
            <?php foreach ($data['pendingPayments'] as $payment): ?>
                <?php
                    // Check if overdue
                    $isOverdue = strtotime($payment->due_date) < time() && $payment->status !== 'completed';
                    $statusClass = $isOverdue ? 'rejected' : 'pending';
                    $statusText = $isOverdue ? 'Overdue' : 'Payment Due';
                ?>
                <div class="rent-payment-card <?php echo $isOverdue ? 'overdue' : ''; ?>">
                    <div class="rent-details">
                        <h4><?php echo htmlspecialchars($payment->property_address ?? 'Property'); ?></h4>
                        <div class="rent-amount">LKR <?php echo number_format($payment->amount * 1.10, 2); ?></div>
                        <div class="due-date">
                            <i class="fas fa-calendar"></i>
                            Due: <?php echo date('F d, Y', strtotime($payment->due_date)); ?>
                        </div>
                        <?php if ($isOverdue): ?>
                            <div class="overdue-notice">
                                <i class="fas fa-exclamation-triangle"></i>
                                Overdue by <?php echo floor((time() - strtotime($payment->due_date)) / 86400); ?> days
                            </div>
                        <?php endif; ?>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        <button class="btn btn-primary" onclick="openPaymentModal(<?php echo $payment->id; ?>, <?php echo $payment->amount * 1.10; ?>, '<?php echo htmlspecialchars($payment->property_address ?? 'Property'); ?>')">
                            <i class="fas fa-credit-card"></i>
                            Pay Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>No pending payments</p>
                <span>All caught up! You have no outstanding rent payments.</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment History -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Payment History</h3>
        </div>

        <?php if (!empty($data['paymentHistory'])): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['paymentHistory'] as $payment): ?>
                            <?php
                                $statusClass = '';
                                switch($payment->status) {
                                    case 'completed':
                                        $statusClass = 'approved';
                                        break;
                                    case 'pending':
                                        $statusClass = 'pending';
                                        break;
                                    case 'failed':
                                        $statusClass = 'rejected';
                                        break;
                                    case 'refunded':
                                        $statusClass = 'info';
                                        break;
                                }
                            ?>
                            <tr>
                                <td>
                                    <?php
                                        echo $payment->payment_date
                                            ? date('M d, Y', strtotime($payment->payment_date))
                                            : date('M d, Y', strtotime($payment->due_date));
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($payment->property_address ?? 'N/A'); ?></td>
                                <td>LKR <?php echo number_format($payment->amount * 1.10, 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($payment->payment_method ?? 'N/A')); ?></td>
                                <td>
                                    <?php if ($payment->transaction_id): ?>
                                        <code><?php echo htmlspecialchars($payment->transaction_id); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($payment->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment->status === 'completed'): ?>
                                        <a href="<?php echo URLROOT; ?>/payments/receipt/<?php echo $payment->id; ?>"
                                           class="btn btn-secondary btn-sm" target="_blank">
                                            <i class="fas fa-download"></i> Receipt
                                        </a>
                                    <?php elseif ($payment->status === 'pending'): ?>
                                        <button onclick="openPaymentModal(<?php echo $payment->id; ?>, <?php echo $payment->amount * 1.10; ?>, '<?php echo htmlspecialchars($payment->property_address ?? 'Property'); ?>')"
                                                class="btn btn-primary btn-sm">
                                            <i class="fas fa-credit-card"></i> Pay
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <p>No payment history</p>
                <span>Your payment history will appear here once you make your first payment.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Make Payment</h3>
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="paymentForm" action="<?php echo URLROOT; ?>/payments/process" method="POST">
                <input type="hidden" id="payment_id" name="payment_id">

                <div class="payment-summary">
                    <h4 id="modal_property_name">Property Name</h4>
                    <div class="amount-display">
                        <span>Amount to Pay:</span>
                        <strong id="modal_amount">LKR 0.00</strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div id="cardFields" class="card-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" class="form-input" maxlength="19">
                        </div>
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" name="expiry_date" placeholder="MM/YY" class="form-input" maxlength="5">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" name="cvv" placeholder="123" class="form-input" maxlength="4">
                        </div>
                        <div class="form-group">
                            <label>Cardholder Name</label>
                            <input type="text" name="cardholder_name" placeholder="John Doe" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Notes (Optional)</label>
                    <textarea name="notes" placeholder="Add any notes about your payment..." class="form-textarea" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPaymentModal(paymentId, amount, propertyName) {
    document.getElementById('payment_id').value = paymentId;
    document.getElementById('modal_amount').textContent = 'LKR ' + parseFloat(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('modal_property_name').textContent = propertyName;
    document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('paymentForm').reset();
}

// Show/hide card fields based on payment method
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const cardFields = document.getElementById('cardFields');

    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'credit_card' || this.value === 'debit_card') {
                cardFields.style.display = 'block';
                cardFields.querySelectorAll('input').forEach(input => input.required = true);
            } else {
                cardFields.style.display = 'none';
                cardFields.querySelectorAll('input').forEach(input => input.required = false);
            }
        });
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target == modal) {
        closePaymentModal();
    }
}
</script>

<style>
.rent-payment-card.overdue {
    border-left: 4px solid #e74c3c;
    background: #fee;
}

.overdue-notice {
    color: #e74c3c;
    font-weight: 600;
    margin: 10px 0;
}

.overdue-notice i {
    margin-right: 5px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card.danger {
    border-left: 4px solid #e74c3c;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.payment-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.amount-display {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    font-size: 18px;
}

.amount-display strong {
    color: #2ecc71;
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}

.text-muted {
    color: #999;
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
