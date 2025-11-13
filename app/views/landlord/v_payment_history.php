<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Payment History</h1>
        <p class="page-subtitle">Track rent payments and financial records</p>
    </div>
</div>

<?php flash('payment_message'); ?>

<!-- Payment Stats -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Income</h3>
            <div class="stat-value">LKR <?php echo number_format($data['totalIncome'] ?? 0, 0); ?></div>
            <div class="stat-change positive">All time earnings</div>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Completed Payments</h3>
            <div class="stat-value">
                <?php
                    $completedCount = 0;
                    $completedAmount = 0;
                    if (!empty($data['payments'])) {
                        foreach ($data['payments'] as $payment) {
                            if ($payment->status === 'completed') {
                                $completedCount++;
                                $completedAmount += $payment->amount;
                            }
                        }
                    }
                    echo $completedCount;
                ?>
            </div>
            <div class="stat-change">LKR <?php echo number_format($completedAmount, 0); ?></div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Pending Payments</h3>
            <div class="stat-value">
                <?php
                    $pendingCount = 0;
                    $pendingAmount = 0;
                    if (!empty($data['payments'])) {
                        foreach ($data['payments'] as $payment) {
                            if ($payment->status === 'pending') {
                                $pendingCount++;
                                $pendingAmount += $payment->amount;
                            }
                        }
                    }
                    echo $pendingCount;
                ?>
            </div>
            <div class="stat-change">LKR <?php echo number_format($pendingAmount, 0); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Payments</h3>
            <div class="stat-value"><?php echo count($data['payments'] ?? []); ?></div>
            <div class="stat-change">All transactions</div>
        </div>
    </div>
</div>

