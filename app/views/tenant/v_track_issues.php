<?php
require APPROOT . '/views/inc/tenant_header.php';

// Function to calculate average days to resolve
function calculateAverageDaysToResolve($issues)
{
    $totalDays = 0;
    $resolvedCount = 0;

    foreach ($issues as $issue) {
        if ($issue->status === 'resolved' && !empty($issue->resolved_at)) {
            $resolvedDate = new DateTime($issue->resolved_at);
            $createdDate = new DateTime($issue->created_at);
            $daysToResolve = $resolvedDate->diff($createdDate)->days;

            $totalDays += $daysToResolve;
            $resolvedCount++;
        }
    }

    return $resolvedCount > 0 ? $totalDays / $resolvedCount : 0; // Return average or 0 if no resolved issues
}

// Handle filters
$statusFilter = $_POST['statusFilter'] ?? '';
$priorityFilter = $_POST['priorityFilter'] ?? '';
$categoryFilter = $_POST['categoryFilter'] ?? '';

// Calculate average days
$averageDaysToResolve = calculateAverageDaysToResolve($data['issues']);
?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Track Issue Status</h2>
            <p>Monitor the progress of your reported issues</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/tenant/report_issue" class="btn btn-primary">
                <i class="fas fa-plus"></i> Report New Issue
            </a>
        </div>
    </div>

    <!-- Issue Status Overview -->
    <div class="stats-grid">
        <!-- Pending Issues -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['pending_issues'] ?? 0; ?></h3>
                <p class="stat-label">Pending Issues</p>
            </div>
        </div>

        <!-- In Progress -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tools"></i></div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['in_progress_issues'] ?? 0; ?></h3>
                <p class="stat-label">In Progress</p>
            </div>
        </div>

        <!-- Resolved -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['resolved_issues'] ?? 0; ?></h3>
                <p class="stat-label">Resolved</p>
            </div>
        </div>

        <!-- Average Days -->
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo number_format($averageDaysToResolve, 1); ?></h3>
                <p class="stat-label">Avg. Days to Resolve</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Filter Issues</h3>
        </div>

        <form method="POST" action="">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select class="form-select" name="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?= $statusFilter == 'in_progress' ? 'selected' : ''; ?>>In Progress
                        </option>
                        <option value="resolved" <?= $statusFilter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Priority</label>
                    <select class="form-select" name="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="low" <?= $priorityFilter == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?= $priorityFilter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?= $priorityFilter == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="emergency" <?= $priorityFilter == 'emergency' ? 'selected' : ''; ?>>Emergency
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Category</label>
                    <select class="form-select" name="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="Plumbing" <?= $categoryFilter == 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="Electrical" <?= $categoryFilter == 'Electrical' ? 'selected' : ''; ?>>Electrical
                        </option>
                        <option value="Heating/Cooling" <?= $categoryFilter == 'Heating/Cooling' ? 'selected' : ''; ?>>
                            Heating/Cooling</option>
                        <option value="Maintenance" <?= $categoryFilter == 'Maintenance' ? 'selected' : ''; ?>>Maintenance
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <button class="btn btn-primary" id="btn" type=" submit">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Issues Table -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Your Issues</h3>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Issue ID</th>
                        <th>Property</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Report Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['issues'] as $issue): ?>
                        <?php
                        // Apply filters
                        if (
                            ($statusFilter && $issue->status !== $statusFilter) ||
                            ($priorityFilter && $issue->priority !== $priorityFilter) ||
                            ($categoryFilter && $issue->category !== $categoryFilter)
                        ) {
                            continue;
                        }
                        ?>
                        <?php
                        // Check if issue can be edited/deleted (within 1 minute of creation)
                        $createdTime = new DateTime($issue->created_at);
                        $currentTime = new DateTime();
                        $timeDiff = $currentTime->getTimestamp() - $createdTime->getTimestamp();
                        $canEdit = ($timeDiff <= 60); // 60 seconds = 1 minute
                        $canDelete = ($timeDiff <= 60); // 60 seconds = 1 minute
                        ?>
                        <tr>
                            <td><strong><?= $issue->id; ?></strong></td>
                            <td><?= $issue->property_address; ?></td>
                            <td><?= $issue->category; ?></td>
                            <td><span
                                    class="priority-badge <?= $issue->priority; ?>"><?= ucfirst($issue->priority); ?></span>
                            </td>
                            <td><span
                                    class="status-badge <?= $issue->status; ?>"><?= ucfirst(str_replace('_', ' ', $issue->status)); ?></span>
                            </td>
                            <td><?= date("F d, Y", strtotime($issue->created_at)); ?></td>
                            <td>
                                <?php if ($canEdit): ?>
                                    <a href="<?= URLROOT; ?>/issues/edit/<?= $issue->id; ?>"
                                        class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled title="Can only edit within 1 minute of creation">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <a href="<?= URLROOT; ?>/issues/delete/<?= $issue->id; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this issue? This action cannot be undone.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled title="Can only delete within 1 minute of creation">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Issue Details Modal -->
<div id="issueModal" class="modal-overlay hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>Issue Details</h3>
            <button class="modal-close" onclick="closeIssueModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>