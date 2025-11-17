<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="dashboard-content">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo number_format($data['totalProperties'] ?? 0); ?></h3>
                <p class="stat-label">Total Properties</p>
                <span class="stat-change">All properties in system</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo number_format($data['activeTenants'] ?? 0); ?></h3>
                <p class="stat-label">Active Tenants</p>
                <span class="stat-change">Currently renting</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number">LKR <?php echo number_format($data['monthlyRevenue'] ?? 0, 0); ?></h3>
                <p class="stat-label">Platform Income</p>
                <span class="stat-change">10% from rental + full maintenance payments</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['pendingApprovals'] ?? 0; ?></h3>
                <p class="stat-label">Pending Approvals</p>
                <span class="stat-change">Requires attention</span>
            </div>
        </div>
    </div>

    <!-- Recent Properties Table -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>Recent Property Submissions</h2>
            <a href="<?php echo URLROOT; ?>/admin/properties" class="btn btn-primary">View All</a>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Property ID</th>
                        <th>Title</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#P001</td>
                        <td>Modern Apartment Downtown</td>
                        <td>John Smith</td>
                        <td>New York, NY</td>
                        <td>Rs 35,500/month</td>
                        <td><span class="status-badge pending">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-success">Approve</button>
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#P002</td>
                        <td>Cozy Studio Near Campus</td>
                        <td>Sarah Johnson</td>
                        <td>Boston, MA</td>
                        <td>Rs 20,500/month</td>
                        <td><span class="status-badge pending">Pending</span></td>
                        <td>
                            <button class="btn btn-sm btn-success">Approve</button>
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#P003</td>
                        <td>Luxury Penthouse</td>
                        <td>Michael Brown</td>
                        <td>Los Angeles, CA</td>
                        <td>Rs 45,500/month</td>
                        <td><span class="status-badge approved">Approved</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary">View</button>
                            <button class="btn btn-sm btn-secondary">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>