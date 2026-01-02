<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Income Reports</h1>
        <p class="page-subtitle">Track your rental income and expenses</p>
    </div>
</div>

<?php flash('income_message'); ?>

<!-- Income Stats -->
<div class="stats-grid">
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Income</h3>
            <div class="stat-value">LKR <?php echo number_format($data['totalIncome'] ?? 0, 0); ?></div>
            <div class="stat-change">Fees from last 30 days</div>
        </div>
    </div>
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">This Month</h3>
            <div class="stat-value">
                LKR <?php
                    $currentMonth = date('Y-m');
                    $monthlyTotal = 0;
                    if (!empty($data['monthlyIncome'][$currentMonth])) {
                        $monthlyTotal = $data['monthlyIncome'][$currentMonth];
                    }
                    echo number_format($monthlyTotal, 0);
                ?>
            </div>
            <div class="stat-change"><?php echo date('F Y'); ?></div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-tools"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Maintenance Costs</h3>
            <div class="stat-value">
                LKR <?php
                    echo number_format(
                        ($data['maintenanceStats']->total_estimated_cost ?? 0) +
                        ($data['maintenanceStats']->total_actual_cost ?? 0),
                        0
                    );
                ?>
            </div>
            <div class="stat-change">Total expenses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Net Income</h3>
            <div class="stat-value">
                LKR <?php
                    $maintenanceCosts = ($data['maintenanceStats']->total_estimated_cost ?? 0) +
                                       ($data['maintenanceStats']->total_actual_cost ?? 0);
                    echo number_format(($data['totalIncome'] ?? 0) - $maintenanceCosts, 0);
                ?>
            </div>
            <div class="stat-change">After expenses (30 days)</div>
        </div>
    </div>
</div>

<!-- Monthly Income Chart -->
<?php if (!empty($data['monthlyIncome'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Monthly Income Breakdown</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Income</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Sort by month descending
                        $monthlyData = $data['monthlyIncome'];
                        krsort($monthlyData);
                        $maxIncome = !empty($monthlyData) ? max($monthlyData) : 1;

                        foreach ($monthlyData as $month => $amount):
                            $percentage = ($amount / $maxIncome) * 100;
                    ?>
                        <tr>
                            <td>
                                <?php
                                    $date = DateTime::createFromFormat('Y-m', $month);
                                    echo $date ? $date->format('F Y') : $month;
                                ?>
                            </td>
                            <td><strong>LKR <?php echo number_format($amount, 2); ?></strong></td>
                            <td>
                                <div class="progress-bar-container">
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payment Statistics -->
<?php if (!empty($data['paymentStats'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Payment Statistics by Month</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Completed</th>
                        <th>Amount</th>
                        <th>Pending</th>
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
                            <td>
                                <strong>LKR <?php echo number_format(
                                    ($stat->completed_amount ?? 0) + ($stat->pending_amount ?? 0),
                                    2
                                ); ?></strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Payments -->
<?php if (!empty($data['payments'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Recent Transactions</h2>
        <a href="<?php echo URLROOT; ?>/landlord/payment_history" class="btn btn-secondary">
            View All Payments
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tenant</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $recentPayments = array_slice($data['payments'], 0, 10);
                        foreach ($recentPayments as $payment):
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
                            <td><?php echo htmlspecialchars($payment->tenant_name ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($payment->property_address ?? 'N/A'); ?></td>
                            <td>LKR <?php echo number_format($payment->amount, 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($payment->status); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Maintenance Summary -->
<?php if (isset($data['maintenanceStats'])): ?>
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">Maintenance Expenses Summary</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-item">
                <h4>Total Requests</h4>
                <p class="stat-big"><?php echo $data['maintenanceStats']->total_requests ?? 0; ?></p>
            </div>
            <div class="stat-item">
                <h4>Completed</h4>
                <p class="stat-big"><?php echo $data['maintenanceStats']->completed_requests ?? 0; ?></p>
            </div>
            <div class="stat-item">
                <h4>Estimated Cost</h4>
                <p class="stat-big">LKR <?php echo number_format($data['maintenanceStats']->total_estimated_cost ?? 0, 2); ?></p>
            </div>
            <div class="stat-item">
                <h4>Actual Cost</h4>
                <p class="stat-big">LKR <?php echo number_format($data['maintenanceStats']->total_actual_cost ?? 0, 2); ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.progress-bar-container {
    width: 100%;
    height: 24px;
    background: #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.stat-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.stat-item h4 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
}

.stat-big {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #333;
}
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
