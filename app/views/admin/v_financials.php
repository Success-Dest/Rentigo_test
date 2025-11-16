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
                <p class="stat-label">Platform Revenue</p>
                <span class="stat-change">10% from rental + full maintenance payments</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['collected'] ?? 0, 0); ?></h3>
                <p class="stat-label">Collected Fees</p>
                <span class="stat-change positive">From completed payments</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['pending'] ?? 0, 0); ?></h3>
                <p class="stat-label">Pending Fees</p>
                <span class="stat-change"><?php echo $data['pendingCount'] ?? 0; ?> transactions</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['overdue'] ?? 0, 0); ?></h3>
                <p class="stat-label">Overdue Fees</p>
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
                        <th>Total Payment</th>
                        <th>Platform Fee (10%)</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['recentTransactions'])): ?>
                        <?php foreach ($data['recentTransactions'] as $transaction): ?>
                            <?php
                                $isMaintenance = isset($transaction->payment_type) && $transaction->payment_type === 'maintenance';
                                $displayDate = $transaction->payment_date ?? $transaction->due_date ?? $transaction->created_at;
                            ?>
                            <tr data-type="income" data-status="<?php echo htmlspecialchars($transaction->status); ?>" data-date="<?php echo date('Y-m-d', strtotime($displayDate)); ?>">
                                <td>
                                    <div class="transaction-type">
                                        <div class="type-icon income">
                                            <i class="fas <?php echo $isMaintenance ? 'fa-tools' : 'fa-home'; ?>"></i>
                                        </div>
                                        <span class="type-label"><?php echo $isMaintenance ? 'Maintenance' : 'Rental'; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="transaction-description">
                                        <div class="description-title">
                                            <?php
                                                if ($isMaintenance) {
                                                    echo htmlspecialchars($transaction->maintenance_title ?? 'Maintenance payment');
                                                } else {
                                                    echo htmlspecialchars($transaction->payment_method ?? 'Rental payment');
                                                }
                                            ?>
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
                                    <div class="amount-display">
                                        <?php if ($isMaintenance): ?>
                                            LKR <?php echo number_format($transaction->amount, 0); ?>
                                        <?php else: ?>
                                            LKR <?php echo number_format($transaction->amount * 1.10, 0); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-display income">
                                        <?php if ($isMaintenance): ?>
                                            <span>-</span>
                                        <?php else: ?>
                                            <strong>LKR <?php echo number_format($transaction->amount * 0.10, 0); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('m/d/Y', strtotime($displayDate)); ?></td>
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
                                        <button class="action-btn view-btn" onclick="viewTransaction('<?php echo $isMaintenance ? 'MAIN' : 'TXN'; ?><?php echo $transaction->id; ?>', event)" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No transactions found</td>
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
    function viewTransaction(transactionId, event) {
        const modal = document.getElementById('transactionModal')
        const modalContent = document.getElementById('transactionModalContent')

        // Get the row element from the clicked button
        const row = event.target.closest('tr')

        // Get all td elements
        const cells = row.querySelectorAll('td')

        // Extract data with safe fallbacks
        const type = cells[0].querySelector('.type-label')?.textContent || 'N/A'
        const description = cells[1].querySelector('.description-title')?.textContent || 'N/A'
        const property = cells[2].querySelector('.property-name')?.textContent || 'N/A'
        const totalAmount = cells[3]?.textContent?.trim() || 'N/A'
        const platformFee = cells[4]?.textContent?.trim() || 'N/A'
        const date = cells[5]?.textContent?.trim() || 'N/A'
        const statusElement = cells[6].querySelector('.status-badge')
        const status = statusElement?.textContent?.trim() || 'N/A'
        const statusClass = statusElement?.className.split(' ')[1] || 'pending'

        // Build modal content with real data
        modalContent.innerHTML = `
            <div class="transaction-details">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Transaction ID</label>
                        <span>${transactionId}</span>
                    </div>
                    <div class="detail-item">
                        <label>Type</label>
                        <span>${type}</span>
                    </div>
                    <div class="detail-item">
                        <label>Description</label>
                        <span>${description}</span>
                    </div>
                    <div class="detail-item">
                        <label>Property</label>
                        <span>${property}</span>
                    </div>
                    <div class="detail-item">
                        <label>Total Payment</label>
                        <span class="amount-large">${totalAmount}</span>
                    </div>
                    <div class="detail-item">
                        <label>Platform Fee (10%)</label>
                        <span>${platformFee}</span>
                    </div>
                    <div class="detail-item">
                        <label>Date</label>
                        <span>${date}</span>
                    </div>
                    <div class="detail-item">
                        <label>Status</label>
                        <span class="status-badge ${statusClass}">${status}</span>
                    </div>
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
                <button class="action-btn view-btn" onclick="viewTransaction('${transactionId}', event)" title="View">
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