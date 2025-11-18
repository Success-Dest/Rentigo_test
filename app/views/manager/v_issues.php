<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="issues-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Issue Resolution</h1>
            <p class="page-subtitle">Track and resolve tenant-reported issues and maintenance requests.</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="dashboard-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search issues..." id="issueSearch">
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <div class="header-icon">
                <i class="fas fa-exclamation-circle"></i>
                <h2>Reported Issues</h2>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" onclick="showIssueTab('all')">
                    All (<?php echo isset($data['allIssues']) ? count($data['allIssues']) : 0; ?>)
                </button>
                <button class="tab-button" onclick="showIssueTab('pending')">
                    Pending (<?php echo isset($data['pendingIssues']) ? count($data['pendingIssues']) : 0; ?>)
                </button>
                <button class="tab-button" onclick="showIssueTab('in-progress')">
                    In Progress (<?php echo isset($data['inProgressIssues']) ? count($data['inProgressIssues']) : 0; ?>)
                </button>
                <button class="tab-button" onclick="showIssueTab('resolved')">
                    Resolved (<?php echo isset($data['resolvedIssues']) ? count($data['resolvedIssues']) : 0; ?>)
                </button>
                <button class="tab-button" onclick="showIssueTab('cancelled')">
                    Cancelled (<?php echo isset($data['cancelledIssues']) ? count($data['cancelledIssues']) : 0; ?>)
                </button>
            </div>

            <!-- All Issues Tab -->
            <div id="all-issues-tab" class="tab-content active">
                <div class="issues-cards">
                    <?php if (!empty($data['allIssues'])): ?>
                        <?php foreach ($data['allIssues'] as $issue): ?>
                            <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/manager/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                                <div class="issue-header">
                                    <div class="issue-title-priority">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($issue->title); ?></h3>
                                        <span class="priority-badge <?php echo htmlspecialchars($issue->priority); ?>">
                                            <?php echo strtoupper(htmlspecialchars($issue->priority)); ?>
                                        </span>
                                    </div>
                                    <span class="status-badge <?php echo htmlspecialchars($issue->status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($issue->status))); ?>
                                    </span>
                                </div>
                                <p class="issue-property"><?php echo htmlspecialchars($issue->property_address ?? ''); ?></p>
                                <p class="issue-description">
                                    <?php
                                    $desc = $issue->description ?? '';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <div class="issue-footer">
                                    <span>By <?php echo htmlspecialchars($issue->tenant_name ?? ''); ?></span>
                                    <span><?php echo date('Y-m-d', strtotime($issue->created_at)); ?></span>
                                    <span><?php echo htmlspecialchars($issue->category); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #6b7280;">No issues reported yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Issues Tab -->
            <div id="pending-issues-tab" class="tab-content">
                <div class="issues-cards">
                    <?php if (!empty($data['pendingIssues'])): ?>
                        <?php foreach ($data['pendingIssues'] as $issue): ?>
                            <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/manager/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                                <div class="issue-header">
                                    <div class="issue-title-priority">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($issue->title); ?></h3>
                                        <span class="priority-badge <?php echo htmlspecialchars($issue->priority); ?>">
                                            <?php echo strtoupper(htmlspecialchars($issue->priority)); ?>
                                        </span>
                                    </div>
                                    <span class="status-badge <?php echo htmlspecialchars($issue->status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($issue->status))); ?>
                                    </span>
                                </div>
                                <p class="issue-property"><?php echo htmlspecialchars($issue->property_address); ?></p>
                                <p class="issue-description">
                                    <?php
                                    $desc = $issue->description ?? '';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <div class="issue-footer">
                                    <span>By <?php echo htmlspecialchars($issue->tenant_name ?? ''); ?></span>
                                    <span><?php echo date('Y-m-d', strtotime($issue->created_at)); ?></span>
                                    <span><?php echo htmlspecialchars($issue->category); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #6b7280;">No pending issues.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- In Progress Issues Tab -->
            <div id="in-progress-issues-tab" class="tab-content">
                <div class="issues-cards">
                    <?php if (!empty($data['inProgressIssues'])): ?>
                        <?php foreach ($data['inProgressIssues'] as $issue): ?>
                            <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/manager/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                                <div class="issue-header">
                                    <div class="issue-title-priority">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($issue->title); ?></h3>
                                        <span class="priority-badge <?php echo htmlspecialchars($issue->priority); ?>">
                                            <?php echo strtoupper(htmlspecialchars($issue->priority)); ?>
                                        </span>
                                    </div>
                                    <span class="status-badge <?php echo htmlspecialchars($issue->status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($issue->status))); ?>
                                    </span>
                                </div>
                                <p class="issue-property"><?php echo htmlspecialchars($issue->property_address); ?></p>
                                <p class="issue-description">
                                    <?php
                                    $desc = $issue->description ?? '';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <div class="issue-footer">
                                    <span>By <?php echo htmlspecialchars($issue->tenant_name ?? ''); ?></span>
                                    <span><?php echo date('Y-m-d', strtotime($issue->created_at)); ?></span>
                                    <span><?php echo htmlspecialchars($issue->category); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #6b7280;">No issues in progress.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Resolved Issues Tab -->
            <div id="resolved-issues-tab" class="tab-content">
                <div class="issues-cards">
                    <?php if (!empty($data['resolvedIssues'])): ?>
                        <?php foreach ($data['resolvedIssues'] as $issue): ?>
                            <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/manager/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                                <div class="issue-header">
                                    <div class="issue-title-priority">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($issue->title); ?></h3>
                                        <span class="priority-badge <?php echo htmlspecialchars($issue->priority); ?>">
                                            <?php echo strtoupper(htmlspecialchars($issue->priority)); ?>
                                        </span>
                                    </div>
                                    <span class="status-badge <?php echo htmlspecialchars($issue->status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($issue->status))); ?>
                                    </span>
                                </div>
                                <p class="issue-property"><?php echo htmlspecialchars($issue->property_address); ?></p>
                                <p class="issue-description">
                                    <?php
                                    $desc = $issue->description ?? '';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <div class="issue-footer">
                                    <span>By <?php echo htmlspecialchars($issue->tenant_name ?? ''); ?></span>
                                    <span><?php echo date('Y-m-d', strtotime($issue->created_at)); ?></span>
                                    <span><?php echo htmlspecialchars($issue->category); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #6b7280;">No resolved issues.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancelled Issues Tab -->
            <div id="cancelled-issues-tab" class="tab-content">
                <div class="issues-cards">
                    <?php if (!empty($data['cancelledIssues'])): ?>
                        <?php foreach ($data['cancelledIssues'] as $issue): ?>
                            <div class="issue-card" onclick="window.location.href='<?php echo URLROOT; ?>/manager/issueDetails/<?php echo $issue->id; ?>'" style="cursor: pointer;">
                                <div class="issue-header">
                                    <div class="issue-title-priority">
                                        <h3 class="font-medium"><?php echo htmlspecialchars($issue->title); ?></h3>
                                        <span class="priority-badge <?php echo htmlspecialchars($issue->priority); ?>">
                                            <?php echo strtoupper(htmlspecialchars($issue->priority)); ?>
                                        </span>
                                    </div>
                                    <span class="status-badge <?php echo htmlspecialchars($issue->status); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($issue->status))); ?>
                                    </span>
                                </div>
                                <p class="issue-property"><?php echo htmlspecialchars($issue->property_address); ?></p>
                                <p class="issue-description">
                                    <?php
                                    $desc = $issue->description ?? '';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                                    ?>
                                </p>
                                <div class="issue-footer">
                                    <span>By <?php echo htmlspecialchars($issue->tenant_name ?? ''); ?></span>
                                    <span><?php echo date('Y-m-d', strtotime($issue->created_at)); ?></span>
                                    <span><?php echo htmlspecialchars($issue->category); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: #6b7280;">No cancelled issues.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ======== TAB SWITCHING ======== //
        const tabs = document.querySelectorAll(".tab-button");
        const tabContents = document.querySelectorAll(".tab-content");

        tabs.forEach(btn => {
            btn.addEventListener("click", function() {
                const tabName = this.getAttribute("onclick").match(/'(.+?)'/)[1];

                // Remove active from all
                tabContents.forEach(tc => tc.classList.remove("active"));
                tabs.forEach(b => b.classList.remove("active"));

                // Activate current
                const selectedTab = document.getElementById(`${tabName}-issues-tab`);
                if (selectedTab) selectedTab.classList.add("active");
                this.classList.add("active");

                // Reset search on tab switch
                document.getElementById("issueSearch").value = "";
                filterIssues("");
            });
        });

        // ======== SEARCH / FILTER ======== //
        const searchInput = document.getElementById("issueSearch");
        searchInput.addEventListener("input", function() {
            const query = this.value.toLowerCase();
            filterIssues(query);
        });

        function filterIssues(query) {
            const activeTab = document.querySelector(".tab-content.active");
            if (!activeTab) return;
            const cards = activeTab.querySelectorAll(".issue-card");

            cards.forEach(card => {
                const title = card.querySelector("h3").textContent.toLowerCase();
                const property = card.querySelector(".issue-property").textContent.toLowerCase();
                const desc = card.querySelector(".issue-description").textContent.toLowerCase();
                const tenant = card.querySelector(".issue-footer span:first-child").textContent.toLowerCase();

                if (title.includes(query) || property.includes(query) || desc.includes(query) || tenant.includes(query)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }

        // ======== SORTING ======== //
        // Optional: Add a dropdown
        const sortContainer = document.createElement("div");
        sortContainer.classList.add("sort-container");
        sortContainer.innerHTML = `
        <label for="sortSelect" class="sort-label">Sort by:</label>
        <select id="sortSelect" class="sort-select">
            <option value="date">Newest</option>
            <option value="priority">Priority</option>
            <option value="title">Title (A-Z)</option>
        </select>
    `;
        document.querySelector(".dashboard-section .section-header").appendChild(sortContainer);

        document.getElementById("sortSelect").addEventListener("change", function() {
            const criteria = this.value;
            sortIssues(criteria);
        });

        function sortIssues(criteria) {
            const activeTab = document.querySelector(".tab-content.active");
            if (!activeTab) return;

            const container = activeTab.querySelector(".issues-cards");
            const cards = Array.from(container.querySelectorAll(".issue-card"));

            cards.sort((a, b) => {
                if (criteria === "priority") {
                    const map = {
                        high: 3,
                        medium: 2,
                        low: 1
                    };
                    const aVal = map[a.querySelector(".priority-badge").textContent.trim().toLowerCase()] || 0;
                    const bVal = map[b.querySelector(".priority-badge").textContent.trim().toLowerCase()] || 0;
                    return bVal - aVal;
                } else if (criteria === "date") {
                    const aDate = new Date(a.querySelector(".issue-footer span:nth-child(2)").textContent);
                    const bDate = new Date(b.querySelector(".issue-footer span:nth-child(2)").textContent);
                    return bDate - aDate;
                } else if (criteria === "title") {
                    const aText = a.querySelector("h3").textContent.toLowerCase();
                    const bText = b.querySelector("h3").textContent.toLowerCase();
                    return aText.localeCompare(bText);
                }
            });

            // Re-append sorted cards
            container.innerHTML = "";
            cards.forEach(card => container.appendChild(card));
        }

    });
</script>
<style>
    .sort-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
        margin-top: 0.5rem;
    }

    .sort-label {
        font-weight: 500;
        color: #374151;
    }

    .sort-select {
        padding: 0.4rem 0.6rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        cursor: pointer;
    }
</style>


<?php require APPROOT . '/views/inc/manager_footer.php'; ?>