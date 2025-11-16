<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Stay updated with your property management activities</p>
    </div>
    <div class="header-actions">
        <?php if (($data['unreadCount'] ?? 0) > 0): ?>
            <a href="<?php echo URLROOT; ?>/landlord/markAllNotificationsRead"
               class="btn btn-secondary">
                <i class="fas fa-check-double"></i> Mark All Read
            </a>
        <?php endif; ?>
    </div>
</div>

<?php flash('notification_message'); ?>

<!-- Notification Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background-color: var(--primary-color);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Notifications</h3>
            <div class="stat-value"><?php echo count($data['notifications'] ?? []); ?></div>
            <div class="stat-change">All time</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon" style="background-color: var(--warning-color);">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Unread</h3>
            <div class="stat-value"><?php echo $data['unreadCount'] ?? 0; ?></div>
            <div class="stat-change">Requires attention</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon" style="background-color: var(--success-color);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Read</h3>
            <div class="stat-value">
                <?php echo count($data['notifications'] ?? []) - ($data['unreadCount'] ?? 0); ?>
            </div>
            <div class="stat-change">Acknowledged</div>
        </div>
    </div>
</div>

<!-- Notifications List -->
<div class="content-card">
    <div class="card-header">
        <h2 class="card-title">All Notifications</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($data['notifications'])): ?>
            <div class="notifications-container">
                <?php foreach ($data['notifications'] as $notification): ?>
                    <?php
                        // Determine icon and color based on type
                        $iconClass = 'fa-bell';
                        $iconColor = 'var(--primary-color)';
                        $bgColor = 'rgba(37, 99, 235, 0.1)';

                        switch($notification->type) {
                            case 'booking':
                                $iconClass = 'fa-calendar-check';
                                $iconColor = 'var(--success-color)';
                                $bgColor = 'rgba(16, 185, 129, 0.1)';
                                break;
                            case 'payment':
                                $iconClass = 'fa-dollar-sign';
                                $iconColor = 'var(--success-color)';
                                $bgColor = 'rgba(16, 185, 129, 0.1)';
                                break;
                            case 'maintenance':
                            case 'issue':
                                $iconClass = 'fa-tools';
                                $iconColor = 'var(--warning-color)';
                                $bgColor = 'rgba(245, 158, 11, 0.1)';
                                break;
                            case 'inspection':
                                $iconClass = 'fa-clipboard-check';
                                $iconColor = 'var(--info-color)';
                                $bgColor = 'rgba(59, 130, 246, 0.1)';
                                break;
                            case 'lease':
                                $iconClass = 'fa-file-contract';
                                $iconColor = 'var(--primary-color)';
                                $bgColor = 'rgba(37, 99, 235, 0.1)';
                                break;
                            case 'property':
                                $iconClass = 'fa-home';
                                $iconColor = 'var(--info-color)';
                                $bgColor = 'rgba(59, 130, 246, 0.1)';
                                break;
                            case 'system':
                            case 'alert':
                                $iconClass = 'fa-exclamation-triangle';
                                $iconColor = 'var(--danger-color)';
                                $bgColor = 'rgba(239, 68, 68, 0.1)';
                                break;
                        }
                    ?>
                    <div class="notification-item <?php echo $notification->is_read ? 'read' : 'unread'; ?>"
                         data-notification-id="<?php echo $notification->id; ?>">
                        <div class="notification-icon"
                             style="width: 48px; height: 48px; border-radius: 50%; background-color: <?php echo $bgColor; ?>; color: <?php echo $iconColor; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-header">
                                <h4><?php echo htmlspecialchars($notification->title); ?></h4>
                                <span class="notification-time">
                                    <?php
                                        $time = strtotime($notification->created_at);
                                        $diff = time() - $time;

                                        if ($diff < 3600) {
                                            echo floor($diff / 60) . ' minutes ago';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' hours ago';
                                        } elseif ($diff < 604800) {
                                            echo floor($diff / 86400) . ' days ago';
                                        } else {
                                            echo date('M d, Y', $time);
                                        }
                                    ?>
                                </span>
                            </div>
                            <p><?php echo htmlspecialchars($notification->message); ?></p>
                            <div class="notification-meta">
                                <span class="notification-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo ucfirst($notification->type); ?>
                                </span>
                                <span class="notification-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('M d, Y - H:i', strtotime($notification->created_at)); ?>
                                </span>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!$notification->is_read): ?>
                                <button class="btn btn-secondary btn-sm"
                                        onclick="markAsRead(<?php echo $notification->id; ?>)">
                                    <i class="fas fa-check"></i> Mark Read
                                </button>
                            <?php endif; ?>
                            <?php if ($notification->link): ?>
                                <a href="<?php echo URLROOT . '/' . ltrim($notification->link, '/'); ?>"
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-right"></i> View Details
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-outline btn-sm"
                                    onclick="deleteNotification(<?php echo $notification->id; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
                <span>You'll receive notifications here about bookings, payments, maintenance requests, and more.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('<?php echo URLROOT; ?>/landlord/markNotificationRead/' + notificationId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notifElement) {
                notifElement.classList.remove('unread');
                notifElement.classList.add('read');
                const button = notifElement.querySelector('.btn-secondary');
                if (button) button.remove();
            }
            // Reload to update counts
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read');
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        window.location.href = '<?php echo URLROOT; ?>/landlord/deleteNotification/' + notificationId;
    }
}
</script>

<style>
.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    align-items: flex-start;
    transition: all 0.3s ease;
}

.notification-item.unread {
    background: #f8f9ff;
    border-left: 4px solid #667eea;
}

.notification-item.read {
    opacity: 0.8;
}

.notification-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.notification-content {
    flex: 1;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.notification-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.notification-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.notification-content > p {
    margin: 0 0 12px 0;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.notification-meta {
    display: flex;
    gap: 20px;
    font-size: 12px;
    color: #999;
}

.notification-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
}

.empty-state p {
    font-size: 20px;
    margin-bottom: 10px;
    color: #666;
    font-weight: 600;
}

.empty-state span {
    font-size: 14px;
    color: #999;
}
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
