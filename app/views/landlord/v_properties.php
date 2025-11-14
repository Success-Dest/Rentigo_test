<?php require APPROOT . '/views/inc/landlord_header.php'; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="header-left">
        <h1 class="page-title">My Properties</h1>
        <p class="page-subtitle">Manage your property listings</p>
    </div>
    <div class="header-actions">
        <a href="<?php echo URLROOT; ?>/properties/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Property
        </a>
    </div>
</div>

<!-- Filters -->
<div class="filters">
    <div class="filter-row">
        <div class="filter-group">
            <label class="form-label">Search Properties</label>
            <input type="text" class="form-control" placeholder="Search by address..." id="propertySearch">
        </div>

        <!-- NEW: Listing Type Filter -->
        <div class="filter-group">
            <label class="form-label">Listing Type</label>
            <select class="form-control" id="listingTypeFilter">
                <option value="">All Properties</option>
                <option value="rent">For Rent</option>
                <option value="maintenance">Maintenance Only</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="form-label">Status</label>
            <select class="form-control" id="statusFilter">
                <option value="">All Status</option>
                <option value="occupied">Occupied</option>
                <option value="vacant">Vacant</option>
                <option value="maintenance">Under Maintenance</option>
                <option value="maintenance_only">Maintenance Only</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="form-label">Property Type</label>
            <select class="form-control" id="typeFilter">
                <option value="">All Types</option>
                <option value="apartment">Apartment</option>
                <option value="house">House</option>
                <option value="condo">Condo</option>
                <option value="townhouse">Townhouse</option>
                <option value="commercial">Commercial</option>
                <option value="land">Land</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="filter-group">
            <button class="btn btn-secondary" onclick="resetFilters()">Reset Filters</button>
        </div>
    </div>
</div>

