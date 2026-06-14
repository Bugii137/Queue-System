<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NAME') ? SITE_NAME : 'SDQMS'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/queue-system/assets/css/style.css">
</head>
<body>
<?php if (strpos($_SERVER['PHP_SELF'], 'admin/login.php') === false): ?>

<!-- NAVBAR -->
<nav class="sdqms-nav">
    <div class="nav-inner">

        <!-- LOGO -->
        <a href="/Queue-System/index.php" class="nav-logo">
            <span class="logo-icon">Q</span>
            <span class="logo-text">SDQMS</span>
        </a>

        <!-- HAMBURGER (mobile) -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <!-- LINKS -->
        <div class="nav-links" id="navLinks">

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- ADMIN NAV -->
                <a href="/Queue-System/admin/dashboard.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) ? 'active' : ''; ?>">Dashboard</a>
                <a href="/Queue-System/customer/display.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'display') !== false) ? 'active' : ''; ?>" target="_blank">Display Screen</a>
                <a href="/Queue-System/admin/reports.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'reports') !== false) ? 'active' : ''; ?>">Reports</a>
                <div class="nav-divider"></div>
                <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <a href="/Queue-System/logout.php" class="nav-btn nav-btn-danger">Logout</a>

            <?php else: ?>
                <!-- CUSTOMER NAV -->
                <a href="/Queue-System/customer/index.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'customer/index') !== false) ? 'active' : ''; ?>">Home</a>
                <a href="/Queue-System/customer/join_queue.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'join_queue') !== false) ? 'active' : ''; ?>">Get Ticket</a>
                <a href="/Queue-System/customer/status.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'status') !== false) ? 'active' : ''; ?>">Track Ticket</a>
                <a href="/Queue-System/customer/display.php" class="nav-link <?= (strpos($_SERVER['PHP_SELF'], 'display') !== false) ? 'active' : ''; ?>">Queue Display</a>
                <div class="nav-divider"></div>
                <a href="/Queue-System/admin/login.php" class="nav-btn nav-btn-admin">Admin</a>

            <?php endif; ?>

        </div>
    </div>
</nav>
<?php endif; ?>

<main class="site-main">