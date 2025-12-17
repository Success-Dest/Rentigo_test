<?php require APPROOT . '/views/inc/manager_header.php' ?>;

<?php
// ADD PAGINATION
require_once APPROOT . '/../app/helpers/AutoPaginate.php';
AutoPaginate::init($data, 10); // 10  per page
?>

<div class="properties-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Property Management</h1>
            <p class="page-subtitle">Manage your properties and their status</p>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <div class="search-filters">
                <input type="text" class="search-input" placeholder="Search properties..." id="propertySearch">
                <select class="filter-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="complex">Complex</option>
                    <option value="townhome">Townhome</option>
                    <option value="condo">Condo</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="propertyTableBody">
                    <?php if (!empty($data['properties'])): ?>
                        <?php foreach ($data['properties'] as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property->address); ?></td>
                                <td><?php echo htmlspecialchars($property->owner_name ?? 'N/A'); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($property->property_type)); ?></td>
                                <td>
                                    <?php if (isset($property->occupancy_total) && isset($property->occupancy_occupied)): ?>
                                        <span class="font-medium"><?php echo $property->occupancy_occupied . '/' . $property->occupancy_total; ?></span>
                                        <span class="text-muted">
                                            (<?php echo round(($property->occupancy_total > 0 ? ($property->occupancy_occupied / $property->occupancy_total * 100) : 0)); ?>%)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    // Show status badge (approved, pending, maintenance, etc)
                                    $status = strtolower($property->status ?? 'unknown');
                                    $badgeClass = 'status-badge ';
                                    if ($status === 'approved' || $status === 'active') $badgeClass .= 'approved';
                                    elseif ($status === 'pending') $badgeClass .= 'pending';
                                    elseif ($status === 'maintenance') $badgeClass .= 'pending';
                                    else $badgeClass .= 'default';
                                    ?>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="btn-icon" title="View Details"
                                            href="<?php echo URLROOT . '/managerproperties/details/' . $property->id; ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Only view button! -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No properties assigned.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD PAGINATION HERE - Render at bottom -->
<?php echo AutoPaginate::render($data['_pagination']); ?>

<!-- JavaScript for client-side filtering -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('propertySearch');
        const typeFilter = document.getElementById('typeFilter');
        const tableBody = document.getElementById('propertyTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        function filterRows() {
            const searchValue = searchInput.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();

            for (let i = 0; i < rows.length; i++) {
                const address = rows[i].children[0].textContent.toLowerCase();
                const type = rows[i].children[2].textContent.toLowerCase();

                const matchesSearch = address.includes(searchValue);
                const matchesType = !typeValue || type === typeValue;

                if (matchesSearch && matchesType) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        searchInput.addEventListener('input', filterRows);
        typeFilter.addEventListener('change', filterRows);
    });
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>