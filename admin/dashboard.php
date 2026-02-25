<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

// fetch active services
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1");
$services = $stmt->fetchAll();
?>

<?php include "../includes/header.php"; ?>

<div class="container" style="max-width: 1200px; margin-top: 40px;">

    <div style="margin-bottom: 40px;">
        <h1 class="page-title">Admin Dashboard</h1>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 30px;">
            Welcome, <strong><?= $_SESSION['username']; ?></strong> | Smart Queue Management System
        </p>
    </div>

    <!-- QUICK STATS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <?php 
        $overall_stats = getQueueStats($pdo);
        ?>
        <div class="stat-card">
            <h5>Total Waiting</h5>
            <div class="stat-number"><?= $overall_stats['waiting']; ?></div>
        </div>
        <div class="stat-card">
            <h5>Currently Serving</h5>
            <div class="stat-number"><?= $overall_stats['serving']; ?></div>
        </div>
        <div class="stat-card">
            <h5>Completed Today</h5>
            <div class="stat-number"><?= $overall_stats['completed']; ?></div>
        </div>
    </div>

    <!-- SERVICES GRID -->
    <div style="background: white; border-radius: 14px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); margin-bottom: 30px;">
        <h3 class="section-title">Manage Services</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
            <?php foreach ($services as $s): ?>
                <a href="view_queue.php?service_id=<?= $s['id']; ?>" 
                   class="service-card" 
                   style="background: linear-gradient(135deg, #f0f6ff 0%, #e1efff 100%); padding: 25px; text-align: center; text-decoration: none; color: #1a3a52;">
                    <div style="font-size: 28px; margin-bottom: 10px;">-</div>
                    <h5 style="font-weight: 600; margin-bottom: 10px;"><?= htmlspecialchars($s['service_name']); ?></h5>
                    <p style="font-size: 12px; color: #666; margin: 0;">View Queue</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div style="background: white; border-radius: 14px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08);">
        <h3 class="section-title">System Features</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="../customer/display.php" target="_blank" class="btn btn-info" style="padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                Display Screen
            </a>
            <a href="reports.php" class="btn btn-primary" style="padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                View Reports
            </a>
            <a href="../logout.php" class="btn btn-danger" style="padding: 15px; text-align: center; text-decoration: none; border-radius: 8px; font-weight: 600;">
                Logout
            </a>
        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
