<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">Tenant Inquiries</h1>
        <p class="page-subtitle">Manage messages from tenants and potential tenants</p>
    </div>
</div>

<?php flash('message_flash'); ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Total Messages</h3>
            <div class="stat-value"><?php echo count($data['messages'] ?? []); ?></div>
            <div class="stat-change">All time</div>
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-envelope-open"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Unread Messages</h3>
            <div class="stat-value"><?php echo $data['unreadCount'] ?? 0; ?></div>
            <div class="stat-change">Requires attention</div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-label">Read Messages</h3>
            <div class="stat-value">
                <?php echo count($data['messages'] ?? []) - ($data['unreadCount'] ?? 0); ?>
            </div>
            <div class="stat-change">Acknowledged</div>
        </div>
    </div>
</div>

<!-- Messages List -->
<div class="inquiries-container">
    <?php if (!empty($data['messages'])): ?>
        <?php foreach ($data['messages'] as $message): ?>
            <div class="inquiry-card <?php echo $message->is_read ? 'read' : 'new'; ?>">
                <div class="inquiry-header">
                    <div class="inquiry-info">
                        <h3><?php echo htmlspecialchars($message->sender_name ?? 'Tenant'); ?></h3>
                        <p class="inquiry-property">
                            <?php if ($message->property_address): ?>
                                RE: <?php echo htmlspecialchars($message->property_address); ?>
                            <?php endif; ?>
                        </p>
                        <p class="inquiry-date">
                            <?php
                                $time = strtotime($message->created_at);
                                $diff = time() - $time;

                                if ($diff < 3600) {
                                    echo 'Received ' . floor($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    echo 'Received ' . floor($diff / 3600) . ' hours ago';
                                } elseif ($diff < 604800) {
                                    echo 'Received ' . floor($diff / 86400) . ' days ago';
                                } else {
                                    echo 'Received ' . date('M d, Y', $time);
                                }
                            ?>
                        </p>
                    </div>
                    <div class="inquiry-status">
                        <?php if (!$message->is_read): ?>
                            <span class="badge badge-info">New</span>
                        <?php else: ?>
                            <span class="badge badge-success">Read</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="inquiry-body">
                    <div class="inquiry-details">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($message->sender_email ?? 'N/A'); ?></p>
                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($message->subject); ?></p>
                        <p><strong>Date:</strong> <?php echo date('M d, Y - H:i', strtotime($message->created_at)); ?></p>
                    </div>
                    <div class="inquiry-message">
                        <p><strong>Message:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($message->message)); ?></p>
                    </div>
                    <div class="inquiry-actions">
                        <a href="<?php echo URLROOT; ?>/messages/reply/<?php echo $message->id; ?>"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-reply"></i> Reply
                        </a>
                        <a href="<?php echo URLROOT; ?>/messages/thread/<?php echo $message->id; ?>"
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> View Full Thread
                        </a>
                        <?php if (!$message->is_read): ?>
                            <button onclick="markAsRead(<?php echo $message->id; ?>)"
                                    class="btn btn-outline btn-sm">
                                <i class="fas fa-check"></i> Mark as Read
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No messages yet</p>
            <span>Messages and inquiries from tenants will appear here.</span>
        </div>
    <?php endif; ?>
</div>

<script>
function markAsRead(messageId) {
    fetch('<?php echo URLROOT; ?>/messages/markAsRead/' + messageId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark message as read');
    });
}
</script>

<style>
.inquiries-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.inquiry-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.inquiry-card.new {
    border-left: 4px solid #3b82f6;
    background: #f8f9ff;
}

.inquiry-card.read {
    opacity: 0.9;
}

.inquiry-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.inquiry-info h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
}

.inquiry-property {
    color: #667eea;
    font-size: 14px;
    margin: 0 0 5px 0;
}

.inquiry-date {
    color: #999;
    font-size: 13px;
    margin: 0;
}

.inquiry-status {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
}

.priority-high {
    background: #fee;
    color: #e74c3c;
}

.inquiry-body {
    padding: 20px;
}

.inquiry-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.inquiry-details p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.inquiry-details strong {
    color: #333;
}

.inquiry-message {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.inquiry-message p:first-child {
    margin-bottom: 10px;
    color: #333;
}

.inquiry-message p:last-child {
    color: #444;
    line-height: 1.6;
}

.inquiry-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
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
</style>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>
