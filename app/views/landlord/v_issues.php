<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Tenant Inquiries</h1>
        <p class="page-subtitle">View and track inquiries reported by your tenants</p>
    </div>
</div>

<?php flash('issue_message'); ?>
<?php flash('issue_error'); ?>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #3b82f6;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Inquiries</h3>
            <div class="stat-value"><?php echo $data['issueStats']->total_issues ?? 0; ?></div>
            <div class="stat-change">All time</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #f59e0b;">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Pending</h3>
            <div class="stat-value"><?php echo $data['issueStats']->pending_count ?? 0; ?></div>
            <div class="stat-change">Awaiting action</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #3b82f6;">
            <i class="fas fa-spinner"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">In Progress</h3>
            <div class="stat-value"><?php echo $data['issueStats']->in_progress_count ?? 0; ?></div>
            <div class="stat-change">Being worked on</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #10b981;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Resolved</h3>
            <div class="stat-value"><?php echo $data['issueStats']->resolved_count ?? 0; ?></div>
            <div class="stat-change">Completed</div>
        </div>
    </div>
</div>

<!-- Issues Container -->
<div class="issues-container">
    <!-- Tabs -->
    <div class="tabs-nav">
        <button class="tab-button active" onclick="showTab('all')">
            All (<?php echo count($data['allIssues'] ?? []); ?>)
        </button>
        <button class="tab-button" onclick="showTab('pending')">
            Pending (<?php echo count($data['pendingIssues'] ?? []); ?>)
        </button>
        <button class="tab-button" onclick="showTab('in-progress')">
            In Progress (<?php echo count($data['inProgressIssues'] ?? []); ?>)
        </button>
        <button class="tab-button" onclick="showTab('resolved')">
            Resolved (<?php echo count($data['resolvedIssues'] ?? []); ?>)
        </button>
    </div>

    <!-- All Issues Tab -->
    <div id="all-tab" class="tab-content active">
        <?php if (!empty($data['allIssues'])): ?>
            <div class="issues-grid">
                <?php foreach ($data['allIssues'] as $issue): ?>
                    <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/landlord/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                        <div class="issue-header">
                            <div class="issue-title-section">
                                <h3><?php echo htmlspecialchars($issue->title); ?></h3>
                                <span class="priority-badge <?php echo $issue->priority; ?>">
                                    <?php echo strtoupper($issue->priority); ?>
                                </span>
                            </div>
                            <span class="status-badge <?php echo $issue->status; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $issue->status)); ?>
                            </span>
                        </div>
                        <div class="issue-body">
                            <p class="issue-property">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($issue->property_address); ?>
                            </p>
                            <p class="issue-tenant">
                                <i class="fas fa-user"></i>
                                Reported by: <?php echo htmlspecialchars($issue->tenant_name); ?>
                            </p>
                            <p class="issue-description">
                                <?php echo htmlspecialchars(substr($issue->description, 0, 100)); ?>
                                <?php echo strlen($issue->description) > 100 ? '...' : ''; ?>
                            </p>
                        </div>
                        <div class="issue-footer">
                            <span class="issue-category">
                                <i class="fas fa-tag"></i>
                                <?php echo ucfirst($issue->category); ?>
                            </span>
                            <span class="issue-date">
                                <?php echo date('M d, Y', strtotime($issue->created_at)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No issues reported yet</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pending Issues Tab -->
    <div id="pending-tab" class="tab-content">
        <?php if (!empty($data['pendingIssues'])): ?>
            <div class="issues-grid">
                <?php foreach ($data['pendingIssues'] as $issue): ?>
                    <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/landlord/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                        <div class="issue-header">
                            <div class="issue-title-section">
                                <h3><?php echo htmlspecialchars($issue->title); ?></h3>
                                <span class="priority-badge <?php echo $issue->priority; ?>">
                                    <?php echo strtoupper($issue->priority); ?>
                                </span>
                            </div>
                            <span class="status-badge <?php echo $issue->status; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $issue->status)); ?>
                            </span>
                        </div>
                        <div class="issue-body">
                            <p class="issue-property">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($issue->property_address); ?>
                            </p>
                            <p class="issue-tenant">
                                <i class="fas fa-user"></i>
                                Reported by: <?php echo htmlspecialchars($issue->tenant_name); ?>
                            </p>
                            <p class="issue-description">
                                <?php echo htmlspecialchars(substr($issue->description, 0, 100)); ?>
                                <?php echo strlen($issue->description) > 100 ? '...' : ''; ?>
                            </p>
                        </div>
                        <div class="issue-footer">
                            <span class="issue-category">
                                <i class="fas fa-tag"></i>
                                <?php echo ucfirst($issue->category); ?>
                            </span>
                            <span class="issue-date">
                                <?php echo date('M d, Y', strtotime($issue->created_at)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No pending issues</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- In Progress Issues Tab -->
    <div id="in-progress-tab" class="tab-content">
        <?php if (!empty($data['inProgressIssues'])): ?>
            <div class="issues-grid">
                <?php foreach ($data['inProgressIssues'] as $issue): ?>
                    <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/landlord/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                        <div class="issue-header">
                            <div class="issue-title-section">
                                <h3><?php echo htmlspecialchars($issue->title); ?></h3>
                                <span class="priority-badge <?php echo $issue->priority; ?>">
                                    <?php echo strtoupper($issue->priority); ?>
                                </span>
                            </div>
                            <span class="status-badge <?php echo $issue->status; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $issue->status)); ?>
                            </span>
                        </div>
                        <div class="issue-body">
                            <p class="issue-property">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($issue->property_address); ?>
                            </p>
                            <p class="issue-tenant">
                                <i class="fas fa-user"></i>
                                Reported by: <?php echo htmlspecialchars($issue->tenant_name); ?>
                            </p>
                            <p class="issue-description">
                                <?php echo htmlspecialchars(substr($issue->description, 0, 100)); ?>
                                <?php echo strlen($issue->description) > 100 ? '...' : ''; ?>
                            </p>
                        </div>
                        <div class="issue-footer">
                            <span class="issue-category">
                                <i class="fas fa-tag"></i>
                                <?php echo ucfirst($issue->category); ?>
                            </span>
                            <span class="issue-date">
                                <?php echo date('M d, Y', strtotime($issue->created_at)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No issues in progress</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Resolved Issues Tab -->
    <div id="resolved-tab" class="tab-content">
        <?php if (!empty($data['resolvedIssues'])): ?>
            <div class="issues-grid">
                <?php foreach ($data['resolvedIssues'] as $issue): ?>
                    <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/landlord/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                        <div class="issue-header">
                            <div class="issue-title-section">
                                <h3><?php echo htmlspecialchars($issue->title); ?></h3>
                                <span class="priority-badge <?php echo $issue->priority; ?>">
                                    <?php echo strtoupper($issue->priority); ?>
                                </span>
                            </div>
                            <span class="status-badge <?php echo $issue->status; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $issue->status)); ?>
                            </span>
                        </div>
                        <div class="issue-body">
                            <p class="issue-property">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($issue->property_address); ?>
                            </p>
                            <p class="issue-tenant">
                                <i class="fas fa-user"></i>
                                Reported by: <?php echo htmlspecialchars($issue->tenant_name); ?>
                            </p>
                            <p class="issue-description">
                                <?php echo htmlspecialchars(substr($issue->description, 0, 100)); ?>
                                <?php echo strlen($issue->description) > 100 ? '...' : ''; ?>
                            </p>
                        </div>
                        <div class="issue-footer">
                            <span class="issue-category">
                                <i class="fas fa-tag"></i>
                                <?php echo ucfirst($issue->category); ?>
                            </span>
                            <span class="issue-date">
                                Resolved: <?php echo date('M d, Y', strtotime($issue->resolved_at ?? $issue->updated_at)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No resolved issues</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 1rem;
    align-items: center;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
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

.issues-container {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.tabs-nav {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.tab-button {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.tab-button.active {
    color: #45a9ea;
    border-bottom-color: #45a9ea;
}

.tab-button:hover {
    color: #45a9ea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.issues-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.issue-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.25rem;
    transition: all 0.2s;
}

.issue-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.issue-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.issue-title-section {
    flex: 1;
}

.issue-title-section h3 {
    font-size: 1.125rem;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.priority-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.688rem;
    font-weight: 700;
}

.priority-badge.emergency {
    background: #fee2e2;
    color: #991b1b;
}

.priority-badge.high {
    background: #fed7aa;
    color: #9a3412;
}

.priority-badge.medium {
    background: #fef3c7;
    color: #92400e;
}

.priority-badge.low {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.in_progress {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.resolved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.issue-body {
    margin-bottom: 1rem;
}

.issue-property,
.issue-tenant {
    font-size: 0.875rem;
    color: #4b5563;
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.issue-property i,
.issue-tenant i {
    color: #9ca3af;
    width: 16px;
}

.issue-description {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0.75rem 0 0 0;
}

.issue-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.813rem;
    color: #6b7280;
}

.issue-category {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.empty-state p {
    font-size: 1.125rem;
    margin: 0;
}
</style>

<script>
function showTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(btn => btn.classList.remove('active'));

    // Show selected tab
    const selectedTab = document.getElementById(tabName + '-tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Activate button
    event.target.classList.add('active');
}
</script>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
