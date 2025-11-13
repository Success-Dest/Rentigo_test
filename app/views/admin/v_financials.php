<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Financial Management</h2>
            <p>Monitor and manage all financial transactions</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['totalRevenue'] ?? 0, 0); ?></h3>
                <p class="stat-label">Total Revenue</p>
                <span class="stat-change">All transactions</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['collected'] ?? 0, 0); ?></h3>
                <p class="stat-label">Collected</p>
                <span class="stat-change positive">Completed payments</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['pending'] ?? 0, 0); ?></h3>
                <p class="stat-label">Pending</p>
                <span class="stat-change"><?php echo $data['pendingCount'] ?? 0; ?> transactions</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['overdue'] ?? 0, 0); ?></h3>
                <p class="stat-label">Overdue</p>
                <span class="stat-change negative"><?php echo $data['overdueCount'] ?? 0; ?> transactions</span>
            </div>
        </div>
    </div>



    <!-- Recent Transactions -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Recent Transactions</h3>
        </div>

        <div class="table-container">
            <table class="data-table transactions-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['recentTransactions'])): ?>
                        <?php foreach ($data['recentTransactions'] as $transaction): ?>
                            <tr data-type="income" data-status="<?php echo htmlspecialchars($transaction->status); ?>" data-date="<?php echo date('Y-m-d', strtotime($transaction->payment_date ?? $transaction->due_date)); ?>">
                                <td>
                                    <div class="transaction-type">
                                        <div class="type-icon income">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <span class="type-label">Payment</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="transaction-description">
                                        <div class="description-title">
                                            <?php echo htmlspecialchars($transaction->payment_method ?? 'Rental payment'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="property-info">
                                        <div class="property-name">
                                            <?php echo htmlspecialchars($transaction->property_address ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-display income">
                                        LKR <?php echo number_format($transaction->amount, 0); ?>
                                    </div>
                                </td>
                                <td><?php echo date('m/d/Y', strtotime($transaction->payment_date ?? $transaction->due_date)); ?></td>
                                <td>
                                    <span class="status-badge <?php
                                        echo $transaction->status === 'completed' ? 'approved' :
                                            ($transaction->status === 'pending' ? 'pending' : 'rejected');
                                    ?>">
                                        <?php echo ucfirst($transaction->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="transaction-actions">
                                        <button class="action-btn view-btn" onclick="viewTransaction('TXN<?php echo $transaction->id; ?>')" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


</div>

<!-- Transaction Details Modal -->
<div id="transactionModal" class="modal-overlay hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>Transaction Details</h3>
            <button class="modal-close" onclick="closeTransactionModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="transactionModalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    // Transaction management functions - Global scope for onclick handlers
    function viewTransaction(transactionId) {
        console.log('Viewing transaction:', transactionId)
        const modal = document.getElementById('transactionModal')
        const modalContent = document.getElementById('transactionModalContent')

        // Simulate transaction details
        modalContent.innerHTML = `
            <div class="transaction-details">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Transaction ID</label>
                        <span>${transactionId}</span>
                    </div>
                    <div class="detail-item">
                        <label>Type</label>
                        <span>Monthly Rent Payment</span>
                    </div>
                    <div class="detail-item">
                        <label>Amount</label>
                        <span class="amount-large">$2,500.00</span>
                    </div>
                    <div class="detail-item">
                        <label>Status</label>
                        <span class="status-badge approved">Approved</span>
                    </div>
                    <div class="detail-item">
                        <label>Property</label>
                        <span>Luxury Apartment Downtown</span>
                    </div>
                    <div class="detail-item">
                        <label>Tenant</label>
                        <span>John Doe</span>
                    </div>
                    <div class="detail-item">
                        <label>Date</label>
                        <span>January 7, 2024</span>
                    </div>
                    <div class="detail-item">
                        <label>Payment Method</label>
                        <span>Credit Card</span>
                    </div>
                </div>
                <div class="detail-notes">
                    <label>Notes</label>
                    <p>Regular monthly rent payment processed successfully.</p>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" onclick="closeTransactionModal()">Close</button>
                </div>
            </div>
        `

        modal.classList.remove('hidden')
    }

    function closeTransactionModal() {
        const modal = document.getElementById('transactionModal')
        if (modal) {
            modal.classList.add('hidden')
        }
    }

    function approveTransaction(transactionId) {
        if (confirm('Are you sure you want to approve this transaction?')) {
            const row = event.target.closest('tr')
            const statusCell = row.querySelector('.status-badge')
            const actionsCell = row.querySelector('.transaction-actions')

            // Update status
            statusCell.textContent = 'Approved'
            statusCell.className = 'status-badge approved'

            // Update actions - remove approve/reject, keep view only
            actionsCell.innerHTML = `
                <button class="action-btn view-btn" onclick="viewTransaction('${transactionId}')" title="View">
                    <i class="fas fa-eye"></i>
                </button>
            `

            showNotification('Transaction approved successfully!', 'success')
            updateFinancialStats()
        }
    }

    function rejectTransaction(transactionId) {
        if (confirm('Are you sure you want to reject this transaction?')) {
            const row = event.target.closest('tr')
            const statusCell = row.querySelector('.status-badge')

            // Update status
            statusCell.textContent = 'Rejected'
            statusCell.className = 'status-badge rejected'

            showNotification('Transaction rejected.', 'warning')
            updateFinancialStats()
        }
    }

    // Initialize basic functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Any initialization code can go here
        console.log('Financial management page loaded')
    })
</script>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>