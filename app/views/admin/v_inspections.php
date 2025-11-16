<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <div class="page-header">
        <div class="header-content">
            <h2>All Inspections</h2>
            <p>View all inspections scheduled by property managers</p>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h3>Scheduled Inspections (<?php echo count($data['inspections'] ?? []); ?>)</h3>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Manager</th>
                        <th>Landlord</th>
                        <th>Tenant</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['inspections'])): ?>
                        <?php foreach ($data['inspections'] as $inspection): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inspection->property_address ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($inspection->manager_name ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($inspection->landlord_name ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($inspection->tenant_name ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo ucfirst(str_replace('_', ' ', $inspection->type)); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($inspection->scheduled_date)); ?></td>
                                <td><?php echo $inspection->scheduled_time ? date('g:i A', strtotime($inspection->scheduled_time)) : '-'; ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch($inspection->status) {
                                        case 'scheduled':
                                            $statusClass = 'badge-primary';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'badge-warning';
                                            break;
                                        case 'completed':
                                            $statusClass = 'badge-success';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'badge-danger';
                                            break;
                                        default:
                                            $statusClass = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($inspection->status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($inspection->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem;">
                                <p>No inspections found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>
