<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="<?php echo URLROOT; ?>/maintenance/index" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">New Maintenance Request</h1>
                <p class="page-subtitle">Submit a maintenance request for your property</p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php flash('maintenance_message'); ?>

    <!-- New Maintenance Request Form -->
    <div class="form-container">
        <form action="<?php echo URLROOT; ?>/maintenance/create" method="POST" enctype="multipart/form-data" class="maintenance-form">

            <!-- Property Selection -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-building"></i> Property Information
                </h3>

                <div class="form-group">
                    <label for="property_id" class="form-label required">Select Property</label>
                    <select name="property_id" id="property_id" class="form-control <?php echo (!empty($data['property_err'])) ? 'is-invalid' : ''; ?>" required>
                        <option value="">-- Select a Property --</option>
                        <?php if (!empty($data['properties'])): ?>
                            <?php foreach ($data['properties'] as $property): ?>
                                <option value="<?php echo $property->id; ?>" <?php echo (isset($data['property_id']) && $data['property_id'] == $property->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($property->address); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['property_err']; ?></span>
                </div>
            </div>

            <!-- Request Details -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-file-alt"></i> Request Details
                </h3>

                <!-- Title -->
                <div class="form-group">
                    <label for="title" class="form-label required">Issue Title</label>
                    <input type="text"
                        name="title"
                        id="title"
                        class="form-control <?php echo (!empty($data['title_err'])) ? 'is-invalid' : ''; ?>"
                        placeholder="e.g., Water Leak in Bathroom, Broken AC Unit"
                        value="<?php echo $data['title']; ?>"
                        required>
                    <span class="invalid-feedback"><?php echo $data['title_err']; ?></span>
                </div>

                <!-- Category -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="category" class="form-label required">Category</label>
                        <select name="category" id="category" class="form-control <?php echo (!empty($data['category_err'])) ? 'is-invalid' : ''; ?>" required>
                            <option value="">-- Select Category --</option>
                            <option value="plumbing" <?php echo ($data['category'] == 'plumbing') ? 'selected' : ''; ?>>Plumbing</option>
                            <option value="electrical" <?php echo ($data['category'] == 'electrical') ? 'selected' : ''; ?>>Electrical</option>
                            <option value="hvac" <?php echo ($data['category'] == 'hvac') ? 'selected' : ''; ?>>HVAC (Heating/Cooling)</option>
                            <option value="appliance" <?php echo ($data['category'] == 'appliance') ? 'selected' : ''; ?>>Appliance</option>
                            <option value="structural" <?php echo ($data['category'] == 'structural') ? 'selected' : ''; ?>>Structural</option>
                            <option value="pest" <?php echo ($data['category'] == 'pest') ? 'selected' : ''; ?>>Pest Control</option>
                            <option value="other" <?php echo ($data['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <span class="invalid-feedback"><?php echo $data['category_err']; ?></span>
                    </div>

                    <!-- Priority -->
                    <div class="form-group">
                        <label for="priority" class="form-label required">Priority Level</label>
                        <select name="priority" id="priority" class="form-control <?php echo (!empty($data['priority_err'])) ? 'is-invalid' : ''; ?>" required>
                            <option value="">-- Select Priority --</option>
                            <option value="low" <?php echo (isset($data['priority']) && $data['priority'] == 'low') ? 'selected' : ''; ?>>Low - Can wait</option>
                            <option value="medium" <?php echo (isset($data['priority']) && $data['priority'] == 'medium') ? 'selected' : ''; ?>>Medium - Within a week</option>
                            <option value="high" <?php echo (isset($data['priority']) && $data['priority'] == 'high') ? 'selected' : ''; ?>>High - Within 2-3 days</option>
                            <option value="emergency" <?php echo (isset($data['priority']) && $data['priority'] == 'emergency') ? 'selected' : ''; ?>>Emergency - Immediate attention</option>
                        </select>
                        <span class="invalid-feedback"><?php echo isset($data['priority_err']) ? $data['priority_err'] : ''; ?></span>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description" class="form-label required">Detailed Description</label>
                    <textarea name="description"
                        id="description"
                        rows="6"
                        class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>"
                        placeholder="Please provide a detailed description of the issue, including location, symptoms, and any other relevant information..."
                        required><?php echo $data['description']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
                    <small class="form-text">Be as specific as possible to help resolve the issue quickly</small>
                </div>

                <!-- Photo Upload (Optional) -->
                <div class="form-group">
                    <label for="photos" class="form-label">
                        <i class="fas fa-camera"></i> Upload Photos (Optional)
                    </label>
                    <input type="file"
                        name="photos[]"
                        id="photos"
                        class="form-control-file"
                        accept="image/*"
                        multiple>
                    <small class="form-text">You can upload multiple photos (Max 5 images, 5MB each)</small>
                    <div id="photo-preview" class="photo-preview-container"></div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i> Additional Information (Optional)
                </h3>

                <div class="form-group">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes"
                        id="notes"
                        rows="3"
                        class="form-control"
                        placeholder="Any additional notes or special instructions..."><?php echo isset($data['notes']) ? $data['notes'] : ''; ?></textarea>
                    <small class="form-text">Include access instructions, tenant contact, or other relevant information</small>
                </div>

                <div class="form-group">
                    <label for="estimated_cost" class="form-label">Estimated Cost (LKR)</label>
                    <input type="number"
                        name="estimated_cost"
                        id="estimated_cost"
                        class="form-control"
                        step="0.01"
                        min="0"
                        placeholder="Optional: Enter estimated cost"
                        value="<?php echo isset($data['estimated_cost']) ? $data['estimated_cost'] : ''; ?>">
                    <small class="form-text">If you have an idea of the repair cost</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="<?php echo URLROOT; ?>/maintenance/index" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .page-content {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f3f4f6;
        color: #374151;
        text-decoration: none;
        transition: all 0.3s;
    }

    .back-button:hover {
        background-color: #e5e7eb;
        color: #1f2937;
    }

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        margin: 0.25rem 0 0 0;
    }

    .form-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: #3b82f6;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-label.required::after {
        content: '*';
        color: #ef4444;
        margin-left: 0.25rem;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 0.813rem;
        margin-top: 0.25rem;
    }

    .form-text {
        display: block;
        color: #6b7280;
        font-size: 0.813rem;
        margin-top: 0.25rem;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-control-file {
        display: block;
        width: 100%;
        padding: 0.5rem;
        border: 2px dashed #d1d5db;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-control-file:hover {
        border-color: #3b82f6;
        background-color: #f9fafb;
    }

    .photo-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .photo-preview {
        position: relative;
        aspect-ratio: 1;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #4b5563;
    }

    @media (max-width: 768px) {
        .page-content {
            padding: 1rem;
        }

        .form-container {
            padding: 1.5rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Photo preview functionality
    document.getElementById('photos').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('photo-preview');
        previewContainer.innerHTML = '';

        const files = Array.from(e.target.files);

        if (files.length > 5) {
            alert('Maximum 5 photos allowed');
            e.target.value = '';
            return;
        }

        files.forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File ${file.name} is too large. Maximum size is 5MB.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'photo-preview';
                div.innerHTML = `<img src="${event.target.result}" alt="Preview ${index + 1}">`;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Form validation
    document.querySelector('.maintenance-form').addEventListener('submit', function(e) {
        const property = document.getElementById('property_id').value;
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();

        if (!property || !title || !description) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }
    });

    // Priority color indicator
    document.getElementById('priority').addEventListener('change', function() {
        const colors = {
            'low': '#10b981',
            'medium': '#f59e0b',
            'high': '#ef4444',
            'urgent': '#dc2626'
        };
        this.style.borderLeftWidth = '4px';
        this.style.borderLeftColor = colors[this.value] || '#d1d5db';
    });
</script>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>