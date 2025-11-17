<?php require APPROOT . '/views/inc/admin_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Send Notification</h2>
            <p>Send a notification to users across the platform</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo URLROOT; ?>/admin/notifications" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Notifications
            </a>
        </div>
    </div>

    <?php if (!empty($data['errors'])): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($data['errors'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Send Notification Form -->
    <div class="dashboard-section">
        <form method="POST" action="<?php echo URLROOT; ?>/admin/sendNotification" class="form-container">
            <div class="form-grid">
                <!-- Recipient Type -->
                <div class="form-group full-width">
                    <label for="recipient_type" class="form-label">
                        <i class="fas fa-users"></i> Send To
                        <span class="required">*</span>
                    </label>
                    <select name="recipient_type" id="recipient_type" class="form-select" required>
                        <option value="">Select recipient group</option>
                        <option value="all" <?php echo ($data['recipient_type'] ?? '') === 'all' ? 'selected' : ''; ?>>All Users</option>
                        <option value="tenants" <?php echo ($data['recipient_type'] ?? '') === 'tenants' ? 'selected' : ''; ?>>All Tenants</option>
                        <option value="landlords" <?php echo ($data['recipient_type'] ?? '') === 'landlords' ? 'selected' : ''; ?>>All Landlords</option>
                        <option value="managers" <?php echo ($data['recipient_type'] ?? '') === 'managers' ? 'selected' : ''; ?>>All Property Managers</option>
                    </select>
                </div>

                <!-- Notification Title -->
                <div class="form-group full-width">
                    <label for="title" class="form-label">
                        <i class="fas fa-heading"></i> Notification Title
                        <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-input"
                        placeholder="Enter notification title"
                        value="<?php echo htmlspecialchars($data['notification_title'] ?? ''); ?>"
                        required
                        maxlength="200"
                    >
                </div>

                <!-- Notification Message -->
                <div class="form-group full-width">
                    <label for="message" class="form-label">
                        <i class="fas fa-comment-alt"></i> Message
                        <span class="required">*</span>
                    </label>
                    <textarea
                        name="message"
                        id="message"
                        class="form-textarea"
                        rows="6"
                        placeholder="Enter the notification message"
                        required
                    ><?php echo htmlspecialchars($data['notification_message'] ?? ''); ?></textarea>
                    <small class="form-help">Maximum 500 characters</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
                <a href="<?php echo URLROOT; ?>/admin/notifications" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-container {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    .form-label i {
        margin-right: 0.5rem;
        color: #6b7280;
    }

    .required {
        color: #ef4444;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 1rem;
        transition: border-color 0.2s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #45a9ea;
        box-shadow: 0 0 0 3px rgba(69, 169, 234, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-help {
        display: block;
        margin-top: 0.375rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-start;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.375rem;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #45a9ea;
        color: white;
    }

    .btn-primary:hover {
        background: #3b8fd1;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>
