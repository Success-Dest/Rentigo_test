<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Property Managers</h2>
            <p>Manage property manager registrations and assignments</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <?php
    $totalManagers = 0;
    $pendingCount = 0;
    $approvedCount = 0;
    $rejectedCount = 0;

    if (!empty($data['allManagers'])) {
        $totalManagers = count($data['allManagers']);
        foreach ($data['allManagers'] as $manager) {
            if ($manager->approval_status === 'pending') $pendingCount++;
            if ($manager->approval_status === 'approved') $approvedCount++;
            if ($manager->approval_status === 'rejected') $rejectedCount++;
        }
    }
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $totalManagers; ?></h3>
                <p class="stat-label">Total Managers</p>
                <span class="stat-change">All Registered</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $pendingCount; ?></h3>
                <p class="stat-label">Awaiting Review</p>
                <span class="stat-change">Pending Approvals</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $approvedCount; ?></h3>
                <p class="stat-label">Currently Approved</p>
                <span class="stat-change">Active Managers</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $rejectedCount; ?></h3>
                <p class="stat-label">Rejected</p>
                <span class="stat-change">Declined Applications</span>
            </div>
        </div>
    </div>


    <!-- Search and Filter -->
    <div class="search-filter-row">
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search managers..." id="searchManagers">
        </div>
        <div class="filter-container">
            <select class="filter-select" id="filterManagers" onchange="filterManagersByStatus()">
                <option value="">All Managers</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- Manager Applications Table -->
    <h3 class="table-title">All Property Manager Applications (<?php echo $totalManagers; ?>)</h3>
    <div class="table-container">
        <table class="managers-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Employee ID</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="managers-tbody">
                <?php if (!empty($data['allManagers'])): ?>
                    <?php foreach ($data['allManagers'] as $manager): ?>
                        <tr id="manager-row-<?php echo $manager->id; ?>" data-status="<?php echo $manager->approval_status; ?>">
                            <td><?php echo htmlspecialchars($manager->name); ?></td>
                            <td><?php echo htmlspecialchars($manager->email); ?></td>
                            <td>
                                <a href="<?php echo URLROOT; ?>/users/viewEmployeeId/<?php echo $manager->id; ?>" target="_blank">
                                    <?php echo htmlspecialchars($manager->employee_id_filename); ?>
                                </a>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($manager->created_at)); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = ucfirst($manager->approval_status);

                                switch ($manager->approval_status) {
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'approved':
                                        $statusClass = 'status-approved';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'status-rejected';
                                        break;
                                    default:
                                        $statusClass = 'status-unknown';
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($manager->approval_status === 'pending'): ?>
                                    <!-- Pending: Show Approve and Reject buttons -->
                                    <div class="action-buttons-group">
                                        <button class="action-btn approve-btn"
                                            id="approve-btn-<?php echo $manager->id; ?>"
                                            onclick="approveManagerCustom(<?php echo $manager->id; ?>)" title="Approve Manager">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="action-btn reject-btn"
                                            id="reject-btn-<?php echo $manager->id; ?>"
                                            onclick="rejectManagerCustom(<?php echo $manager->id; ?>)" title="Reject Manager">
                                            <i class=" fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php elseif ($manager->approval_status === 'approved'): ?>
                                    <!-- Approved: Show Remove button only -->
                                    <button class="action-btn reject-btn"
                                        id="remove-btn-<?php echo $manager->id; ?>"
                                        onclick="removeManagerCustom(<?php echo $manager->id; ?>)" title="Remove Manager">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php elseif ($manager->approval_status === 'rejected'): ?>
                                    <!-- Rejected: Show Remove button only -->
                                    <button class="action-btn reject-btn"
                                        id="remove-btn-<?php echo $manager->id; ?>"
                                        onclick="removeManagerCustom(<?php echo $manager->id; ?>)" title="Remove Manager">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No property managers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>

<!-- IMPORTANT: This script must come AFTER admin_footer.php to override admin.js -->
<script>
    // Approve Manager Function
    function approveManagerCustom(managerId) {
        console.log('=== APPROVE MANAGER FUNCTION CALLED ===');
        console.log('Manager ID:', managerId);

        if (!confirm('Are you sure you want to approve this manager?')) {
            console.log('User cancelled the operation');
            return;
        }

        const button = document.getElementById('approve-btn-' + managerId);
        if (!button) {
            console.error('❌ Button not found for manager ID:', managerId);
            alert('Error: Button not found');
            return;
        }

        console.log('✓ Button found:', button);
        button.disabled = true;
        button.innerHTML = '<i class=" fas fa-spinner fa-spin"></i> Processing...';

        const url = '<?php echo URLROOT; ?>/admin/approvePM/' + managerId;
        console.log('Fetch URL:', url);

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('=== RESPONSE RECEIVED ===');
                console.log('Status:', response.status);

                return response.clone().text().then(text => {
                    console.log('Raw response text:', text);

                    try {
                        const data = JSON.parse(text);
                        return {
                            data,
                            status: response.status
                        };
                    } catch (e) {
                        console.error('❌ JSON parse error:', e);
                        throw new Error('Server did not return valid JSON. Response: ' + text.substring(0, 200));
                    }
                });
            })
            .then(({
                data,
                status
            }) => {
                console.log('=== PARSED JSON DATA ===');
                console.log('Success:', data.success);
                console.log('Message:', data.message);

                if (data.success) {
                    alert(data.message || 'Manager approved successfully!');
                    location.reload();
                } else {
                    console.error('❌ Server returned success=false');
                    alert('Error: ' + (data.message || 'Failed to approve manager'));
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i> Approve';
                }
            })
            .catch(error => {
                console.error('=== FETCH ERROR ===');
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i> Approve';
            });
    }

    // Reject Manager Function
    function rejectManagerCustom(managerId) {
        console.log('=== REJECT MANAGER FUNCTION CALLED ===');
        console.log('Manager ID:', managerId);

        if (!confirm('Are you sure you want to reject this property manager application?')) {
            console.log('User cancelled the operation');
            return;
        }

        const button = document.getElementById('reject-btn-' + managerId);
        if (!button) {
            console.error('❌ Button not found for manager ID:', managerId);
            alert('Error: Button not found');
            return;
        }

        console.log('✓ Button found:', button);
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting...';

        const url = '<?php echo URLROOT; ?>/admin/rejectPM/' + managerId;
        console.log('Fetch URL:', url);

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('=== RESPONSE RECEIVED ===');
                console.log('Status:', response.status);

                return response.clone().text().then(text => {
                    console.log('Raw response text:', text);

                    try {
                        const data = JSON.parse(text);
                        return {
                            data,
                            status: response.status
                        };
                    } catch (e) {
                        console.error('❌ JSON parse error:', e);
                        throw new Error('Server did not return valid JSON. Response: ' + text.substring(0, 200));
                    }
                });
            })
            .then(({
                data,
                status
            }) => {
                console.log('=== PARSED JSON DATA ===');
                console.log('Success:', data.success);
                console.log('Message:', data.message);

                if (data.success) {
                    alert(data.message || 'Manager application rejected successfully!');
                    location.reload();
                } else {
                    console.error('❌ Server returned success=false');
                    alert('Error: ' + (data.message || 'Failed to reject manager'));
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-times"></i> Reject';
                }
            })
            .catch(error => {
                console.error('=== FETCH ERROR ===');
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-times"></i> Reject';
            });
    }

    // Remove Manager Function
    function removeManagerCustom(managerId) {
        console.log('=== REMOVE MANAGER FUNCTION CALLED ===');
        console.log('Manager ID:', managerId);

        if (!confirm('Are you sure you want to remove this property manager? This action cannot be undone.')) {
            console.log('User cancelled the operation');
            return;
        }

        const button = document.getElementById('remove-btn-' + managerId);
        if (!button) {
            console.error('❌ Button not found for manager ID:', managerId);
            alert('Error: Button not found');
            return;
        }

        console.log('✓ Button found:', button);
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';

        const url = '<?php echo URLROOT; ?>/admin/removePropertyManager/' + managerId;
        console.log('Fetch URL:', url);

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('=== RESPONSE RECEIVED ===');
                console.log('Status:', response.status);

                return response.clone().text().then(text => {
                    console.log('Raw response text:', text);

                    try {
                        const data = JSON.parse(text);
                        return {
                            data,
                            status: response.status
                        };
                    } catch (e) {
                        console.error('❌ JSON parse error:', e);
                        throw new Error('Server did not return valid JSON. Response: ' + text.substring(0, 200));
                    }
                });
            })
            .then(({
                data,
                status
            }) => {
                console.log('=== PARSED JSON DATA ===');
                console.log('Success:', data.success);
                console.log('Message:', data.message);

                if (data.success) {
                    alert(data.message || 'Manager removed successfully!');
                    location.reload();
                } else {
                    console.error('❌ Server returned success=false');
                    alert('Error: ' + (data.message || 'Failed to remove manager'));
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-trash"></i> Remove';
                }
            })
            .catch(error => {
                console.error('=== FETCH ERROR ===');
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash"></i> Remove';
            });
    }

    // Filter managers by status
    function filterManagersByStatus() {
        const filterValue = document.getElementById('filterManagers').value.toLowerCase();
        const rows = document.querySelectorAll('#managers-tbody tr');

        console.log('Filter value:', filterValue); // Debug log

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            console.log('Row status:', status); // Debug log

            if (filterValue === '' || status === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search managers
    const searchInput = document.getElementById('searchManagers');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#managers-tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
</script>

<style>
    /* Table Container */
    .table-container {
        overflow-x: auto;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Table Styles */
    .managers-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
    }

    .managers-table thead {
        background-color: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .managers-table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .managers-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        color: #1f2937;
        vertical-align: middle;
    }

    .managers-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .managers-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Status Badge Styles */
    .status-badge {
        display: inline-block;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.813rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        white-space: nowrap;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .status-unknown {
        background-color: #e5e7eb;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    /* Action Buttons Group */
    .action-buttons-group {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Action Button Styles */
    .action-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    /* Employee ID Link */
    .managers-table tbody td a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
    }

    .managers-table tbody td a:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .table-container {
            overflow-x: scroll;
        }

        .managers-table {
            min-width: 1000px;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.813rem;
        }

        .status-badge {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
        }

        .action-buttons-group {
            flex-direction: column;
            gap: 0.25rem;
        }
    }

    /* Loading Spinner Animation */
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>