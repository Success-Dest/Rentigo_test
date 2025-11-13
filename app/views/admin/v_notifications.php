<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Notifications</h2>
            <p>Manage and send notifications to users</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="sendNotification()">
                <i class="fas fa-paper-plane"></i> Send Notification
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['totalSent'] ?? 0; ?></h3>
                <p class="stat-label">Total Sent</p>
                <span class="stat-change">All notifications</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['delivered'] ?? 0; ?></h3>
                <p class="stat-label">Delivered</p>
                <span class="stat-change positive">Successfully sent</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['draft'] ?? 0; ?></h3>
                <p class="stat-label">Draft</p>
                <span class="stat-change">Pending send</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['totalRecipients'] ?? 0; ?></h3>
                <p class="stat-label">Recipients</p>
                <span class="stat-change">Total reach</span>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section">
        <div class="search-filter-content">
            <div class="search-input-wrapper">
                <input type="text" class="form-input" placeholder="Search notifications..." id="searchNotifications">
            </div>
            <div class="filter-dropdown-wrapper">
                <select class="form-select" id="filterNotifications">
                    <option value="">All Notifications</option>
                    <option value="sent">Sent</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Notification History -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Notification History (3)</h3>
        </div>

        <div class="table-container">
            <table class="data-table notifications-table">
                <thead>
                    <tr>
                        <th>Notification</th>
                        <th>Type</th>
                        <th>Audience</th>
                        <th>Recipients</th>
                        <th>Date Sent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-status="sent">
                        <td>
                            <div class="notification-info">
                                <div class="notification-icon system">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="notification-details">
                                    <div class="notification-title">System Maintenance Notice</div>
                                    <div class="notification-preview">The property management system will unde...</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="type-badge system">System</span>
                        </td>
                        <td>All</td>
                        <td>245</td>
                        <td>01/07/2024</td>
                        <td><span class="status-badge sent">Sent</span></td>
                        <td>
                            <div class="notification-actions">
                                <button class="action-btn view-btn" onclick="viewNotification('NOT001')" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn danger-btn" onclick="deleteNotification('NOT001')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-status="sent">
                        <td>
                            <div class="notification-info">
                                <div class="notification-icon payment">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="notification-details">
                                    <div class="notification-title">New Payment Policy</div>
                                    <div class="notification-preview">Starting next month, all rent payments must...</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="type-badge payment">Payment</span>
                        </td>
                        <td>Tenants</td>
                        <td>156</td>
                        <td>28/06/2024</td>
                        <td><span class="status-badge sent">Sent</span></td>
                        <td>
                            <div class="notification-actions">
                                <button class="action-btn view-btn" onclick="viewNotification('NOT002')" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn danger-btn" onclick="deleteNotification('NOT002')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-status="draft">
                        <td>
                            <div class="notification-info">
                                <div class="notification-icon general">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="notification-details">
                                    <div class="notification-title">Holiday Schedule Update</div>
                                    <div class="notification-preview">Please note the updated office hours during...</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="type-badge general">General</span>
                        </td>
                        <td>All</td>
                        <td>-</td>
                        <td>-</td>
                        <td><span class="status-badge draft">Draft</span></td>
                        <td>
                            <div class="notification-actions">
                                <button class="action-btn view-btn" onclick="editNotification('NOT003')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn send-btn" onclick="sendDraftNotification('NOT003')" title="Send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button class="action-btn danger-btn" onclick="deleteNotification('NOT003')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Notification Management Functions - Global scope for onclick handlers
    function sendNotification() {
        showNotification('Send Notification functionality to be implemented', 'info')
        // Here you would open a compose notification modal or navigate to compose page
    }

    function viewNotification(notificationId) {
        console.log('Viewing notification:', notificationId)
        showNotification('Opening notification details...', 'info')
        // Here you would open a view modal with full notification details
    }

    function editNotification(notificationId) {
        console.log('Editing notification:', notificationId)
        showNotification('Opening notification editor...', 'info')
        // Here you would open an edit modal or navigate to edit page
    }

    function sendDraftNotification(notificationId) {
        if (confirm('Are you sure you want to send this notification?')) {
            const row = event.target.closest('tr')
            const statusCell = row.querySelector('.status-badge')
            const actionsCell = row.querySelector('.notification-actions')
            const recipientsCell = row.cells[3]
            const dateSentCell = row.cells[4]

            // Update status
            statusCell.textContent = 'Sent'
            statusCell.className = 'status-badge sent'

            // Update data attribute
            row.dataset.status = 'sent'

            // Update recipients and date
            recipientsCell.textContent = '245'
            dateSentCell.textContent = new Date().toLocaleDateString('en-GB')

            // Update actions - remove send and edit, keep view and delete
            actionsCell.innerHTML = `
            <button class="action-btn view-btn" onclick="viewNotification('${notificationId}')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button class="action-btn danger-btn" onclick="deleteNotification('${notificationId}')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        `

            showNotification('Notification sent successfully!', 'success')
            updateNotificationStats()
        }
    }

    function deleteNotification(notificationId) {
        if (confirm('Are you sure you want to delete this notification? This action cannot be undone.')) {
            const row = event.target.closest('tr')
            row.remove()

            // Update count in section header
            const sectionHeader = document.querySelector('.section-header h3')
            if (sectionHeader) {
                const currentCount = parseInt(sectionHeader.textContent.match(/\((\d+)\)/)?.[1] || 0)
                sectionHeader.textContent = `Notification History (${Math.max(0, currentCount - 1)})`
            }

            showNotification('Notification deleted successfully!', 'success')
            updateNotificationStats()
        }
    }

    function updateNotificationStats() {
        const rows = document.querySelectorAll('.notifications-table tbody tr')
        const totalNotifications = rows.length

        let sentCount = 0
        let draftCount = 0
        let totalRecipients = 0

        rows.forEach(row => {
            const status = row.dataset.status
            if (status === 'sent') {
                sentCount++
                const recipients = parseInt(row.cells[3].textContent) || 0
                totalRecipients += recipients
            }
            if (status === 'draft') draftCount++
        })

        // Update stat cards
        const statCards = document.querySelectorAll('.stat-card')
        if (statCards.length >= 4) {
            // Total Notifications
            const totalStatNumber = statCards[0].querySelector('.stat-number')
            if (totalStatNumber) totalStatNumber.textContent = totalNotifications

            // Sent Notifications
            const sentStatNumber = statCards[1].querySelector('.stat-number')
            if (sentStatNumber) sentStatNumber.textContent = sentCount

            // Draft Notifications
            const draftStatNumber = statCards[2].querySelector('.stat-number')
            if (draftStatNumber) draftStatNumber.textContent = draftCount

            // Total Recipients
            const recipientsStatNumber = statCards[3].querySelector('.stat-number')
            if (recipientsStatNumber) recipientsStatNumber.textContent = totalRecipients
        }
    }

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchNotifications')
        const filterDropdown = document.getElementById('filterNotifications')

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase()
                filterNotifications(searchTerm, filterDropdown.value)
            })
        }

        if (filterDropdown) {
            filterDropdown.addEventListener('change', function() {
                const searchTerm = searchInput.value.toLowerCase()
                filterNotifications(searchTerm, this.value)
            })
        }
    })

    function filterNotifications(searchTerm, statusFilter) {
        const rows = document.querySelectorAll('.notifications-table tbody tr')
        let visibleCount = 0

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase()
            const rowStatus = row.dataset.status

            const matchesSearch = !searchTerm || rowText.includes(searchTerm)
            const matchesStatus = !statusFilter || rowStatus === statusFilter

            if (matchesSearch && matchesStatus) {
                row.style.display = ''
                visibleCount++
            } else {
                row.style.display = 'none'
            }
        })

        // Update section header count
        const sectionHeader = document.querySelector('.section-header h3')
        if (sectionHeader) {
            const baseText = sectionHeader.textContent.replace(/\(\d+\)/, '')
            sectionHeader.textContent = `${baseText}(${visibleCount})`
        }
    }
</script>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>