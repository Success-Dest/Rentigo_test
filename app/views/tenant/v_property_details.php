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
                <?php if (isTenant()): ?>
                    <?php if ($property->status === 'available'): ?>
                        <!-- Reserve Property Button -->
                        <form method="POST" action="<?php echo URLROOT; ?>/tenantproperties/reserve/<?php echo $property->id; ?>"
                              onsubmit="return confirm('Are you sure you want to reserve this property? You will be notified to visit our office for viewing.');">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-calendar-check"></i> Reserve Property
                            </button>
                        </form>
                    <?php elseif ($property->status === 'reserved'): ?>
                        <!-- Book Property Button (after physical visit) -->
                        <button class="btn btn-success" type="button" onclick="openBookingModal(<?php echo $property->id; ?>)">
                            <i class="fas fa-file-signature"></i> Book Property
                        </button>
                        <p style="color: #92400e; margin-top: 0.8em;">
                            <i class="fas fa-info-circle"></i> This property is reserved. After visiting our office and viewing the property, you can proceed with booking.
                        </p>
                    <?php elseif ($property->status === 'occupied'): ?>
                        <p style="color: #dc2626; margin-top: 0.8em;">
                            <i class="fas fa-lock"></i> This property is currently occupied and not available for reservation.
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color: #6b7280; margin-top: 0.8em;">
                        <i class="fas fa-sign-in-alt"></i> Please <a href="<?php echo URLROOT; ?>/users/login">login as a tenant</a> to reserve this property.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="property-details-card" style="max-width:700px; margin:2rem auto 3rem auto;">
            <div class="alert alert-danger">Property details not found or no longer available.</div>
        </div>
    <?php endif; ?>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Book Property</h3>
            <button class="modal-close" onclick="closeBookingModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="<?php echo URLROOT; ?>/bookings/create/<?php echo $property->id ?? ''; ?>" id="bookingForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="move_in_date">Move-in Date <span style="color: red;">*</span></label>
                    <input type="date" class="form-control" id="move_in_date" name="move_in_date" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="move_out_date">Move-out Date <span style="color: red;">*</span></label>
                    <input type="date" class="form-control" id="move_out_date" name="move_out_date" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="notes">Additional Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                              placeholder="Any special requests or requirements..."></textarea>
                </div>
                <div class="alert alert-info" style="margin-top: 1rem;">
                    <i class="fas fa-info-circle"></i> You are booking this reserved property. The Property Manager will review your booking request.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-signature"></i> Submit Booking Request
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #1f2937;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
    }

    .modal-close:hover {
        color: #1f2937;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #374151;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>

<script>
    function openBookingModal(propertyId) {
        document.getElementById('bookingModal').classList.remove('hidden');
    }

    function closeBookingModal() {
        document.getElementById('bookingModal').classList.add('hidden');
    }

    // Validate move-out date is after move-in date
    document.getElementById('move_in_date')?.addEventListener('change', function() {
        const moveOutDate = document.getElementById('move_out_date');
        if (moveOutDate) {
            moveOutDate.min = this.value;
        }
    });
</script>

<?php require APPROOT . '/views/inc/tenant_footer.php'; ?>