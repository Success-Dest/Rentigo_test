<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Notifications</h2>
            <p>Manage and send notifications to users</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/admin/sendNotification" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send Notification
            </a>
        </div>
    </div>

    <?php flash('notification_message'); ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']->total_sent ?? 0; ?></h3>
                <p class="stat-label">Total Sent</p>
                <span class="stat-change">All notifications</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']->total_recipients ?? 0; ?></h3>
                <p class="stat-label">Recipients</p>
                <span class="stat-change">Unique users</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']->read_count ?? 0; ?></h3>
                <p class="stat-label">Read</p>
                <span class="stat-change positive">Acknowledged</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-number"><?php echo $data['stats']->unread_count ?? 0; ?></h3>
                <p class="stat-label">Unread</p>
                <span class="stat-change">Pending</span>
            </div>
        </div>
    </div>

    <!-- Notification History with Tabs -->
    <div class="dashboard-section">
        <div class="section-header">
            <h3>Notification History</h3>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-button active" data-tab="admin-sent">
                    <i class="fas fa-user-shield"></i>
                    Admin Sent (<?php echo count($data['adminNotifications'] ?? []); ?>)
                </button>
                <button class="tab-button" data-tab="other-notifications">
                    <i class="fas fa-bell"></i>
                    Other Notifications (<?php echo count($data['otherNotifications'] ?? []); ?>)
                </button>
            </div>

            <!-- Tab 1: Admin Sent Notifications -->
            <div class="tab-content active" id="admin-sent">
                <div class="table-container">
                    <table class="data-table notifications-table">
                        <thead>
                            <tr>
                                <th>Notification Type</th>
                                <th>Who Sent It</th>
                                <th>To Whom</th>
                                <th>Date Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['adminNotifications'])): ?>
                                <?php foreach ($data['adminNotifications'] as $notification): ?>
                                    <tr>
                                        <td>
                                            <div class="notification-type-cell">
                                                <i class="fas fa-info-circle type-icon"></i>
                                                <span><?php echo ucfirst(str_replace('_', ' ', $notification->type)); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <i class="fas fa-user-shield"></i>
                                                <span><?php echo htmlspecialchars($notification->sender_name); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <?php
                                                $recipientIcon = match($notification->recipient_type) {
                                                    'tenant' => 'fas fa-user',
                                                    'landlord' => 'fas fa-home-user',
                                                    'property_manager' => 'fas fa-user-tie',
                                                    'admin' => 'fas fa-user-shield',
                                                    default => 'fas fa-user'
                                                };
                                                ?>
                                                <i class="<?php echo $recipientIcon; ?>"></i>
                                                <span><?php echo htmlspecialchars($notification->recipient_name); ?></span>
                                                <small class="user-type">(<?php echo ucfirst(str_replace('_', ' ', $notification->recipient_type)); ?>)</small>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M Y, H:i', strtotime($notification->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">
                                        <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                        No admin notifications sent yet. Click "Send Notification" to send one.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Other Notifications -->
            <div class="tab-content" id="other-notifications">
                <div class="table-container">
                    <table class="data-table notifications-table">
                        <thead>
                            <tr>
                                <th>Notification Type</th>
                                <th>Who Sent It</th>
                                <th>To Whom</th>
                                <th>Date Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['otherNotifications'])): ?>
                                <?php foreach ($data['otherNotifications'] as $notification): ?>
                                    <tr>
                                        <td>
                                            <div class="notification-type-cell">
                                                <?php
                                                // Determine icon based on type
                                                $typeIcon = match ($notification->type) {
                                                    'payment' => 'fas fa-dollar-sign',
                                                    'booking' => 'fas fa-calendar-check',
                                                    'lease' => 'fas fa-file-contract',
                                                    'property' => 'fas fa-home',
                                                    'issue', 'issue_reported', 'issue_update' => 'fas fa-exclamation-triangle',
                                                    'inspection', 'inspection_scheduled' => 'fas fa-clipboard-check',
                                                    'maintenance', 'maintenance_request' => 'fas fa-tools',
                                                    'review' => 'fas fa-star',
                                                    default => 'fas fa-bell'
                                                };
                                                ?>
                                                <i class="<?php echo $typeIcon; ?> type-icon"></i>
                                                <span><?php echo ucfirst(str_replace('_', ' ', $notification->type)); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <?php
                                                $senderIcon = match($notification->sender_type) {
                                                    'tenant' => 'fas fa-user',
                                                    'landlord' => 'fas fa-home-user',
                                                    'property_manager' => 'fas fa-user-tie',
                                                    'system' => 'fas fa-robot',
                                                    default => 'fas fa-user'
                                                };
                                                ?>
                                                <i class="<?php echo $senderIcon; ?>"></i>
                                                <span><?php echo htmlspecialchars($notification->sender_name); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <?php
                                                $recipientIcon = match($notification->recipient_type) {
                                                    'tenant' => 'fas fa-user',
                                                    'landlord' => 'fas fa-home-user',
                                                    'property_manager' => 'fas fa-user-tie',
                                                    'admin' => 'fas fa-user-shield',
                                                    default => 'fas fa-user'
                                                };
                                                ?>
                                                <i class="<?php echo $recipientIcon; ?>"></i>
                                                <span><?php echo htmlspecialchars($notification->recipient_name); ?></span>
                                                <small class="user-type">(<?php echo ucfirst(str_replace('_', ' ', $notification->recipient_type)); ?>)</small>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M Y, H:i', strtotime($notification->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #6b7280;">
                                        <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                        No other notifications in the system yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tabs Styling */
.tabs-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    background: #f9fafb;
}

.tab-button {
    flex: 1;
    padding: 1rem 1.5rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.tab-button:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab-button.active {
    color: #45a9ea;
    border-bottom-color: #45a9ea;
    background: white;
}

.tab-button i {
    font-size: 1.1rem;
}

.tab-content {
    display: none;
    padding: 1.5rem;
}

.tab-content.active {
    display: block;
}

/* Simplified Table Styling */
.notifications-table {
    width: 100%;
    border-collapse: collapse;
}

.notifications-table th {
    background: #f9fafb;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.notifications-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #4b5563;
}

.notifications-table tr:hover {
    background: #f9fafb;
}

.notification-type-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.type-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0f2fe;
    color: #45a9ea;
    border-radius: 50%;
    font-size: 0.875rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-info i {
    color: #6b7280;
    font-size: 1rem;
}

.user-info .user-type {
    color: #9ca3af;
    font-size: 0.75rem;
    margin-left: 0.25rem;
}

.table-container {
    overflow-x: auto;
}
</style>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
});
</script>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>
