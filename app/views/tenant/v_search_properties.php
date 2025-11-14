<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<div class="page-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h2>Search Properties</h2>
            <p>Find your perfect rental property</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <label>Location</label>
                <input type="text" class="form-input" placeholder="Enter location" id="locationFilter">
            </div>
            <div class="filter-group">
                <label>Min Price</label>
                <input type="number" class="form-input" placeholder="0" id="minPriceFilter">
            </div>
            <div class="filter-group">
                <label>Max Price</label>
                <input type="number" class="form-input" placeholder="50,000" id="maxPriceFilter">
            </div>
            <div class="filter-group">
                <label>Property Type</label>
                <select class="form-select" id="typeFilter">
                    <option value="">All Types</option>
                    <option value="Apartment">Apartment</option>
                    <option value="House">House</option>
                    <option value="Studio">Studio</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn btn-primary" id="searchBtn">Search</button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="table-section">
        <div class="section-header">
            <h3>Available Properties</h3>
        </div>

        <div class="properties-grid" id="propertiesGrid">
            <?php if (!empty($data['properties'])): ?>
                <?php foreach ($data['properties'] as $property): ?>
                    <div class="property-card"
                        data-location="<?php echo strtolower(htmlspecialchars($property->address)); ?>"
                        data-type="<?php echo htmlspecialchars($property->property_type); ?>"
                        data-price="<?php echo intval($property->rent); ?>">
                        <div class="property-image">
                            <img src="<?php echo !empty($property->primary_image) ? $property->primary_image : URLROOT . '/img/property-placeholder.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($property->address); ?>">
                            <div class="property-status">
                                <?php if ($property->status === 'available'): ?>
                                    <span class="status-badge available">Available</span>
                                <?php else: ?>
                                    <span class="status-badge reserved">Reserved</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="property-content">
                            <div class="property-header">
                                <h4 class="property-title"><?php echo htmlspecialchars($property->address); ?></h4>
                                <span class="property-price">Rs <?php echo number_format($property->rent); ?>/mo</span>
                            </div>

                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($property->address); ?></span>
                                <span class="separator">•</span>
                                <i class="fas fa-home"></i>
                                <span><?php echo htmlspecialchars($property->property_type); ?></span>
                            </div>

                            <div class="property-features">
                                <span class="feature-tag"><?php echo intval($property->bedrooms); ?> Bedrooms</span>
                                <span class="feature-tag"><?php echo intval($property->bathrooms); ?> Bathroom<?php echo intval($property->bathrooms) > 1 ? 's' : ''; ?></span>
                                <?php if (!empty($property->parking) && $property->parking > 0): ?>
                                    <span class="feature-tag">Parking</span>
                                <?php endif; ?>
                                <?php if (!empty($property->description)): ?>
                                    <span class="feature-tag"><?php echo htmlspecialchars($property->description); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="property-actions">
                                <a class="btn-property-action"
                                    href="<?php echo URLROOT . '/tenantproperties/details/' . $property->id; ?>">
                                    View Details
                                </a>
                                <?php if ($property->status === 'available'): ?>
                                    <button class="btn-property-action" id="btn-reserve-property" onclick=" reserveProperty(<?php echo $property->id; ?>)">
                                        Reserve Property
                                    </button>
                                <?php else: ?>
                                    <button class="btn-property-action disabled" disabled>
                                        Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted" style="padding:2em;">No properties available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reservation Modal (your modal code here) -->
<div id="reservationModal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Reservation</h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be populated by JavaScript -->
        </div>
    </div>
</div>

<style>
    #btn-reserve-property {
        margin-top: 15px;
    }
</style>

<!-- JavaScript for client-side filtering -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchBtn = document.getElementById('searchBtn');
        const locationFilter = document.getElementById('locationFilter');
        const minPriceFilter = document.getElementById('minPriceFilter');
        const maxPriceFilter = document.getElementById('maxPriceFilter');
        const typeFilter = document.getElementById('typeFilter');
        const propertiesGrid = document.getElementById('propertiesGrid');
        const propertyCards = propertiesGrid.getElementsByClassName('property-card');

        function filterProperties() {
            const locationVal = locationFilter.value.toLowerCase();
            const minPriceVal = parseInt(minPriceFilter.value) || 0;
            const maxPriceVal = parseInt(maxPriceFilter.value) || Number.MAX_SAFE_INTEGER;
            const typeVal = typeFilter.value.toLowerCase();

            let anyVisible = false;

            for (let i = 0; i < propertyCards.length; i++) {
                const card = propertyCards[i];
                const address = card.getAttribute('data-location');
                const type = card.getAttribute('data-type').toLowerCase();
                const price = parseInt(card.getAttribute('data-price'));

                const matchesLocation = !locationVal || address.includes(locationVal);
                const matchesType = !typeVal || type === typeVal;
                const matchesPrice = price >= minPriceVal && price <= maxPriceVal;

                if (matchesLocation && matchesType && matchesPrice) {
                    card.style.display = '';
                    anyVisible = true;
                } else {
                    card.style.display = 'none';
                }
            }

            // Optional: Show a message if no cards are visible
            const noResultsMsg = document.getElementById('noResultsMsg');

            if (!anyVisible) {
                if (!noResultsMsg) {
                    const msg = document.createElement('div');
                    msg.id = 'noResultsMsg';
                    msg.className = 'text-muted';
                    msg.style.padding = '2em';
                    msg.textContent = 'No properties match your search.';
                    propertiesGrid.appendChild(msg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }

        searchBtn.addEventListener('click', filterProperties);

        // Optionally enable filtering on pressing Enter in any input
        [locationFilter, minPriceFilter, maxPriceFilter].forEach(function(input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    filterProperties();
                }
            });
        });
    });

    // Dummy reservation modal logic for demonstration
    function reserveProperty(id) {
        document.getElementById('reservationModal').classList.remove('hidden');
        document.getElementById('modalBody').innerHTML = 'Reservation for property #' + id + ' coming soon!';
    }

    function closeModal() {
        document.getElementById('reservationModal').classList.add('hidden');
    }
</script><?php require APPROOT . '/views/inc/tenant_footer.php'; ?>