<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'Rentigo Admin'; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/admin/admin.css">
    <!-- <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/components.css"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <span class="logo-text">Rentigo Admin</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'dashboard' ? 'active' : ''; ?>"
                            data-tooltip="Dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/properties"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'properties' ? 'active' : ''; ?>"
                            data-tooltip="Properties">
                            <i class="fas fa-home"></i>
                            <span class="nav-text">Properties</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/managers"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'managers' ? 'active' : ''; ?>"
                            data-tooltip="Property Managers">
                            <i class="fas fa-users"></i>
                            <span class="nav-text">Property Managers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/documents"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'documents' ? 'active' : ''; ?>"
                            data-tooltip="Documents">
                            <i class="fas fa-file-alt"></i>
                            <span class="nav-text">Documents</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/financials"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'financials' ? 'active' : ''; ?>"
                            data-tooltip="Financials">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-text">Financials</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/providers"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'providers' ? 'active' : ''; ?>"
                            data-tooltip="Service Providers">
                            <i class="fas fa-tools"></i>
                            <span class="nav-text">Service Providers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/inspections"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'inspections' ? 'active' : ''; ?>"
                            data-tooltip="Inspections">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="nav-text">Inspections</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/policies"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'policies' ? 'active' : ''; ?>"
                            data-tooltip="Policies">
                            <i class="fas fa-shield-alt"></i>
                            <span class="nav-text">Policies</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/admin/notifications"
                            class="nav-link <?php echo ($data['page'] ?? '') === 'notifications' ? 'active' : ''; ?>"
                            data-tooltip="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="nav-text">Notifications</span>
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
                    <h1 class="page-title"><?php echo $data['title'] ?? 'Admin Dashboard'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <button class="user-menu-toggle" id="userMenuToggle">
                            <div class="user-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
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