<!-- Properties Grid -->
<div class="property-grid">
    <?php if (!empty($data['properties']) && is_array($data['properties'])): ?>
        <?php foreach ($data['properties'] as $property): ?>
            <?php
            // Determine listing type
            $listingType = $property->listing_type ?? 'rent';
            $isMaintenanceProperty = ($listingType === 'maintenance');
            ?>

            <div class="property-card"
                data-status="<?php echo isset($property->status) ? htmlspecialchars($property->status) : ''; ?>"
                data-type="<?php echo isset($property->property_type) ? htmlspecialchars($property->property_type) : ''; ?>"
                data-listing-type="<?php echo htmlspecialchars($listingType); ?>">

                <!-- Property Image with Gallery -->
                <div class="property-image-container">
                    <?php if (isset($property->primary_image) && !empty($property->primary_image)): ?>
                        <img src="<?php echo htmlspecialchars($property->primary_image); ?>"
                            alt="Property Image"
                            class="property-image"
                            onclick="openImageGallery(<?php echo $property->id; ?>)">

                        <!-- Image Counter Badge -->
                        <?php if (isset($property->images) && count($property->images) > 1): ?>
                            <div class="image-counter">
                                <i class="fas fa-images"></i>
                                <?php echo count($property->images); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Image Navigation Dots -->
                        <?php if (isset($property->images) && count($property->images) > 1): ?>
                            <div class="image-dots">
                                <?php for ($i = 0; $i < min(count($property->images), 5); $i++): ?>
                                    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                        onclick="changePropertyImage(<?php echo $property->id; ?>, <?php echo $i; ?>)"></span>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="property-image-placeholder">
                            <i class="fas fa-home"></i>
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>

                    <!-- Listing Type Badge Overlay -->
                    <?php
                    $listingTypeBadgeClass = $isMaintenanceProperty ? 'badge-listing-maintenance' : 'badge-listing-rent';
                    $listingTypeIcon = $isMaintenanceProperty ? 'fa-tools' : 'fa-home';
                    $listingTypeText = $isMaintenanceProperty ? 'Maintenance Only' : 'For Rent';
                    ?>
                    <div class="listing-type-badge <?php echo $listingTypeBadgeClass; ?>">
                        <i class="fas <?php echo $listingTypeIcon; ?>"></i>
                        <?php echo $listingTypeText; ?>
                    </div>
                </div>

                <div class="property-info">
                    <!-- Property Address -->
                    <h3 class="property-title"><?php echo isset($property->address) ? htmlspecialchars($property->address) : 'No Address'; ?></h3>

                    <div class="property-details">
                        <!-- Type -->
                        <p><strong>Type:</strong> <?php echo isset($property->property_type) ? ucfirst(htmlspecialchars($property->property_type)) : '-'; ?></p>

                        <!-- Rent (only for rental properties) -->
                        <?php if (!$isMaintenanceProperty): ?>
                            <p><strong>Rent:</strong> Rs <?php echo isset($property->rent) ? number_format($property->rent) : '-'; ?>/month</p>
                        <?php endif; ?>

                        <!-- Current Occupant (for maintenance properties) -->
                        <?php if ($isMaintenanceProperty && !empty($property->current_occupant)): ?>
                            <p><strong>Occupant:</strong> <?php echo htmlspecialchars($property->current_occupant); ?></p>
                        <?php endif; ?>

                        <!-- Status -->
                        <p><strong>Status:</strong>
                            <?php
                            $statusClass = 'badge-secondary';
                            $statusValue = $property->status ?? 'unknown';

                            if ($statusValue === 'occupied') $statusClass = 'badge-success';
                            elseif ($statusValue === 'available') $statusClass = 'badge-warning';
                            elseif ($statusValue === 'maintenance') $statusClass = 'badge-danger';
                            elseif ($statusValue === 'maintenance_only') $statusClass = 'badge-info';
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $statusValue)); ?>
                            </span>
                        </p>

                        <!-- Approval Status Badge (PHASE 3) -->
                        <?php
                        $approval = strtolower($property->approval_status ?? 'pending');
                        $badgeClass = $approval === 'approved' ? 'approved' : ($approval === 'pending' ? 'pending' : 'rejected');
                        ?>
                        <p>
                            <strong>Approval:</strong>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <?php echo ucfirst($approval); ?>
                            </span>
                        </p>

                        <!-- Bedrooms & Bathrooms -->
                        <?php if (!$isMaintenanceProperty): ?>
                            <p><strong>Bedrooms:</strong> <?php echo $property->bedrooms ?? '-'; ?></p>
                        <?php endif; ?>

                        <?php if (!$isMaintenanceProperty): ?>
                            <p><strong>Bathrooms:</strong> <?php echo $property->bathrooms ?? '-'; ?></p>
                        <?php endif; ?>

                        <!-- Square Footage -->
                        <?php if (!empty($property->sqft)): ?>
                            <p><strong>Area:</strong> <?php echo number_format($property->sqft); ?> sq ft</p>
                        <?php endif; ?>

                        <!-- Available Date (only for rental) -->
                        <?php if (!$isMaintenanceProperty && !empty($property->available_date)): ?>
                            <p><strong>Available:</strong> <?php echo date('M d, Y', strtotime($property->available_date)); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Property Actions -->
                    <div class="property-actions">
                        <a href="<?php echo URLROOT; ?>/properties/edit/<?php echo $property->id; ?>"
                            class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>

                        <!-- View Images Button -->
                        <?php if (isset($property->images) && count($property->images) > 0): ?>
                            <button class="btn btn-outline btn-sm"
                                onclick="openImageGallery(<?php echo $property->id; ?>)">
                                <i class="fas fa-images"></i> Images (<?php echo count($property->images); ?>)
                            </button>
                        <?php endif; ?>

                        <!-- Delete button -->
                        <a href="<?php echo URLROOT; ?>/properties/delete/<?php echo $property->id; ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Are you sure you want to delete this property and all its images?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-properties">
            <div class="no-properties-icon">
                <i class="fas fa-home"></i>
            </div>
            <h3>No Properties Found</h3>
            <p>Add a new property to get started.</p>
            <a href="<?php echo URLROOT; ?>/properties/add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Your First Property
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Image Gallery Modal -->
<div id="imageGalleryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="galleryTitle">Property Images</h3>
            <span class="close" onclick="closeImageGallery()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="gallery-container">
                <div class="gallery-main">
                    <img id="galleryMainImage" src="" alt="Property Image">
                    <div class="gallery-nav">
                        <button class="gallery-nav-btn prev" onclick="previousImage()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="gallery-nav-btn next" onclick="nextImage()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="gallery-thumbnails" id="galleryThumbnails">
                    <!-- Thumbnails will be loaded here -->
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="image-info">
                <span id="imageCounter">1 of 1</span>
                <span id="imageName">Image name</span>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
    /* Property Image Styles */
    .property-image-container {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        border-radius: 0.75rem 0.75rem 0 0;
        cursor: pointer;
    }

    .property-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .property-image:hover {
        transform: scale(1.05);
    }

    .property-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 1rem;
    }

    .property-image-placeholder i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    /* Listing Type Badge Overlay */
    .listing-type-badge {
        position: absolute;
        top: 0.5rem;
        left: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.375rem;
        backdrop-filter: blur(8px);
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .badge-listing-rent {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95));
        color: white;
    }

    .badge-listing-maintenance {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.95), rgba(217, 119, 6, 0.95));
        color: white;
    }

    .listing-type-badge i {
        font-size: 0.875rem;
    }

    /* Image Counter Badge */
    .image-counter {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .image-dots {
        position: absolute;
        bottom: 0.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 0.25rem;
        z-index: 10;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .dot.active {
        background: white;
    }

    /* Status Badges */
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.813rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .badge-info {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }

    /* No Properties Styles */
    .no-properties {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .no-properties-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .no-properties-icon i {
        font-size: 2rem;
        color: white;
    }

    .no-properties h3 {
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .no-properties p {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background-color: white;
        margin: 2% auto;
        padding: 0;
        border-radius: 0.75rem;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        overflow: hidden;
        animation: slideIn 0.3s ease;
    }

    .modal-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #1f2937;
    }

    .close {
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        color: #6b7280;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .close:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .modal-body {
        padding: 1rem;
    }

    .gallery-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .gallery-main {
        position: relative;
        width: 100%;
        height: 500px;
        background: #f3f4f6;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .gallery-nav {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        display: flex;
        justify-content: space-between;
        padding: 0 1rem;
        transform: translateY(-50%);
    }

    .gallery-nav-btn {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        transition: all 0.2s ease;
    }

    .gallery-nav-btn:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(1.1);
    }

    .gallery-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .gallery-nav-btn:disabled:hover {
        transform: none;
    }

    .gallery-thumbnails {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.5rem 0;
    }

    .gallery-thumbnail {
        width: 80px;
        height: 60px;
        border-radius: 0.25rem;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .gallery-thumbnail.active {
        border-color: #3b82f6;
    }

    .gallery-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-thumbnail:hover {
        transform: scale(1.05);
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .image-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .status-badge {
        display: inline-block;
        padding: 1px 10px;
        border-radius: 14px;
        font-size: 0.98rem;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 2px;
        letter-spacing: 0.04em;
    }

    .status-badge.approved {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            margin: 5% auto;
            width: 95%;
        }

        .gallery-main {
            height: 300px;
        }

        .gallery-nav-btn {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .gallery-thumbnails {
            flex-wrap: wrap;
        }

        .gallery-thumbnail {
            width: 60px;
            height: 45px;
        }

        .listing-type-badge {
            font-size: 0.688rem;
            padding: 0.25rem 0.5rem;
        }

        .listing-type-badge i {
            font-size: 0.75rem;
        }

        .image-counter {
            font-size: 0.688rem;
            padding: 0.188rem 0.375rem;
        }
    }
</style>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filter functionality
        initFilter('#statusFilter', '.property-card', null, 'data-status');
        initFilter('#typeFilter', '.property-card', null, 'data-type');
        initFilter('#listingTypeFilter', '.property-card', null, 'data-listing-type');

        // Search functionality
        const searchInput = document.getElementById('propertySearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const propertyCards = document.querySelectorAll('.property-card');

                propertyCards.forEach(card => {
                    const title = card.querySelector('.property-title').textContent.toLowerCase();
                    const details = card.querySelector('.property-details').textContent.toLowerCase();

                    if (title.includes(searchTerm) || details.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });

    // Image Gallery Variables
    let currentPropertyId = null;
    let currentImageIndex = 0;
    let propertyImages = [];

    // Open Image Gallery
    function openImageGallery(propertyId) {
        currentPropertyId = propertyId;

        // Get property images via AJAX
        fetch(`${URLROOT}/properties/getImagesJson/${propertyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.images.length > 0) {
                    propertyImages = data.images;
                    currentImageIndex = 0;

                    document.getElementById('galleryTitle').textContent = `Property Images (${data.images.length})`;
                    loadGalleryImages();
                    showImage(0);
                    document.getElementById('imageGalleryModal').style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Prevent background scroll
                } else {
                    showNotification('No images found for this property', 'info');
                }
            })
            .catch(error => {
                console.error('Error loading images:', error);
                showNotification('Error loading images', 'error');
            });
    }

    // Close Image Gallery
    function closeImageGallery() {
        document.getElementById('imageGalleryModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scroll
    }

    // Load Gallery Images
    function loadGalleryImages() {
        const thumbnailContainer = document.getElementById('galleryThumbnails');
        thumbnailContainer.innerHTML = '';

        propertyImages.forEach((image, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = `gallery-thumbnail ${index === 0 ? 'active' : ''}`;
            thumbnail.onclick = () => showImage(index);

            const img = document.createElement('img');
            img.src = image.url;
            img.alt = image.name;

            thumbnail.appendChild(img);
            thumbnailContainer.appendChild(thumbnail);
        });
    }

    // Show Specific Image
    function showImage(index) {
        if (index < 0 || index >= propertyImages.length) return;

        currentImageIndex = index;
        const image = propertyImages[index];

        document.getElementById('galleryMainImage').src = image.url;
        document.getElementById('imageCounter').textContent = `${index + 1} of ${propertyImages.length}`;
        document.getElementById('imageName').textContent = image.name;

        // Update active thumbnail
        document.querySelectorAll('.gallery-thumbnail').forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });

        // Update navigation buttons
        document.querySelector('.gallery-nav-btn.prev').disabled = index === 0;
        document.querySelector('.gallery-nav-btn.next').disabled = index === propertyImages.length - 1;
    }

    // Previous Image
    function previousImage() {
        if (currentImageIndex > 0) {
            showImage(currentImageIndex - 1);
        }
    }

    // Next Image
    function nextImage() {
        if (currentImageIndex < propertyImages.length - 1) {
            showImage(currentImageIndex + 1);
        }
    }

    // Change Property Image (for property card image cycling)
    function changePropertyImage(propertyId, imageIndex) {
        // Open the gallery at specified image
        openImageGallery(propertyId);
    }

    // Reset Filters
    function resetFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('listingTypeFilter').value = '';
        document.getElementById('propertySearch').value = '';

        document.querySelectorAll('.property-card').forEach(card => {
            card.style.display = 'block';
        });

        showNotification('Filters reset', 'info');
    }

    // Filter Helper Function
    function initFilter(selectId, cardClass, badgeClass = null, dataAttr = null) {
        const filterSelect = document.querySelector(selectId);
        if (filterSelect) {
            filterSelect.addEventListener('change', function() {
                const selectedValue = this.value.toLowerCase();
                const cards = document.querySelectorAll(cardClass);

                cards.forEach(card => {
                    if (selectedValue === '') {
                        card.style.display = 'block';
                    } else {
                        let cardValue;
                        if (dataAttr) {
                            cardValue = card.getAttribute(dataAttr);
                        } else if (badgeClass) {
                            const badge = card.querySelector(badgeClass);
                            cardValue = badge ? badge.textContent.toLowerCase() : '';
                        }

                        if (cardValue && cardValue.toLowerCase() === selectedValue) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        }
    }

    // Notification Function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '1rem 1.5rem',
            borderRadius: '0.5rem',
            color: 'white',
            fontWeight: '500',
            zIndex: '10000',
            opacity: '0',
            transform: 'translateY(-20px)',
            transition: 'all 0.3s ease'
        });

        const colors = {
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#3b82f6'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 100);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('imageGalleryModal');
        if (event.target === modal) {
            closeImageGallery();
        }
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('imageGalleryModal');
        if (modal.style.display === 'block') {
            switch (event.key) {
                case 'Escape':
                    closeImageGallery();
                    break;
                case 'ArrowLeft':
                    previousImage();
                    break;
                case 'ArrowRight':
                    nextImage();
                    break;
            }
        }
    });

    // URL constants
    const URLROOT = '<?php echo URLROOT; ?>';
</script>

<?php require APPROOT . '/views/inc/landlord_footer.php'; ?>