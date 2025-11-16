<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<div class="edit-inspection-content">
    <div class="page-header">
        <div class="header-left">
            <h1 class="page-title">Edit Inspection</h1>
            <p class="page-subtitle">Update the details of the inspection</p>
        </div>
    </div>

    <div class="form-container">
        <form action="<?php echo URLROOT; ?>/inspections/edit/<?php echo $data['inspection']->id; ?>" method="post" class="inspection-form">

            <!-- Property Field -->
            <div class="form-group">
                <label for="property_id">Property <span style="color:red;">*</span></label>
                <select
                    id="property_id"
                    name="property_id"
                    class="form-control <?php echo !empty($data['property_id_err']) ? 'is-invalid' : ''; ?>"
                    required>
                    <option value="">-- Select Property --</option>
                    <?php
                    // Fetch all properties for edit form
                    $M_Inspection = new M_Inspection();
                    $properties = $M_Inspection->getAllPropertiesByManager($_SESSION['user_id']);

                    if (!empty($properties)):
                        foreach ($properties as $property):
                            // Check if this property matches the current inspection's property or submitted value
                            $isSelected = false;
                            if (isset($data['property_id']) && $data['property_id'] == $property->id) {
                                $isSelected = true;
                            } elseif (!isset($data['property_id']) && $property->id == $data['inspection']->property_id) {
                                $isSelected = true;
                            }
                            $selected = $isSelected ? 'selected' : '';
                    ?>
                            <option value="<?php echo $property->id; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($property->address); ?>
                            </option>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </select>
                <?php if (!empty($data['property_id_err'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $data['property_id_err']; ?>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">Current: <?php echo htmlspecialchars($data['inspection']->property_address ?? 'N/A'); ?></small>
            </div>

            <!-- Issue Field -->
            <div class="form-group">
                <label for="issue_id">Issue <span style="color:red;">*</span></label>
                <select
                    id="issue_id"
                    name="issue_id"
                    class="form-control <?php echo !empty($data['issue_id_err']) ? 'is-invalid' : ''; ?>"
                    required>
                    <option value="<?php echo $data['inspection']->issue_id ?? ''; ?>" selected>
                        <?php echo $data['inspection']->issue_id ? 'Current Issue ID: ' . $data['inspection']->issue_id : 'No Issue Linked'; ?>
                    </option>
                </select>
                <?php if (!empty($data['issue_id_err'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $data['issue_id_err']; ?>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted" id="issue-helper">Change property to see other issues</small>
                <div id="issue-loader" style="display: none; margin-top: 10px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading issues...
                </div>
            </div>

            <!-- Inspection Type Field -->
            <div class="form-group">
                <label for="type">Inspection Type <span style="color:red;">*</span></label>
                <select
                    id="type"
                    name="type"
                    class="form-control <?php echo !empty($data['type_err']) ? 'is-invalid' : ''; ?>"
                    required>
                    <?php
                    $selectedType = isset($data['type']) ? $data['type'] : $data['inspection']->type;
                    ?>
                    <option value="">-- Select Type --</option>
                    <option value="routine" <?php echo $selectedType == 'routine' ? 'selected' : ''; ?>>Routine</option>
                    <option value="move_in" <?php echo $selectedType == 'move_in' ? 'selected' : ''; ?>>Move In</option>
                    <option value="move_out" <?php echo $selectedType == 'move_out' ? 'selected' : ''; ?>>Move Out</option>
                    <option value="maintenance" <?php echo $selectedType == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="annual" <?php echo $selectedType == 'annual' ? 'selected' : ''; ?>>Annual</option>
                    <option value="emergency" <?php echo $selectedType == 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                    <option value="issue" <?php echo $selectedType == 'issue' ? 'selected' : ''; ?>>Issue</option>
                </select>
                <?php if (!empty($data['type_err'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $data['type_err']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Scheduled Date Field -->
            <div class="form-group">
                <label for="date">Scheduled Date <span style="color:red;">*</span></label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="form-control <?php echo !empty($data['date_err']) ? 'is-invalid' : ''; ?>"
                    value="<?php echo isset($data['date']) ? $data['date'] : $data['inspection']->scheduled_date; ?>"
                    required>
                <?php if (!empty($data['date_err'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $data['date_err']; ?>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">Date must be in the future (unless marking as completed)</small>
            </div>

            <!-- Status Field -->
            <div class="form-group">
                <label for="status">Status <span style="color:red;">*</span></label>
                <select
                    id="status"
                    name="status"
                    class="form-control <?php echo !empty($data['status_err']) ? 'is-invalid' : ''; ?>"
                    required>
                    <?php
                    $selectedStatus = isset($data['status']) ? $data['status'] : $data['inspection']->status;
                    ?>
                    <option value="scheduled" <?php echo $selectedStatus == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="in_progress" <?php echo $selectedStatus == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $selectedStatus == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $selectedStatus == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <?php if (!empty($data['status_err'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $data['status_err']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Inspection
                </button>
                <a href="<?php echo URLROOT; ?>/inspections/index" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const propertySelect = document.getElementById('property_id');
        const issueSelect = document.getElementById('issue_id');
        const issueLoader = document.getElementById('issue-loader');
        const issueHelper = document.getElementById('issue-helper');

        // Store the original issue ID
        const originalIssueId = <?php echo $data['inspection']->issues; ?>;
        const selectedIssueId = '<?php echo isset($data['issue_id']) ? $data['issue_id'] : ''; ?>';
        let currentPropertyId = propertySelect.value;

        propertySelect.addEventListener('change', function() {
            const propertyId = this.value;

            if (!propertyId) {
                issueSelect.innerHTML = '<option value="' + originalIssueId + '" selected>Current Issue ID: ' + originalIssueId + '</option>';
                issueHelper.textContent = 'Select a property to see available issues';
                issueHelper.style.color = '#6c757d';
                return;
            }

            loadIssues(propertyId);
        });

        // Load issues for the currently selected property on page load
        if (currentPropertyId) {
            loadIssues(currentPropertyId);
        }

        function loadIssues(propertyId) {
            // Show loader
            issueLoader.style.display = 'block';
            issueSelect.disabled = true;
            issueSelect.innerHTML = '<option value="">Loading...</option>';
            issueHelper.textContent = 'Loading issues...';
            issueHelper.style.color = '#6c757d';

            // Fetch issues via AJAX
            fetch('<?php echo URLROOT; ?>/inspections/getIssuesByProperty/' + propertyId)
                .then(response => response.json())
                .then(data => {
                    issueLoader.style.display = 'none';

                    if (data.success && data.issues.length > 0) {
                        issueSelect.innerHTML = '<option value="">-- Select an Issue --</option>';

                        data.issues.forEach(issue => {
                            const option = document.createElement('option');
                            option.value = issue.id;

                            // Select the current issue or submitted issue if it's in the list
                            if ((selectedIssueId && issue.id == selectedIssueId) || (!selectedIssueId && issue.id == originalIssueId)) {
                                option.selected = true;
                            }

                            // Format the option text with issue details
                            let priorityBadge = '';
                            switch (issue.priority) {
                                case 'emergency':
                                    priorityBadge = '🔴 Emergency';
                                    break;
                                case 'high':
                                    priorityBadge = '🟠 High';
                                    break;
                                case 'medium':
                                    priorityBadge = '🟡 Medium';
                                    break;
                                case 'low':
                                    priorityBadge = '🟢 Low';
                                    break;
                            }

                            option.textContent = `${issue.title} - ${issue.category} [${priorityBadge}] - ${issue.status}`;
                            option.title = `${issue.description} | Reported by: ${issue.tenant_name || 'N/A'}`;

                            issueSelect.appendChild(option);
                        });

                        issueSelect.disabled = false;
                        issueHelper.textContent = `${data.issues.length} issue(s) found. Select one to inspect.`;
                        issueHelper.style.color = '#28a745';
                    } else {
                        issueSelect.innerHTML = '<option value="">No issues found for this property</option>';
                        issueHelper.textContent = 'No pending issues found for this property';
                        issueHelper.style.color = '#6c757d';
                    }
                })
                .catch(error => {
                    console.error('Error fetching issues:', error);
                    issueLoader.style.display = 'none';
                    issueSelect.innerHTML = '<option value="' + originalIssueId + '" selected>Current Issue ID: ' + originalIssueId + ' (Error loading)</option>';
                    issueHelper.textContent = 'Error loading issues. Please try again.';
                    issueHelper.style.color = '#dc3545';
                });
        }
    });
</script>

<style>
    .edit-inspection-content {
        padding: 2rem;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        color: #718096;
        font-size: 1rem;
    }

    .form-container {
        background: white;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        max-width: 800px;
    }

    .inspection-form {
        width: 100%;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .form-control:disabled {
        background-color: #f7fafc;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-control.is-invalid {
        border-color: #fc8181;
        background-color: #fff5f5;
    }

    .form-control.is-invalid:focus {
        border-color: #fc8181;
        box-shadow: 0 0 0 3px rgba(252, 129, 129, 0.1);
    }

    .invalid-feedback {
        display: block;
        color: #e53e3e;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    .form-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #718096;
    }

    label span {
        color: #e53e3e;
    }

    #issue_id option {
        padding: 0.5rem;
    }

    #issue-loader {
        color: #4299e1;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e2e8f0;
    }

    .btn {
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #2d3748;
    }

    .btn-secondary:hover {
        background: #cbd5e0;
        transform: translateY(-1px);
    }
</style>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>