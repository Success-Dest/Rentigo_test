<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<?php
// ADD PAGINATION
require_once APPROOT . '/../app/helpers/AutoPaginate.php';
AutoPaginate::init($data, 5);
?>

<div class="maintenance-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Maintenance Requests</h1>
            <p class="page-subtitle">Manage maintenance requests, assign providers, and upload quotations</p>
        </div>
    </div>

    <?php flash('maintenance_message'); ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Pending</h3>
                <div class="stat-value"><?php echo $data['maintenanceStats']->pending ?? 0; ?></div>
                <div class="stat-change">Awaiting provider assignment</div>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Quotation Needed</h3>
                <div class="stat-value"><?php echo $data['maintenanceStats']->quotation_needed ?? 0; ?></div>
                <div class="stat-change">Ready to upload</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Completed</h3>
                <div class="stat-value"><?php echo $data['maintenanceStats']->completed ?? 0; ?></div>
                <div class="stat-change">Total completed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Total Income</h3>
                <div class="stat-value stat-value-small">LKR <?php echo number_format($data['maintenanceStats']->total_cost ?? 0, 2); ?></div>
                <div class="stat-change">Maintenance income</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <form method="GET" action="<?php echo URLROOT; ?>/maintenance/index">
        <div class="search-filter-content">
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="filter_status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($data['filter_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="scheduled" <?php echo ($data['filter_status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="in_progress" <?php echo ($data['filter_status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo ($data['filter_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo ($data['filter_status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" name="filter_priority">
                    <option value="">All Priorities</option>
                    <option value="low" <?php echo ($data['filter_priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($data['filter_priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($data['filter_priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="emergency" <?php echo ($data['filter_priority'] ?? '') === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                </select>
            </div>
            <div class="filter-dropdown-wrapper">
                <input type="date" name="filter_date_from" class="form-input" placeholder="From Date" value="<?php echo htmlspecialchars($data['filter_date_from'] ?? ''); ?>">
            </div>
            <div class="filter-dropdown-wrapper">
                <input type="date" name="filter_date_to" class="form-input" placeholder="To Date" value="<?php echo htmlspecialchars($data['filter_date_to'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="<?php echo URLROOT; ?>/maintenance/index" class="btn btn-outline">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>

    <!-- Maintenance Requests Table -->
    <div class="dashboard-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>PROPERTY</th>
                        <th>LANDLORD</th>
                        <th>ISSUE</th>
                        <th>CATEGORY</th>
                        <th>PRIORITY</th>
                        <th>PROVIDER</th>
                        <th>QUOTATION</th>
                        <th>PAYMENT</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['maintenanceRequests'])): ?>
                        <?php foreach ($data['maintenanceRequests'] as $request): ?>
                            <tr data-status="<?php echo $request->status; ?>">
                                <td class="font-medium">MNT-<?php echo str_pad($request->id, 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars(substr($request->property_address ?? 'N/A', 0, 30)); ?></td>
                                <td><?php echo htmlspecialchars($request->landlord_name ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($request->title, 0, 30)); ?></td>
                                <td><?php echo ucfirst($request->category); ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $request->priority; ?>">
                                        <?php echo strtoupper($request->priority); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($request->provider_name ?? 'Not assigned'); ?></td>
                                <td>
                                    <?php if (isset($request->quotation_status)): ?>
                                        <?php if ($request->quotation_status === 'pending'): ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php elseif ($request->quotation_status === 'approved'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Approved
                                            </span>
                                        <?php endif; ?>
                                        <br><small>LKR <?php echo number_format($request->quotation_amount ?? 0, 2); ?></small>
                                    <?php else: ?>
                                        <?php if ($request->provider_id): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-upload"></i> Upload needed
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($request->payment_status) && $request->payment_status === 'completed'): ?>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    <?php elseif (isset($request->quotation_status) && $request->quotation_status === 'approved'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Awaiting
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $request->status; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $request->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo URLROOT; ?>/maintenance/details/<?php echo $request->id; ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted" style="padding: 2rem;">
                                <i class="fas fa-tools" style="font-size: 48px; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                No maintenance requests found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD PAGINATION HERE - Render at bottom -->
<?php echo AutoPaginate::render($data['_pagination']); ?>

<style>
    .maintenance-content {
        padding: 0;
    }

    .page-header {
        margin-bottom: 2rem;
        padding: 0;
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
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding: 0;
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
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
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
        margin: 0 0 1.5rem 0;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .form-control {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
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

    .priority-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .priority-low {
        background: #d1fae5;
        color: #065f46;
    }

    .priority-medium {
        background: #fef3c7;
        color: #92400e;
    }

    .priority-high {
        background: #fee2e2;
        color: #991b1b;
    }

    .priority-emergency {
        background: #991b1b;
        color: white;
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

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
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

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.813rem;
        border: none;
        border-radius: 0.375rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #45a9ea;
        color: white;
    }

    .btn-primary:hover {
        background: #3b82f6;
    }
</style>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>