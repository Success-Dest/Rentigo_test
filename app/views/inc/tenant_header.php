<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'TenantHub'; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/tenant/tenant.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="tenant-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-home"></i>
                    <span class="logo-text">Rentigo Tenant
                    </span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>"
                            data-tooltip="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <div class="tooltip">Dashboard</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/search_properties"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'search_properties' ? 'active' : ''; ?>"
                            data-tooltip="Search Properties">
                            <i class="fas fa-search"></i>
                            <span class="nav-text">Search Properties</span>
                        </a>
                        <div class="tooltip">Search Properties</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/bookings"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'bookings' ? 'active' : ''; ?>"
                            data-tooltip="My Bookings">
                            <i class="fas fa-calendar"></i>
                            <span class="nav-text">My Bookings</span>
                        </a>
                        <div class="tooltip">My Bookings</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/pay_rent"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'pay_rent' ? 'active' : ''; ?>"
                            data-tooltip="Pay Rent">
                            <i class="fas fa-credit-card"></i>
                            <span class="nav-text">Pay Rent</span>
                        </a>
                        <div class="tooltip">Pay Rent</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/agreements"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'agreements' ? 'active' : ''; ?>"
                            data-tooltip="Agreements">
                            <i class="fas fa-file-contract"></i>
                            <span class="nav-text">Agreements</span>
                        </a>
                        <div class="tooltip">Agreements</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/report_issue"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'report_issue' ? 'active' : ''; ?>"
                            data-tooltip="Report Issues">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="nav-text">Report Issues</span>
                        </a>
                        <div class="tooltip">Report Issues</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/track_issues"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'track_issues' ? 'active' : ''; ?>"
                            data-tooltip="Track Issues">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-text">Track Issues</span>
                        </a>
                        <div class="tooltip">Track Issues</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/my_reviews"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'my_reviews' ? 'active' : ''; ?>"
                            data-tooltip="My Reviews">
                            <i class="fas fa-star"></i>
                            <span class="nav-text">My Reviews</span>
                        </a>
                        <div class="tooltip">My Reviews</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/notifications"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'notifications' ? 'active' : ''; ?>"
                            data-tooltip="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="nav-text">Notifications</span>
                            <?php
                            // Show notification badge if there are unread notifications
                            $unreadCount = $data['unread_notifications'] ?? 0;
                            if ($unreadCount > 0) {
                                echo '<span class="notification-badge">' . min($unreadCount, 99) . '</span>';
                            }
                            ?>
                        </a>
                        <div class="tooltip">Notifications</div>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/feedback"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'feedback' ? 'active' : ''; ?>"
                            data-tooltip="Feedback">
                            <i class="fas fa-comment"></i>
                            <span class="nav-text">Feedback</span>
                        </a>
                        <div class="tooltip">Feedback</div>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/tenant/settings"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'settings' ? 'active' : ''; ?>"
                            data-tooltip="Settings">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">Settings</span>
                        </a>
                        <div class="tooltip">Settings</div>
                    </li> -->
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
                    <h1 class="page-title"><?php echo $data['title'] ?? 'Tenant Dashboard'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <button class="user-menu-toggle" id="userMenuToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'Tenant'; ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </button>
                        <div class="user-dropdown">
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