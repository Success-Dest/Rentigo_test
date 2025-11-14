<?php require APPROOT . '/views/inc/admin_header.php'; ?>

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

    .admin-actions {
        margin-top: 2.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .admin-actions .btn {
        min-width: 110px;
        font-size: 1.04rem;
        padding: 9px 18px;
        border-radius: 8px;
    }

    .form-select {
        min-width: 180px;
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
        <div class="header-content">
            <h2 style="padding-bottom: 0.1em;">Property Details</h2>
            <p style="margin-bottom:2.2em;">View and verify all property details, images, and documents before approval.</p>
        </div>
    </div>

    <?php flash('admin_property_message'); ?>

    <div class="property-details-card">

        <h3>Location</h3>
        <p><?php echo nl2br(htmlspecialchars($data['property']->address)); ?></p>

        <h4>Owner</h4>
        <p>
            <?php echo htmlspecialchars($data['property']->landlord_name ?? ''); ?><br>
            <span style="color:#2563eb;"><?php echo htmlspecialchars($data['property']->landlord_email ?? ''); ?></span>
        </p>

        <h4>Type</h4>
        <p><?php echo ucfirst(htmlspecialchars($data['property']->property_type)); ?></p>

        <h4>Status</h4>
        <span class="status-badge <?php echo strtolower($data['property']->status); ?>">
            <?php echo strtoupper($data['property']->status); ?>
        </span>

        <h4>Approval Status</h4>
        <span class="status-badge <?php echo strtolower($data['property']->approval_status); ?>">
            <?php echo strtoupper($data['property']->approval_status); ?>
        </span>
        <?php if (!empty($data['property']->approved_at)): ?>
            <p style="margin-top:0.6em;"><strong>Approved At:</strong> <?php echo htmlspecialchars($data['property']->approved_at); ?></p>
        <?php endif; ?>

        <h4>Manager</h4>
        <p>
            <?php
            if ($data['property']->manager_name) {
                echo htmlspecialchars($data['property']->manager_name);
            } else {
                echo '<span class="text-muted">Unassigned</span>';
            }
            ?>
        </p>

        <h4>Rent</h4>
        <p>
            <?php echo isset($data['property']->rent) && $data['property']->rent > 0 ? '<b>Rs ' . number_format($data['property']->rent) . '/month</b>' : '<span class="text-muted">N/A</span>'; ?>
        </p>

        <!-- Property Images Gallery -->
        <?php if (!empty($data['property']->images)): ?>
            <h4>Property Images</h4>
            <div class="property-images-gallery">
                <?php foreach ($data['property']->images as $img): ?>
                    <div>
                        <a href="<?php echo $img['url']; ?>" target="_blank">
                            <img src="<?php echo $img['url']; ?>" alt="Property Image">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Property Documents -->
        <?php if (!empty($data['property']->documents)): ?>
            <h4>Property Documents</h4>
            <ul>
                <?php foreach ($data['property']->documents as $doc): ?>
                    <li>
                        <a href="<?php echo $doc['url']; ?>" target="_blank" style="color:#2563eb;font-weight:500;">
                            <?php echo htmlspecialchars($doc['name']); ?>
                        </a>
                        <span style="color:#64748b;">(<?php echo strtoupper($doc['type']); ?>, <?php echo round($doc['size'] / 1024); ?> KB)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <hr>

        <!-- Admin actions -->
        <div class="admin-actions">
            <?php if ($data['property']->approval_status === 'pending'): ?>
                <form action="<?php echo URLROOT . '/adminproperties/approve/' . $data['property']->id; ?>" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this property?');">Approve</button>
                </form>
                <form action="<?php echo URLROOT . '/adminproperties/reject/' . $data['property']->id; ?>" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this property?');">Reject</button>
                </form>
            <?php elseif ($data['property']->approval_status === 'rejected'): ?>
                <form action="<?php echo URLROOT . '/adminproperties/approve/' . $data['property']->id; ?>" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Re-approve this property?');">Re-approve</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Assign manager form (only show if approved) -->
        <?php if ($data['property']->approval_status === 'approved'): ?>
            <div style="margin-top: 2.5rem;">
                <form action="<?php echo URLROOT . '/adminproperties/assign/' . $data['property']->id; ?>" method="post" style="display:flex;align-items:end;gap:15px;flex-wrap:wrap;">
                    <label for="manager_id" style="margin-bottom:0.4em;"><strong>Assign/Change Property Manager:</strong></label>
                    <select name="manager_id" id="manager_id" class="form-select" required>
                        <option value="">Select Manager</option>
                        <?php foreach ($data['managers'] as $manager): ?>
                            <option value="<?php echo $manager->id; ?>" <?php echo ($data['property']->manager_id == $manager->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($manager->name); ?> (<?php echo htmlspecialchars($manager->email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" style="margin-top: 0;">Assign</button>
                </form>
                <?php if ($data['property']->manager_id): ?>
                    <form action="<?php echo URLROOT . '/adminproperties/unassign/' . $data['property']->id; ?>" method="post" style="margin-top:14px;">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Unassign this property manager?');">Unassign Manager</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require APPROOT . '/views/inc/admin_footer.php'; ?>