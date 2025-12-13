<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="tenants-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Tenant Management</h1>
            <p class="page-subtitle">Manage tenant information and lease agreements</p>
        </div>
    </div>

    <?php flash('tenant_message'); ?>

    <!-- Tenant Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Active</h3>
                <div class="stat-value"><?php echo $data['activeCount'] ?? 0; ?></div>
                <div class="stat-change">Currently active tenants</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Pending</h3>
                <div class="stat-value"><?php echo $data['pendingCount'] ?? 0; ?></div>
                <div class="stat-change">Awaiting approval</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-label">Vacated</h3>
                <div class="stat-value"><?php echo $data['vacatedCount'] ?? 0; ?></div>
                <div class="stat-change">Completed or cancelled</div>
            </div>
        </div>
    </div>

    <?php if (($data['assignedPropertiesCount'] ?? 0) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-home"></i>
            <h3>No Properties Assigned</h3>
            <p>You don't have any properties assigned to you yet. Please contact the administrator to assign properties to your account.</p>
        </div>
    <?php else: ?>
        <!-- Filter Section -->
        <div class="filter-container">
            <form method="GET" action="<?php echo URLROOT; ?>/manager/tenants" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all" <?php echo ($data['currentStatusFilter'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="active" <?php echo ($data['currentStatusFilter'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="pending" <?php echo ($data['currentStatusFilter'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="vacated" <?php echo ($data['currentStatusFilter'] ?? '') === 'vacated' ? 'selected' : ''; ?>>Vacated</option>
                            <option value="approved" <?php echo ($data['currentStatusFilter'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="completed" <?php echo ($data['currentStatusFilter'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($data['currentStatusFilter'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="property_id">Property</label>
                        <select name="property_id" id="property_id" class="form-control">
                            <option value="all" <?php echo ($data['currentPropertyFilter'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Properties</option>
                            <?php if (!empty($data['assignedProperties'])): ?>
                                <?php foreach ($data['assignedProperties'] as $property): ?>
                                    <option value="<?php echo $property->id; ?>" <?php echo ($data['currentPropertyFilter'] ?? '') == $property->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($property->address); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($data['currentDateFromFilter'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($data['currentDateToFilter'] ?? ''); ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="<?php echo URLROOT; ?>/manager/tenants" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tenants Table -->
        <div class="table-container-wrapper">
            <?php if (!empty($data['allBookings'])): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Property</th>
                                <th>Monthly Rent</th>
                                <th>Platform Fee</th>
                                <th>Lease Start</th>
                                <th>Lease End</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['allBookings'] as $booking): ?>
                                <tr>
                                    <td class="font-medium"><?php echo htmlspecialchars($booking->tenant_name ?? 'N/A'); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($booking->tenant_email ?? 'N/A'); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking->address ?? 'N/A'); ?></td>
                                    <td>LKR <?php echo number_format($booking->monthly_rent * 1.10 ?? 0, 0); ?></td>
                                    <td>
                                        <?php if ($booking->status === 'active' || $booking->status === 'approved'): ?>
                                            <strong class="text-success">LKR <?php echo number_format($booking->monthly_rent * 0.10 ?? 0, 0); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">LKR <?php echo number_format($booking->monthly_rent * 0.10 ?? 0, 0); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($booking->move_in_date)); ?></td>
                                    <td><?php echo $booking->move_out_date ? date('Y-m-d', strtotime($booking->move_out_date)) : 'N/A'; ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = ucfirst($booking->status);
                                        switch($booking->status) {
                                            case 'active':
                                            case 'approved':
                                                $statusClass = 'status-badge approved';
                                                break;
                                            case 'pending':
                                                $statusClass = 'status-badge pending';
                                                break;
                                            case 'completed':
                                            case 'cancelled':
                                                $statusClass = 'status-badge rejected';
                                                break;
                                            default:
                                                $statusClass = 'status-badge';
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No tenants found<?php echo (isset($data['currentStatusFilter']) && $data['currentStatusFilter'] !== 'all') || (isset($data['currentPropertyFilter']) && $data['currentPropertyFilter'] !== 'all') ? ' matching your filters' : ''; ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        background: #45a9ea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
        margin-bottom: 2px;
    }

    .stat-change {
        font-size: 12px;
        color: #999;
    }

    .filter-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 30px;
    }

    .filter-form {
        width: 100%;
    }

    .filter-row {
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
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .table-container-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table-container {
        overflow-x: auto;
        padding: 1.5rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: #f9fafb;
    }

    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .data-table td {
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .data-table tbody tr:hover {
        background: #f9fafb;
    }

    .font-medium {
        font-weight: 500;
    }

    .text-success {
        color: #10b981;
    }

    .text-muted {
        color: #6b7280;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 500;
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

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6b7280;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state h3 {
        margin-bottom: 0.5rem;
        color: #374151;
    }

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #45a9ea;
        color: white;
    }

    .btn-primary:hover {
        background: #3a8bc7;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #45a9ea;
        box-shadow: 0 0 0 3px rgba(69, 169, 234, 0.1);
    }

    @media (max-width: 768px) {
        .filter-row {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            width: 100%;
        }

        .filter-actions .btn {
            flex: 1;
        }
    }
</style>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>