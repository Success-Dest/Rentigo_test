<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'Rentigo Landlord'; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css//landlord/landlord.css">
    <!-- <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/components.css"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="landlord-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <span class="logo-text">Rentigo Landlord</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>"
                            data-tooltip="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                            <span class="tooltip">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/properties"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'properties' ? 'active' : ''; ?>"
                            data-tooltip="Properties">
                            <i class="fas fa-home"></i>
                            <span class="nav-text">Properties</span>
                            <span class="tooltip">Properties</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/bookings"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'bookings' ? 'active' : ''; ?>"
                            data-tooltip="Bookings">
                            <i class="fas fa-calendar-check"></i>
                            <span class="nav-text">Bookings</span>
                            <span class="tooltip">Bookings</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/add_property"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'add_property' ? 'active' : ''; ?>"
                            data-tooltip="Add Property">
                            <i class="fas fa-plus"></i>
                            <span class="nav-text">Add Property</span>
                            <span class="tooltip">Add Property</span>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/maintenance"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'maintenance' ? 'active' : ''; ?>"
                            data-tooltip="Maintenance">
                            <i class="fas fa-tools"></i>
                            <span class="nav-text">Maintenance</span>
                            <span class="tooltip">Maintenance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/inquiries"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'inquiries' ? 'active' : ''; ?>"
                            data-tooltip="Inquiries">
                            <i class="fas fa-comments"></i>
                            <span class="nav-text">Inquiries</span>
                            <span class="tooltip">Inquiries</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/payment_history"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'payment_history' ? 'active' : ''; ?>"
                            data-tooltip="Payment History">
                            <i class="fas fa-credit-card"></i>
                            <span class="nav-text">Payment History</span>
                            <span class="tooltip">Payment History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/feedback"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'feedback' ? 'active' : ''; ?>"
                            data-tooltip="Feedback">
                            <i class="fas fa-star"></i>
                            <span class="nav-text">Feedback</span>
                            <span class="tooltip">Feedback</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/landlord/notifications"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'notifications' ? 'active' : ''; ?>"
                            data-tooltip="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="nav-text">Notifications</span>
                            <span class="tooltip">Notifications</span>
                        </a>
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
                    <h1 class="page-title"><?php echo $data['title'] ?? 'Landlord Dashboard'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <button class="user-menu-toggle" id="userMenuToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'Landlord'; ?></span>
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