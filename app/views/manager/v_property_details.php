<?php
// Make sure $property is available in this view.
// If you do not use extract($data) in your view loader, use $data['property'] everywhere.
if (!isset($property) && isset($data['property'])) {
    $property = $data['property'];
}
?>

<?php require APPROOT . '/views/inc/manager_header.php'; ?>

<style>
    .property-details-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        padding: 2rem 2.5rem 2rem 2.5rem;
        max-width: 800px;
        margin: 2rem auto 3rem auto;
    }

    .property-details-card h3,
    .property-details-card h4 {
        margin-top: 2rem;
        margin-bottom: 0.75rem;
        font-weight: 700;
        color: #22223b;
    }

    .property-details-card h3:first-child {
        margin-top: 0;
    }

    .property-details-card p,
    .property-details-card ul,
    .property-details-card label {
        font-size: 1.07rem;
        color: #4b5563;
        margin-bottom: 0.85rem;
        margin-top: 0;
    }

    .property-details-card ul {
        padding-left: 1.3rem;
        margin-bottom: 1.5rem;
    }

    .property-details-card li {
        margin-bottom: 0.5rem;
        line-height: 1.7;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 13px;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 600;
        margin-right: 10px;
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

    .status-badge.available,
    .status-badge.available {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #6366f1;
    }

    .status-badge.occupied {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #64748b;
    }

    .text-muted {
        color: #a0aec0;
        font-style: italic;
    }

    .property-images-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 1.5rem;
    }

    .property-images-gallery>div {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px;
        background: #f9fafb;
        transition: box-shadow .2s;
    }

    .property-images-gallery img {
        display: block;
        max-width: 140px;
        max-height: 110px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .back-to-properties {
        margin-bottom: 1.2em;
        display: inline-block;
    }

    hr {
        border: none;
        border-top: 1.5px solid #e5e7eb;
        margin: 2.4rem 0;
    }

    @media (max-width: 700px) {
        .property-details-card {
            padding: 1.1rem 0.7rem;
        }

        .property-images-gallery img {
            max-width: 70vw;
        }
    }
</style>

<div class="page-content">
    <div class="page-header">
        <a href="<?php echo URLROOT; ?>/managerproperties/index" class="btn btn-secondary back-to-properties">
            <i class="fas fa-arrow-left"></i> Back to My Properties
        </a>
        <h2 style="padding-bottom: 0.1em;">Property Details</h2>
        <p style="margin-bottom:2.2em;">View all assigned property details, images, and documents.</p>
    </div>

    <?php if (isset($property) && !empty($property)): ?>
        <div class="property-details-card">

            <h3>Location</h3>
            <p><?php echo nl2br(htmlspecialchars($property->address)); ?></p>

            <h4>Type</h4>
            <p><?php echo ucfirst(htmlspecialchars($property->property_type)); ?></p>

            <h4>Status</h4>
            <span class="status-badge <?php echo strtolower($property->status); ?>">
                <?php echo strtoupper($property->status); ?>
            </span>

            <h4>Approval Status</h4>
            <span class="status-badge <?php echo strtolower($property->approval_status); ?>">
                <?php echo strtoupper($property->approval_status); ?>
            </span>
            <?php if (!empty($property->approved_at)): ?>
                <p style="margin-top:0.6em;"><strong>Approved At:</strong> <?php echo htmlspecialchars($property->approved_at); ?></p>
            <?php endif; ?>

            <?php if (isset($property->rent)): ?>
                <h4>Rent</h4>
                <p>
                    <?php echo $property->rent > 0 ? '<b>Rs ' . number_format($property->rent) . '/month</b>' : '<span class="text-muted">N/A</span>'; ?>
                </p>
            <?php endif; ?>

            <?php if (isset($property->bedrooms)): ?>
                <h4>Bedrooms</h4>
                <p>
                    <?php
                    if (intval($property->bedrooms) == $property->bedrooms) {
                        echo intval($property->bedrooms);
                    } else {
                        echo $property->bedrooms;
                    }
                    ?>
                </p>
            <?php endif; ?>

            <?php if (isset($property->bathrooms)): ?>
                <h4>Bathrooms</h4>
                <p>
                    <?php
                    if (intval($property->bathrooms) == $property->bathrooms) {
                        echo intval($property->bathrooms);
                    } else {
                        echo $property->bathrooms;
                    }
                    ?>
                </p>
            <?php endif; ?>

            <?php if (isset($property->sqft)): ?>
                <h4>Sqft</h4>
                <p><?php echo htmlspecialchars($property->sqft); ?> sq ft</p>
            <?php endif; ?>

            <?php if (isset($property->description) && $property->description): ?>
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

            <!-- Property Documents -->
            <?php if (!empty($property->documents)): ?>
                <h4>Property Documents</h4>
                <ul>
                    <?php foreach ($property->documents as $doc): ?>
                        <li>
                            <a href="<?php echo $doc['url']; ?>" target="_blank" style="color:#2563eb;font-weight:500;">
                                <?php echo htmlspecialchars($doc['name']); ?>
                            </a>
                            <span style="color:#64748b;">(<?php echo strtoupper($doc['type']); ?>, <?php echo round($doc['size'] / 1024); ?> KB)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    <?php else: ?>
        <div class="property-details-card">
            <div class="alert alert-danger">Property details not found or access denied.</div>
        </div>
    <?php endif; ?>
</div>

<?php require APPROOT . '/views/inc/manager_footer.php'; ?>