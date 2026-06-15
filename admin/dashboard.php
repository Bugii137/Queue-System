<?php
include "../config.php";
include "../includes/auth.php";
requireLogin();
requireAdmin();

$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1");
$services = $stmt->fetchAll();
$overall_stats = getQueueStats($pdo);

// Service icons map
$icons = [
    'TLR' => '',
    'ACC' => '',
    'LNS' => '',
    'CST' => '',
    'FEX' => '',
    'CRD' => '',
];

// Per-service waiting count
$svc_counts = [];
foreach ($services as $s) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM queue_tickets WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()");
    $st->execute([$s['id']]);
    $svc_counts[$s['id']] = $st->fetchColumn();
}

include "../includes/header.php";
?>

<div class="container" style="max-width:1200px; margin:30px auto; padding:0 20px;">

    <!-- PAGE HEADER -->
    <div style="margin-bottom:30px; text-align:center;">
        <h1 class="page-title" style="margin-bottom:6px;">Admin Dashboard</h1>
        <p style="color:#666; font-size:14px;">
            Welcome, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>
            &nbsp;|&nbsp; <?= date('l, d F Y'); ?>
        </p>
    </div>

    <!-- STAT CARDS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:30px;">
        <div class="stat-card">
            <h5>Total Waiting</h5>
            <div class="stat-number"><?= $overall_stats['waiting']; ?></div>
        </div>
        <div class="stat-card" style="border-color:#28a745;">
            <h5>Currently Serving</h5>
            <div class="stat-number" style="color:#28a745;"><?= $overall_stats['serving']; ?></div>
        </div>
        <div class="stat-card" style="border-color:#6c757d;">
            <h5>Completed Today</h5>
            <div class="stat-number" style="color:#6c757d;"><?= $overall_stats['completed']; ?></div>
        </div>
        <div class="stat-card" style="border-color:#ffc107;">
            <h5>Total Today</h5>
            <div class="stat-number" style="color:#ffc107;"><?= $overall_stats['total']; ?></div>
        </div>
    </div>

    <!-- SERVICES GRID -->
    <div style="background:white; border-radius:14px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,0.07); margin-bottom:24px;">
        <h3 class="section-title">Manage Services</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px;">
            <?php foreach ($services as $s):
                $icon    = $icons[$s['service_code']] ?? '';
                $waiting = $svc_counts[$s['id']];
            ?>
            <a href="view_queue.php?service_id=<?= $s['id']; ?>" class="service-card"
               style="background:linear-gradient(135deg,#f0f6ff,#e1efff); text-align:center; padding:24px 16px;">
                <div style="font-size:32px; margin-bottom:10px;"><?= $icon; ?></div>
                <div style="font-weight:700; color:#1a3a52; margin-bottom:6px; font-size:14px;">
                    <?= htmlspecialchars($s['service_name']); ?>
                </div>
                <?php if ($waiting > 0): ?>
                    <span style="background:#ffc107; color:#333; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                        <?= $waiting; ?> waiting
                    </span>
                <?php else: ?>
                    <span style="color:#999; font-size:12px;">No queue</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div style="background:white; border-radius:14px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,0.07);">
        <h3 class="section-title">Quick Actions</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px;">
            <a href="../customer/display.php" target="_blank"
               style="display:flex; align-items:center; gap:12px; padding:16px; background:#f0f6ff; border-radius:10px; text-decoration:none; color:#0d6efd; font-weight:600; border:1px solid #d0e4ff;">
                Display Screen
            </a>
            <a href="reports.php"
               style="display:flex; align-items:center; gap:12px; padding:16px; background:#f0fff4; border-radius:10px; text-decoration:none; color:#28a745; font-weight:600; border:1px solid #c3e6cb;">
                View Reports
            </a>
            <a href="../customer/join_queue.php"
               style="display:flex; align-items:center; gap:12px; padding:16px; background:#fff8f0; border-radius:10px; text-decoration:none; color:#fd7e14; font-weight:600; border:1px solid #ffd8b1;">
                Issue Ticket
            </a>
        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>