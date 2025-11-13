<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="maintenance-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Maintenance Tracking</h1>
        </div>
        <div class="header-right">
            <p class="page-subtitle">Track maintenance requests, quotations, and service completion status.</p>
            <!-- <button class="btn btn-primary" onclick="openNewRequestModal()">
                <i class="fas fa-plus"></i>
                New Request
            </button> -->
        </div>
    </div>

    <!-- Search Bar -->
    <div class="dashboard-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search maintenance requests..." id="maintenanceSearch">
        </div>
    </div>

    <!-- Maintenance Requests with Tabs -->
    <div class="dashboard-section">
        <div class="section-header">
            <div class="header-icon">
                <i class="fas fa-tools"></i>
                <h2>Maintenance Requests</h2>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="showMaintenanceTab('all')">All</button>
                <button class="tab-button" onclick="showMaintenanceTab('requested')">Requested</button>
                <button class="tab-button" onclick="showMaintenanceTab('quoted')">Quoted</button>
                <button class="tab-button" onclick="showMaintenanceTab('approved')">Approved</button>
                <button class="tab-button" onclick="showMaintenanceTab('completed')">Completed</button>
            </div>

            <div id="all-tab" class="tab-content active">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>REQUEST ID</th>
                                <th>PROPERTY</th>
                                <th>ISSUE</th>
                                <th>DATE</th>
                                <th>PROVIDER</th>
                                <th>QUOTATION</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['allRequests'])): ?>
                                <?php foreach ($data['allRequests'] as $request): ?>
                                    <tr>
                                        <td class="font-medium">MNT-<?php echo str_pad($request->id, 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($request->property_address ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($request->title ?? $request->description ?? 'N/A'); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($request->created_at)); ?></td>
                                        <td><?php echo htmlspecialchars($request->provider_name ?? 'Not assigned'); ?></td>
                                        <td class="font-semibold text-primary">
                                            LKR <?php echo number_format($request->estimated_cost ?? 0, 0); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php
                                                echo $request->status === 'completed' ? 'approved' :
                                                    ($request->status === 'approved' || $request->status === 'in_progress' ? 'approved' :
                                                    ($request->status === 'quoted' ? 'pending' : 'pending'));
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $request->status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($request->status === 'quoted'): ?>
                                                    <button class="btn btn-sm btn-success">Approve</button>
                                                <?php endif; ?>
                                                <button class="btn-icon" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No maintenance requests</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Quotation Approvals -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Pending Quotation Approvals</h3>
        </div>
        <div class="approval-cards">
            <?php if (!empty($data['pendingApprovals'])): ?>
                <?php foreach ($data['pendingApprovals'] as $request): ?>
                    <div class="approval-card">
                        <div class="card-content">
                            <div class="card-info">
                                <h4 class="font-medium">MNT-<?php echo str_pad($request->id, 3, '0', STR_PAD_LEFT); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($request->property_address ?? 'N/A'); ?></p>
                                <p><?php echo htmlspecialchars($request->title ?? $request->description ?? 'N/A'); ?></p>
                                <p class="quotation-amount">LKR <?php echo number_format($request->estimated_cost ?? 0, 0); ?></p>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-success">Approve Quote</button>
                                <button class="btn btn-secondary">Request Revision</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted" style="text-align: center; padding: 2rem;">No pending quotation approvals</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>