<!-- Payment Stats By Month -->
<?php if (!empty($data['paymentStats'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Monthly Payment Statistics</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Completed Payments</th>
                        <th>Completed Amount</th>
                        <th>Pending Payments</th>
                        <th>Pending Amount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['paymentStats'] as $stat): ?>
                        <tr>
                            <td>
                                <?php
                                    $monthName = date('F Y', mktime(0, 0, 0, $stat->month, 1, $stat->year));
                                    echo $monthName;
                                ?>
                            </td>
                            <td><?php echo $stat->completed_count ?? 0; ?></td>
                            <td>LKR <?php echo number_format($stat->completed_amount ?? 0, 2); ?></td>
                            <td><?php echo $stat->pending_count ?? 0; ?></td>
                            <td>LKR <?php echo number_format($stat->pending_amount ?? 0, 2); ?></td>
                            <td><strong>LKR <?php echo number_format(($stat->completed_amount ?? 0) + ($stat->pending_amount ?? 0), 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payment History Table -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">All Payments</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($data['payments'])): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['payments'] as $payment): ?>
                            <?php
                                $statusClass = '';
                                switch($payment->status) {
                                    case 'completed':
                                        $statusClass = 'success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'warning';
                                        break;
                                    case 'failed':
                                        $statusClass = 'danger';
                                        break;
                                    case 'refunded':
                                        $statusClass = 'info';
                                        break;
                                    default:
                                        $statusClass = 'secondary';
                                }
                            ?>
                            <tr class="payment-row">
                                <td>
                                    <?php
                                        if ($payment->payment_date) {
                                            echo date('M d, Y', strtotime($payment->payment_date));
                                        } else {
                                            echo 'Due: ' . date('M d, Y', strtotime($payment->due_date));
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($payment->tenant_name ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment->property_address ?? 'N/A'); ?></td>
                                <td>LKR <?php echo number_format($payment->amount, 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($payment->payment_method ?? 'N/A')); ?></td>
                                <td>
                                    <?php if ($payment->transaction_id): ?>
                                        <code><?php echo htmlspecialchars($payment->transaction_id); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($payment->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment->status === 'completed'): ?>
                                        <a href="<?php echo URLROOT; ?>/payments/receipt/<?php echo $payment->id; ?>"
                                           class="btn btn-outline btn-sm" target="_blank">
                                            <i class="fas fa-receipt"></i> Receipt
                                        </a>
                                    <?php elseif ($payment->status === 'pending'): ?>
                                        <button class="btn btn-secondary btn-sm" onclick="sendReminder(<?php echo $payment->id; ?>)">
                                            <i class="fas fa-bell"></i> Remind
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo URLROOT; ?>/payments/details/<?php echo $payment->id; ?>"
                                           class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p>No payment history yet</p>
                <span>Payment records will appear here once you have active leases and tenants start making payments.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Method Analytics -->
<?php if (!empty($data['payments'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Payment Analytics</h2>
    </div>
    <div class="card-body">
        <div class="analytics-grid">
            <div class="analytics-item">
                <h4>Payment Methods</h4>
                <div class="method-stats">
                    <?php
                        // Calculate payment method distribution
                        $methodCounts = [];
                        $totalPayments = count($data['payments']);
                        foreach ($data['payments'] as $payment) {
                            if ($payment->status === 'completed' && $payment->payment_method) {
                                $method = ucfirst($payment->payment_method);
                                if (!isset($methodCounts[$method])) {
                                    $methodCounts[$method] = 0;
                                }
                                $methodCounts[$method]++;
                            }
                        }

                        if (!empty($methodCounts)):
                            arsort($methodCounts);
                            foreach ($methodCounts as $method => $count):
                                $percentage = ($count / array_sum($methodCounts)) * 100;
                    ?>
                        <div class="method-item">
                            <span class="method-label"><?php echo htmlspecialchars($method); ?></span>
                            <span class="method-count"><?php echo $count; ?> payments</span>
                            <span class="method-percentage"><?php echo number_format($percentage, 1); ?>%</span>
                        </div>
                    <?php
                            endforeach;
                        else:
                    ?>
                        <p class="text-muted">No completed payments yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="analytics-item">
                <h4>Payment Status Distribution</h4>
                <div class="status-stats">
                    <?php
                        $statusCounts = [
                            'completed' => 0,
                            'pending' => 0,
                            'failed' => 0,
                            'refunded' => 0
                        ];

                        foreach ($data['payments'] as $payment) {
                            if (isset($statusCounts[$payment->status])) {
                                $statusCounts[$payment->status]++;
                            }
                        }

                        foreach ($statusCounts as $status => $count):
                            if ($count > 0):
                                $percentage = ($count / $totalPayments) * 100;
                                $statusClass = '';
                                switch($status) {
                                    case 'completed': $statusClass = 'success'; break;
                                    case 'pending': $statusClass = 'warning'; break;
                                    case 'failed': $statusClass = 'danger'; break;
                                    case 'refunded': $statusClass = 'info'; break;
                                }
                    ?>
                        <div class="status-item">
                            <span class="status-badge badge-<?php echo $statusClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                            <span class="status-count"><?php echo $count; ?> payments</span>
                            <span class="status-percentage"><?php echo number_format($percentage, 1); ?>%</span>
                        </div>
                    <?php
                            endif;
                        endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function sendReminder(paymentId) {
    if (confirm('Send payment reminder to tenant?')) {
        // In a real application, this would make an AJAX call
        alert('Payment reminder sent to tenant!');
    }
}
</script>

<style>
code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    font-family: monospace;
}

.text-muted {
    color: #999;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.analytics-item h4 {
    margin-bottom: 15px;
    font-size: 16px;
    color: #333;
}

.method-stats,
.status-stats {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.method-item,
.status-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.method-label,
.status-badge {
    font-weight: 600;
    flex: 1;
}

.method-count,
.status-count {
    font-size: 14px;
    color: #666;
    margin: 0 15px;
}

.method-percentage,
.status-percentage {
    font-weight: 600;
    color: #667eea;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    color: #ddd;
}

.empty-state p {
    font-size: 18px;
    margin-bottom: 8px;
    color: #666;
}

.empty-state span {
    font-size: 14px;
}
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
