<?php require APPROOT . '/views/inc/manager_header.php'; ?>
<?php

// Read filters from query string (no JS needed)
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';     // values: all | Issue | Maintanace
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all'; // values: all | scheduled | in-progress | completed

// Normalize helper
$normalize = function ($v) {
    $v = strtolower((string) $v);
    $v = str_replace('_', '-', $v);
    return trim($v);
};

$filteredInspections = [];
if (!empty($data['inspections']) && is_iterable($data['inspections'])) {
    foreach ($data['inspections'] as $inspection) {
        $rowType = $normalize($inspection->type ?? '');
        $rowStatus = $normalize($inspection->status ?? '');
        $rowText = $normalize(
            'ins-' . ($inspection->id ?? '') . ' ' .
                ($inspection->property ?? '') . ' ' .
                ($inspection->type ?? '') . ' ' .
                ($inspection->scheduled_date ?? '') . ' ' .
                ($inspection->status ?? '') . ' ' .
                ($inspection->issues ?? '')
        );

        $matchesType = ($typeFilter === 'all') || ($normalize($typeFilter) === $rowType);
        $matchesStatus = ($statusFilter === 'all') || ($normalize($statusFilter) === $rowStatus);
        $matchesSearch = ($searchQuery === '') || (strpos($rowText, $normalize($searchQuery)) !== false);

        if ($matchesType && $matchesStatus && $matchesSearch) {
            $filteredInspections[] = $inspection;
        }
    }
}
?>

<div class="inspections-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Pre-Inspection Reports</h1>
            <p class="page-subtitle">Manage inspection checklists and reports</p>
        </div>
        <div class="header-right">
            <a href="<?php echo URLROOT; ?>/inspections/add" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Schedule Inspection
            </a>
        </div>
    </div>

    <div class="tabs-container">
        <!-- All Inspections Tab -->
        <div id="all-inspections-tab" class="tab-content active">
            <!-- Search and Filters -->
            <div class="dashboard-section"
                style="padding: 1rem 1rem 0 1rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                <!-- Replace static div with a GET form -->
                <form method="get" action="<?php echo htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')); ?>"
                    class="filters-grid"
                    style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
                    <!-- Search box -->
                    <div class="search-container"
                        style="flex: 1 1 250px; display: flex; align-items: center; background: #f5f6fa; border-radius: 6px; padding: 0.5rem 0.75rem;">
                        <i class="fas fa-search search-icon" style="color: #888; margin-right: 0.5rem;"></i>
                        <input type="text" class="search-input" placeholder="Search inspections..."
                            id="inspectionSearch" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>"
                            style="border: none; background: transparent; width: 100%; outline: none; font-size: 0.95rem;">
                    </div>

                    <!-- Type filter -->
                    <select class="filter-select" id="typeFilter" name="type"
                        style="flex: 0 1 160px; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; background: #fff; font-size: 0.9rem;">
                        <option value="all" <?php echo ($typeFilter === 'all') ? 'selected' : ''; ?>>All Types</option>
                        <option value="Issue" <?php echo ($typeFilter === 'Issue') ? 'selected' : ''; ?>>Issue</option>
                        <option value="Maintanace" <?php echo ($typeFilter === 'Maintanace') ? 'selected' : ''; ?>>
                            Maintanace</option>
                    </select>

                    <!-- Status filter -->
                    <select class="filter-select" id="statusFilter" name="status"
                        style="flex: 0 1 160px; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc; background: #fff; font-size: 0.9rem;">
                        <option value="all" <?php echo ($statusFilter === 'all') ? 'selected' : ''; ?>>All Status</option>
                        <option value="scheduled" <?php echo ($statusFilter === 'scheduled') ? 'selected' : ''; ?>>
                            Scheduled</option>
                        <option value="in-progress" <?php echo ($statusFilter === 'in-progress') ? 'selected' : ''; ?>>In
                            Progress</option>
                        <option value="completed" <?php echo ($statusFilter === 'completed') ? 'selected' : ''; ?>>
                            Completed</option>
                    </select>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="submit" class="btn btn-secondary"
                            style="padding: 0.5rem 1rem; background: #007bff; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
                            Apply Filters
                        </button>
                        <a class="btn btn-outline"
                            href="<?php echo htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')); ?>"
                            style="padding: 0.5rem 1rem; border: 1px solid #ccc; border-radius: 6px; color: #333; text-decoration: none; background: #f8f8f8;">
                            Reset
                        </a>
                    </div>
                </form>
            </div>


            <!-- Inspections Table -->
            <div class="dashboard-section">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>PROPERTY</th>
                                <th>TYPE</th>
                                <th>SCHEDULED DATE</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($filteredInspections)): ?>
                                <?php foreach ($filteredInspections as $inspection): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inspection->property_address ?? 'N/A'); ?></td>
                                        <td><?php echo ucfirst($inspection->type); ?></td>
                                        <td><?php echo $inspection->scheduled_date; ?></td>
                                        <td><span
                                                class="status-badge pending"><?php echo ucfirst($inspection->status); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo URLROOT; ?>/inspections/edit/<?php echo $inspection->id; ?>"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="confirmDelete(<?php echo $inspection->id; ?>)">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No inspections found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for deletion -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="inspection_id" id="deleteInspectionId">
</form>

<script>
    function confirmDelete(inspectionId) {
        if (confirm('Are you sure you want to delete this inspection? This action cannot be undone.')) {
            // Set the inspection ID in hidden form
            document.getElementById('deleteInspectionId').value = inspectionId;

            // Set form action to delete URL
            const form = document.getElementById('deleteForm');
            form.action = '<?php echo URLROOT; ?>/inspections/delete/' + inspectionId;

            // Submit form via POST
            form.submit();
        }
    }
</script>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>