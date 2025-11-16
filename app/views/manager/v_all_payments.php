<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="payments-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">All Payments</h1>
            <p class="page-subtitle">View all rental and maintenance payments</p>
        </div>
        <div class="header-right">
            <a href="<?php echo URLROOT; ?>/manager/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php flash('payment_message'); ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Total Income</h3>
                <div class="stat-value">LKR <?php echo number_format($data['totalIncome'] ?? 0, 2); ?></div>
                <div class="stat-change"><?php echo $data['completedCount'] ?? 0; ?> completed payments</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Pending</h3>
                <div class="stat-value">LKR <?php echo number_format($data['pendingAmount'] ?? 0, 2); ?></div>
                <div class="stat-change"><?php echo $data['pendingCount'] ?? 0; ?> pending payments</div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="dashboard-section">
        <div class="header-actions">
            <div class="filter-group">
                <label for="typeFilter">Payment Type:</label>
                <select class="form-control" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="rental">Rental</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select class="form-control" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="dashboard-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Total Payment</th>
                        <th>Platform Fee</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['allPayments'])): ?>
                        <?php foreach ($data['allPayments'] as $payment): ?>
                            <?php
                                $isMaintenance = isset($payment->payment_type) && $payment->payment_type === 'maintenance';
                                $customerName = $isMaintenance ? ($payment->landlord_name ?? 'N/A') : ($payment->tenant_name ?? 'N/A');
                                $totalPayment = $isMaintenance ? $payment->amount : ($payment->amount * 1.10);
                                $platformFee = $isMaintenance ? $payment->amount : ($payment->amount * 0.10);
                                $paymentType = $isMaintenance ? 'maintenance' : 'rental';
                            ?>
                            <tr data-type="<?php echo $paymentType; ?>" data-status="<?php echo $payment->status; ?>">
                                <td>
                                    <span class="badge <?php echo $isMaintenance ? 'badge-info' : 'badge-success'; ?>">
                                        <?php echo $isMaintenance ? 'Maintenance' : 'Rental'; ?>
                                    </span>
                                </td>
                                <td class="font-medium"><?php echo htmlspecialchars($customerName); ?></td>
                                <td><?php echo htmlspecialchars($payment->property_address ?? 'N/A'); ?></td>
                                <td>LKR <?php echo number_format($totalPayment, 2); ?></td>
                                <td><strong>LKR <?php echo number_format($platformFee, 2); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($payment->payment_date ?? $payment->due_date ?? $payment->created_at)); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $payment->status === 'completed' ? 'approved' : ($payment->status === 'pending' ? 'pending' : 'rejected'); ?>">
                                        <?php echo ucfirst($payment->status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                                <i class="fas fa-receipt" style="font-size: 48px; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                No payments found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.payments-content {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.header-left {
    flex: 1;
}

.header-right {
    display: flex;
    gap: 1rem;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 1rem;
    align-items: center;
    border: 1px solid #e5e7eb;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 0.5rem;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #45a9ea;
}

.stat-card.success .stat-icon {
    background: #d1fae5;
    color: #065f46;
}

.stat-card.warning .stat-icon {
    background: #fef3c7;
    color: #92400e;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 0.25rem 0;
}

.stat-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-change {
    font-size: 0.813rem;
    color: #6b7280;
}

.dashboard-section {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.header-actions {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
}

.form-control {
    min-width: 180px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background-color: white;
    cursor: pointer;
}

.form-control:focus {
    outline: none;
    border-color: #45a9ea;
    box-shadow: 0 0 0 3px rgba(69, 169, 234, 0.1);
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f9fafb;
    padding: 0.75rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    border-bottom: 1px solid #e5e7eb;
}

.data-table td {
    padding: 1rem 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.875rem;
}

.font-medium {
    font-weight: 500;
}

.text-muted {
    color: #6b7280;
}

.text-center {
    text-align: center;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.querySelector('.data-table tbody');

    if (!typeFilter || !statusFilter || !table) {
        console.error('Filter elements not found');
        return;
    }

    function filterTable() {
        const selectedType = typeFilter.value;
        const selectedStatus = statusFilter.value;
        const rows = table.querySelectorAll('tr[data-type]');

        let visibleCount = 0;

        rows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            const rowStatus = row.getAttribute('data-status');

            const typeMatch = !selectedType || rowType === selectedType;
            const statusMatch = !selectedStatus || rowStatus === selectedStatus;

            if (typeMatch && statusMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        console.log(`Filtered: ${visibleCount} of ${rows.length} rows visible`);
    }

    typeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
});
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>
