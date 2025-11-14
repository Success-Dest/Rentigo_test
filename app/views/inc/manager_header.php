<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'Rentigo Manager'; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/manager/manager.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <span class="logo-text">Rentigo Manager</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>"
                            data-tooltip="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <div class="tooltip">Dashboard</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/properties"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'properties' ? 'active' : ''; ?>"
                            data-tooltip="Properties">
                            <i class="fas fa-home"></i>
                            <span class="nav-text">Properties</span>
                        </a>
                        <div class="tooltip">Properties</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/tenants"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'tenants' ? 'active' : ''; ?>"
                            data-tooltip="Tenants">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Tenants</span>
                        </a>
                        <div class="tooltip">Tenants</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/bookings"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'bookings' ? 'active' : ''; ?>"
                            data-tooltip="Bookings">
                            <i class="fas fa-calendar-check"></i>
                            <span class="nav-text">Bookings</span>
                        </a>
                        <div class="tooltip">Bookings</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/maintenance"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'maintenance' ? 'active' : ''; ?>"
                            data-tooltip="Maintenance">
                            <i class="fas fa-tools"></i>
                            <span class="nav-text">Maintenance</span>
                        </a>
                        <div class="tooltip">Maintenance</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/inspections"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'inspections' ? 'active' : ''; ?>"
                            data-tooltip="Inspections">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="nav-text">Inspections</span>
                        </a>
                        <div class="tooltip">Inspections</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/issues"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'issues' ? 'active' : ''; ?>"
                            data-tooltip="Issues">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="nav-text">Issues</span>
                        </a>
                        <div class="tooltip">Issues</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/leases"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'leases' ? 'active' : ''; ?>"
                            data-tooltip="Lease Agreements">
                            <i class="fas fa-file-contract"></i>
                            <span class="nav-text">Lease Agreements</span>
                        </a>
                        <div class="tooltip">Lease Agreements</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/providers"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'providers' ? 'active' : ''; ?>"
                            data-tooltip="Service Providers">
                            <i class="fas fa-handshake"></i>
                            <span class="nav-text">Service Providers</span>
                        </a>
                        <div class="tooltip">Service Providers</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/manager/notifications"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'notifications' ? 'active' : ''; ?>"
                            data-tooltip="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="nav-text">Notifications</span>
                            <?php
                            // Show notification badge if there are unread notifications
                            $unreadCount = $data['unread_notifications'] ?? 0;
                            if ($unreadCount > 0) {
                                echo '<span class="notification-badge">' . $unreadCount . '</span>';
                            }
                            ?>
                        </a>
                        <div class="tooltip">Notifications</div>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="header-title">Property Manager</h1>
                    <!-- Added search functionality from Header component -->
                    <!-- <div class="header-search">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="Search properties, tenants..." id="globalSearch">
                        </div>
                    </div> -->
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <button class="user-menu-toggle" id="userMenuToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'Manager'; ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="<?php echo URLROOT; ?>/users/profile" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <hr class="dropdown-divider">
                            <a href="<?php echo URLROOT; ?>/users/logout" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <style>
                    /* User Info - Name and Chevron side by side */
                    .user-info {
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    .user-name {
                        font-weight: 500;
                        color: #1f2937;
                        font-size: 0.938rem;
                        white-space: nowrap;
                    }

                    /* Chevron icon - next to name */
                    .user-info .fa-chevron-down {
                        font-size: 0.75rem;
                        color: #6b7280;
                        transition: transform 0.2s;
                    }
                </style>