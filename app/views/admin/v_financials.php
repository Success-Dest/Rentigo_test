<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<?php
// ADD PAGINATION
require_once APPROOT . '/../app/helpers/AutoPaginate.php';
AutoPaginate::init($data, 10);
?>

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
                <span class="stat-change">Fees from last 30 days</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['collected'] ?? 0, 0); ?></h3>
                <p class="stat-label">Collected Fees</p>
                <span class="stat-change positive">Completed in last 30 days</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['pending'] ?? 0, 0); ?></h3>
                <p class="stat-label">Pending Fees</p>
                <span class="stat-change"><?php echo $data['pendingCount'] ?? 0; ?> pending (30 days)</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['overdue'] ?? 0, 0); ?></h3>
                <p class="stat-label">Overdue Fees</p>
                <span class="stat-change negative"><?php echo $data['overdueCount'] ?? 0; ?> overdue (30 days)</span>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="<?php echo URLROOT; ?>/admin/financials" class="filter-form">
            <div class="filter-grid">
                <!-- Transaction Type Filter -->
                <div class="filter-group">
                    <label for="filter_type">Type</label>
                    <select name="filter_type" id="filter_type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="rental" <?php echo ($data['filter_type'] ?? '') === 'rental' ? 'selected' : ''; ?>>Rental</option>
                        <option value="maintenance" <?php echo ($data['filter_type'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-group">
                    <label for="filter_status">Status</label>
                    <select name="filter_status" id="filter_status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="completed" <?php echo ($data['filter_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo ($data['filter_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="overdue" <?php echo ($data['filter_status'] ?? '') === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                        <option value="failed" <?php echo ($data['filter_status'] ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    </select>
                </div>

                <!-- Date From Filter -->
                <div class="filter-group">
                    <label for="filter_date_from">From Date</label>
                    <input type="date" name="filter_date_from" id="filter_date_from" class="filter-input" value="<?php echo htmlspecialchars($data['filter_date_from'] ?? ''); ?>">
                </div>

                <!-- Date To Filter -->
                <div class="filter-group">
                    <label for="filter_date_to">To Date</label>
                    <input type="date" name="filter_date_to" id="filter_date_to" class="filter-input" value="<?php echo htmlspecialchars($data['filter_date_to'] ?? ''); ?>">
                </div>

                <!-- Filter Buttons -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="<?php echo URLROOT; ?>/admin/financials" class="btn btn-secondary clear-btn">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>

            <!-- Active Filters Indicator -->
            <?php
            $activeFilters = [];
            if (!empty($data['filter_type'])) $activeFilters[] = 'Type: ' . ucfirst($data['filter_type']);
            if (!empty($data['filter_status'])) $activeFilters[] = 'Status: ' . ucfirst($data['filter_status']);
            if (!empty($data['filter_date_from'])) $activeFilters[] = 'From: ' . $data['filter_date_from'];
            if (!empty($data['filter_date_to'])) $activeFilters[] = 'To: ' . $data['filter_date_to'];
            
            if (!empty($activeFilters)):
            ?>
            <div class="active-filters">
                <span class="active-filters-label"><i class="fas fa-check-circle"></i> Active Filters:</span>
                <?php foreach ($activeFilters as $filter): ?>
                    <span class="filter-tag"><?php echo htmlspecialchars($filter); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </form>
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
                                        <div class="type-icon <?php echo $isMaintenance ? 'maintenance' : 'rental'; ?>">
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
                                                                echo $transaction->status === 'completed' ? 'approved' : ($transaction->status === 'pending' ? 'pending' : 'rejected');
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

<!-- ADD PAGINATION HERE - Render at bottom -->
<?php echo AutoPaginate::render($data['_pagination']); ?>

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

<style>
/* Filter Section Styles */
.filter-section {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filter-form {
    width: 100%;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
    font-size: 0.9rem;
}

.filter-select,
.filter-input {
    padding: 0.6rem;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
    background: #fff;
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: #45a9ea;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.filter-btn,
.clear-btn {
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
    text-decoration: none;
    white-space: nowrap;
}

.filter-btn {
    background: #45a9ea;
    color: #fff;
}

.filter-btn:hover {
    background: #3a8fc7;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(69, 169, 234, 0.3);
}

.clear-btn {
    background: #f5f5f5;
    color: #666;
    border: 2px solid #ddd;
}

.clear-btn:hover {
    background: #e0e0e0;
    color: #333;
}

.active-filters {
    margin-top: 1rem;
    padding: 0.8rem;
    background: #e3f2fd;
    border-left: 4px solid #45a9ea;
    border-radius: 4px;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.active-filters-label {
    font-weight: 600;
    color: #1976d2;
    margin-right: 0.5rem;
}

.filter-tag {
    background: #45a9ea;
    color: #fff;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Responsive Filters */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .filter-btn,
    .clear-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>