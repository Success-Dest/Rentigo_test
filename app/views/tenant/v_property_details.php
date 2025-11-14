<?php require APPROOT . '/views/inc/tenant_header.php'; ?>

<style>
    .property-details-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(30, 34, 90, 0.07);
        padding: 2.5rem 2.2rem;
        max-width: 880px;
        margin: 2.5rem auto 3.5rem auto;
        position: relative;
        overflow: hidden;
    }

    .property-details-card h3 {
        font-size: 2rem;
        margin: 0 0 0.85em 0;
        color: #232946;
        font-weight: 700;
    }

    .property-details-card h4 {
        font-size: 1.15rem;
        margin: 2.1em 0 0.3em 0;
        color: #22223b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .property-details-card p,
    .property-details-card ul {
        font-size: 1.09rem;
        color: #50587a;
        margin-bottom: 1.05em;
    }

    .property-details-card ul {
        padding-left: 1.5em;
        margin-bottom: 2em;
    }

    .status-badge {
        display: inline-block;
        background: #eef6ff;
        color: #1e40af;
        border-radius: 16px;
        padding: 0.28em 1.2em;
        font-size: 1em;
        margin-top: 0.3em;
        margin-bottom: 1em;
        border: 1px solid #93c5fd;
        font-weight: 600;
        letter-spacing: 0.03em;
    }

    .status-badge.available,
    .status-badge.available {
        background: #d1fae5;
        color: #047857;
        border-color: #34d399;
    }

    .status-badge.reserved,
    .status-badge.occupied {
        background: #fef3c7;
        color: #92400e;
        border-color: #fbbf24;
    }

    .property-features {
        margin: 1.2em 0 2.2em 0;
        display: flex;
        flex-wrap: wrap;
        gap: 1.2em;
    }

    .feature-tag {
        background: #f3f4f6;
        color: #374151;
        border-radius: 7px;
        padding: 0.32em 0.85em;
        font-size: 1em;
        margin-right: 0.25em;
        margin-bottom: 0.3em;
        border: 1px solid #e5e7eb;
    }

    .property-images-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 2.2rem;
        margin-top: 1.7rem;
    }

    .property-images-gallery>div {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px;
        transition: box-shadow .2s;
    }

    .property-images-gallery img {
        display: block;
        max-width: 160px;
        max-height: 120px;
        border-radius: 7px;
        box-shadow: 0 2px 8px rgba(30, 34, 90, 0.07);
    }

    .property-documents {
        margin-top: 1.6em;
    }

    .property-documents ul {
        padding-left: 1.5em;
    }

    .property-documents li {
        margin-bottom: 0.4em;
    }

    .action-buttons {
        margin-top: 2.7em;
        display: flex;
        gap: 1em;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        font-size: 1.04em;
        padding: 0.7em 2.2em;
        border-radius: 25px;
        font-weight: 600;
    }

    @media (max-width: 700px) {
        .property-details-card {
            padding: 1.1rem 0.7rem;
        }

        .property-images-gallery img {
            max-width: 70vw;
        }

        .action-buttons {
            flex-direction: column;
            gap: 0.7em;
        }
    }
</style>

<div class="page-content">
    <div class="page-header">
        <a href="<?php echo URLROOT; ?>/tenantproperties/index" class="btn btn-secondary back-to-properties" style="margin-bottom:1.3em;">
            <i class="fas fa-arrow-left"></i> Back to Properties
        </a>
        <h2 style="padding-bottom: 0.1em;">Property Details</h2>
        <p style="margin-bottom:2.2em;">View complete details of this property and contact the owner or manager.</p>
    </div>

    <?php
    // Make sure $property is available regardless of extraction method
    if (!isset($property) && isset($data['property'])) {
        $property = $data['property'];
    }
    ?>

    <?php if (isset($property) && !empty($property)): ?>
        <div class="property-details-card">

            <h3><?php echo htmlspecialchars($property->address); ?></h3>

            <div class="property-features">
                <span class="feature-tag"><?php echo ucfirst(htmlspecialchars($property->property_type)); ?></span>
                <span class="feature-tag"><?php echo intval($property->bedrooms); ?> Bedrooms</span>
                <span class="feature-tag"><?php echo intval($property->bathrooms); ?> Bathrooms</span>
                <?php if (isset($property->sqft) && $property->sqft): ?>
                    <span class="feature-tag"><?php echo htmlspecialchars($property->sqft); ?> sq ft</span>
                <?php endif; ?>
                <?php if (!empty($property->parking) && $property->parking > 0): ?>
                    <span class="feature-tag">Parking</span>
                <?php endif; ?>
            </div>

            <span class="status-badge <?php echo strtolower($property->status); ?>">
                <?php echo ucfirst($property->status); ?>
            </span>

            <h4>Rent</h4>
            <p>
                <?php echo $property->rent > 0 ? '<b>Rs ' . number_format($property->rent) . '/month</b>' : '<span class="text-muted">N/A</span>'; ?>
            </p>

            <?php if (!empty($property->description)): ?>
                <h4>Description</h4>
                <p><?php echo nl2br(htmlspecialchars($property->description)); ?></p>
            <?php endif; ?>

            <!-- Property Images Gallery -->
            <?php if (!empty($property->images)): ?>
                <h4>Property Images</h4>
                <div class="property-images-gallery">
                    <?php foreach ($property->images as $img): ?>
                        <div>
                            <a href="<?php echo $img['url']; ?>" target="_blank">
                                <img src="<?php echo $img['url']; ?>" alt="Property Image">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <button class="btn btn-outline-primary" type="button"
                    onclick="reserveProperty(<?php echo $property->id; ?>)">
                    <i class="fas fa-calendar-check"></i> Reserve Property
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="property-details-card" style="max-width:700px; margin:2rem auto 3rem auto;">
            <div class="alert alert-danger">Property details not found or no longer available.</div>
        </div>
    <?php endif; ?>
</div>

<!-- Reservation Modal (optional, if you want to reuse from previous) -->
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

<script>
    function reserveProperty(id) {
        document.getElementById('reservationModal').classList.remove('hidden');
        document.getElementById('modalBody').innerHTML = 'Reservation for property #' + id + ' coming soon!';
    }

    function closeModal() {
        document.getElementById('reservationModal').classList.add('hidden');
    }
</script>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>