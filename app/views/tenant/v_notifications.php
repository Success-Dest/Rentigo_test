<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Notifications</h2>
            <p>Stay updated with your property activities</p>
        </div>
        <div class="header-actions">
            <?php if (($data['unreadCount'] ?? 0) > 0): ?>
                <a href="<?php echo URLROOT; ?>/tenant/markAllNotificationsRead" class="btn btn-secondary">
                    <i class="fas fa-check-double"></i> Mark all as read
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php flash('notification_message'); ?>

    <!-- Notifications Stats -->
    <div class="notification-stats">
        <div class="stat-item">
            <i class="fas fa-bell"></i>
            <span><?php echo $data['unreadCount'] ?? 0; ?> unread notification<?php echo ($data['unreadCount'] ?? 0) != 1 ? 's' : ''; ?></span>
        </div>
        <div class="stat-item">
            <i class="fas fa-envelope"></i>
            <span><?php echo count($data['notifications'] ?? []); ?> total notification<?php echo count($data['notifications'] ?? []) != 1 ? 's' : ''; ?></span>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="notifications-section">
        <?php if (!empty($data['notifications'])): ?>
            <?php foreach ($data['notifications'] as $notification): ?>
                <?php
                    // Determine icon and color based on type
                    $iconClass = 'fa-bell';
                    $indicatorClass = 'info';

                    switch($notification->type) {
                        case 'payment':
                            $iconClass = 'fa-dollar-sign';
                            $indicatorClass = 'warning';
                            break;
                        case 'booking':
                            $iconClass = 'fa-calendar-check';
                            $indicatorClass = 'success';
                            break;
                        case 'lease':
                            $iconClass = 'fa-file-contract';
                            $indicatorClass = 'info';
                            break;
                        case 'issue':
                        case 'maintenance':
                            $iconClass = 'fa-tools';
                            $indicatorClass = 'warning';
                            break;
                        case 'inspection':
                            $iconClass = 'fa-clipboard-check';
                            $indicatorClass = 'info';
                            break;
                        case 'property':
                            $iconClass = 'fa-home';
                            $indicatorClass = 'info';
                            break;
                        case 'alert':
                        case 'system':
                            $iconClass = 'fa-exclamation-triangle';
                            $indicatorClass = 'warning';
                            break;
                    }
                ?>
                <div class="notification-card <?php echo $notification->is_read ? '' : 'unread'; ?>"
                     data-notification-id="<?php echo $notification->id; ?>">
                    <div class="notification-indicator <?php echo $indicatorClass; ?>">
                        <i class="fas <?php echo $iconClass; ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <h4 class="notification-title"><?php echo htmlspecialchars($notification->title); ?></h4>
                            <div class="notification-meta">
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
                                <?php if (!$notification->is_read): ?>
                                    <div class="unread-indicator"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="notification-message"><?php echo htmlspecialchars($notification->message); ?></p>
                        <div class="notification-actions">
                            <?php if (!$notification->is_read): ?>
                                <button class="btn btn-sm btn-secondary"
                                        onclick="markAsRead(<?php echo $notification->id; ?>)">
                                    <i class="fas fa-check"></i> Mark as read
                                </button>
                            <?php endif; ?>
                            <?php if ($notification->link): ?>
                                <a href="<?php echo URLROOT . '/' . $notification->link; ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-right"></i> View
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
                <span>You'll receive notifications about your bookings, payments, and property updates here.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('<?php echo URLROOT; ?>/tenant/markNotificationRead/' + notificationId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update UI
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to mark notification as read', 'error');
    });
}

function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas ${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;

    Object.assign(notification.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        padding: "1rem 1.5rem",
        borderRadius: "0.5rem",
        color: "white",
        fontWeight: "500",
        zIndex: "9999",
        opacity: "0",
        transform: "translateY(-20px)",
        transition: "all 0.3s ease",
        maxWidth: "400px",
        boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)"
    });

    const colors = {
        success: "#10b981",
        warning: "#f59e0b",
        error: "#ef4444",
        info: "#3b82f6",
    };
    notification.style.backgroundColor = colors[type] || colors.info;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = "1";
        notification.style.transform = "translateY(0)";
    }, 100);

    setTimeout(() => {
        notification.style.opacity = "0";
        notification.style.transform = "translateY(-20px)";
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

function getNotificationIcon(type) {
    const icons = {
        success: "fa-check-circle",
        warning: "fa-exclamation-triangle",
        error: "fa-times-circle",
        info: "fa-info-circle"
    };
    return icons[type] || icons.info;
}
</script>

<style>
.notification-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-item i {
    font-size: 24px;
    color: #667eea;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
}

.empty-state i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 20px;
    font-weight: 600;
    color: #666;
    margin-bottom: 10px;
}

.empty-state span {
    font-size: 14px;
    color: #999;
}

.notification-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
</style>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>
