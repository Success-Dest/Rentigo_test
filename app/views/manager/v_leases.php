<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="leases-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Lease Agreement Verification</h1>
            <p class="page-subtitle">Review, validate, and manage rental agreements and lease documentation.</p>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="openAddAgreementModal()">
                <i class="fas fa-plus"></i>
                Add Agreement
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="dashboard-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search agreements..." id="agreementSearch">
        </div>
    </div>

    <div class="leases-layout">
        <!-- Lease Agreements Table -->
        <div class="agreements-list">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="header-icon">
                        <i class="fas fa-file-contract"></i>
                        <h2>Lease Agreements</h2>
                    </div>
                </div>

                <div class="tabs-container">
                    <div class="tabs-nav">
                        <button class="tab-button active" onclick="showLeaseTab('all')">All</button>
                        <button class="tab-button" onclick="showLeaseTab('pending-review')">Pending</button>
                        <button class="tab-button" onclick="showLeaseTab('validated')">Validated</button>
                        <button class="tab-button" onclick="showLeaseTab('rejected')">Rejected</button>
                    </div>

                    <div id="all-leases-tab" class="tab-content active">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>AGREEMENT ID</th>
                                        <th>TENANT</th>
                                        <th>PROPERTY</th>
                                        <th>LEASE PERIOD</th>
                                        <th>RENT</th>
                                        <th>STATUS</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['allLeases'])): ?>
                                        <?php foreach ($data['allLeases'] as $lease): ?>
                                            <tr class="agreement-row" onclick="selectAgreement('AGR-<?php echo $lease->id; ?>')">
                                                <td class="font-medium">AGR-<?php echo str_pad($lease->id, 3, '0', STR_PAD_LEFT); ?></td>
                                                <td><?php echo htmlspecialchars($lease->tenant_name ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($lease->property_address ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php
                                                        echo date('Y-m-d', strtotime($lease->lease_start_date));
                                                        echo ' to ';
                                                        echo date('Y-m-d', strtotime($lease->lease_end_date));
                                                    ?>
                                                </td>
                                                <td class="font-semibold text-primary">
                                                    LKR <?php echo number_format($lease->monthly_rent ?? 0, 0); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php
                                                        $status = $lease->validation_status ?? 'pending';
                                                        echo ($status === 'validated' || $status === 'approved') ? 'approved' :
                                                             ($status === 'rejected' ? 'rejected' : 'pending');
                                                    ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn-icon" title="View Agreement">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn-icon" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No lease agreements</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>