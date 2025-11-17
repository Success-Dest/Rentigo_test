<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <div class="page-header">
        <div class="header-content">
            <h2>Properties Management</h2>
            <p>Manage, approve, and oversee all property listings</p>
        </div>
    </div>
    <div class="search-filter-content">
        <div class="search-input-wrapper">
            <input type="text" class="form-input" placeholder="Search properties..." id="searchProperties">
        </div>
        <div class="filter-dropdown-wrapper">
            <select class="form-select" id="filterProperties" onchange="window.location.href='?filter=' + this.value;">
                <option value="" <?php echo (($data['current_filter'] ?? 'all') === 'all' || ($data['current_filter'] ?? '') === '') ? 'selected' : ''; ?>>All Properties</option>
                <option value="pending" <?php echo (($data['current_filter'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo (($data['current_filter'] ?? '') === 'approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo (($data['current_filter'] ?? '') === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
    </div>
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Property Listings (<?php echo count($data['properties'] ?? []); ?>)</h3>
        </div>
        <div class="table-container">
            <table class="data-table properties-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Manager</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['properties'])): ?>
                        <?php foreach ($data['properties'] as $property): ?>
                            <tr>
                                <td><?php echo nl2br(htmlspecialchars($property->address)); ?></td>
                                <td>
                                    <div class="owner-info">
                                        <div class="owner-name"><?php echo htmlspecialchars($property->landlord_name ?? ''); ?></div>
                                        <div class="owner-email"><?php echo htmlspecialchars($property->landlord_email ?? ''); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="manager-info">
                                        <div class="manager-name">
                                            <?php echo $property->manager_name ? htmlspecialchars($property->manager_name) : '<span class="text-muted">Unassigned</span>'; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo ucfirst(htmlspecialchars($property->property_type)); ?></td>
                                <td><?php echo isset($property->rent) && $property->rent > 0 ? 'Rs ' . number_format($property->rent) . '/month' : '<span class="text-muted">N/A</span>'; ?></td>
                                <td><span class="status-badge <?php echo strtolower($property->status); ?>"><?php echo ucfirst($property->status); ?></span></td>
                                <td>
                                    <?php
                                    $approval = strtolower($property->approval_status);
                                    $badgeClass = $approval === 'approved' ? 'approved' : ($approval === 'pending' ? 'pending' : 'rejected');
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($approval); ?></span>
                                </td>
                                <td>
                                    <div class="property-actions">
                                        <a class="action-btn view-btn" href="<?php echo URLROOT . '/adminproperties/propertyDetails/' . $property->id; ?>" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($property->approval_status === 'pending'): ?>
                                            <form action="<?php echo URLROOT . '/adminproperties/approve/' . $property->id; ?>" method="post" style="display:inline;">
                                                <button type="submit" class="action-btn approve-btn" title="Approve" onclick="return confirm('Approve this property?');">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo URLROOT . '/adminproperties/reject/' . $property->id; ?>" method="post" style="display:inline;">
                                                <button type="submit" class="action-btn reject-btn" title="Reject" onclick="return confirm('Reject this property?');">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php elseif ($property->approval_status === 'rejected'): ?>
                                            <form action="<?php echo URLROOT . '/adminproperties/approve/' . $property->id; ?>" method="post" style="display:inline;">
                                                <button type="submit" class="action-btn approve-btn" title="Re-approve" onclick="return confirm('Re-approve this property?');">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <!-- Delete property icon -->
                                        <form action="<?php echo URLROOT . '/adminproperties/delete/' . $property->id; ?>" method="post" style="display:inline;">
                                            <button type="submit" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this property? This cannot be undone!');" style="color:#dc3545;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No properties found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchProperties');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.properties-table tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>
<?php require APPROOT . '/views/inc/admin_footer.php'; ?>