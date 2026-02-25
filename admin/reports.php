<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$today = date('Y-m-d');

/* ===============================
   OVERALL STATS
================================ */

// total today
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM queue_tickets 
    WHERE DATE(created_at)=?
");
$stmt->execute([$today]);
$total_today = $stmt->fetchColumn();

function countByStatus($pdo, $status, $today) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM queue_tickets 
        WHERE status=? AND DATE(created_at)=?
    ");
    $stmt->execute([$status, $today]);
    return $stmt->fetchColumn();
}

$waiting = countByStatus($pdo,'waiting',$today);
$serving = countByStatus($pdo,'serving',$today);
$completed = countByStatus($pdo,'completed',$today);
$cancelled = countByStatus($pdo,'cancelled',$today);


/* ===============================
   PER SERVICE REPORT
================================ */

$stmt = $pdo->query("
    SELECT 
        s.service_name,
        COUNT(q.id) AS total,
        SUM(CASE WHEN q.status='completed' THEN 1 ELSE 0 END) AS completed,
        AVG(q.estimated_wait_time) AS avg_wait
    FROM services s
    LEFT JOIN queue_tickets q ON s.id = q.service_id
    GROUP BY s.id
");

$services = $stmt->fetchAll();
?>

<?php include "../includes/header.php"; ?>

<div class="container" style="max-width: 1200px; margin-top: 30px;">

    <h2 class="page-title">📊 System Reports</h2>

    <a href="dashboard.php" class="btn btn-secondary me-2 mb-4">← Back</a>

    <!-- STATS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 40px;">

        <div class="stat-card">
            <h5>Total Tickets</h5>
            <div class="stat-number"><?= $total_today ?></div>
        </div>

        <div class="stat-card">
            <h5 style="color: #ffc107;">⏳ Waiting</h5>
            <div class="stat-number"><?= $waiting ?></div>
        </div>

        <div class="stat-card">
            <h5 style="color: #28a745;">🟢 Serving</h5>
            <div class="stat-number"><?= $serving ?></div>
        </div>

        <div class="stat-card">
            <h5 style="color: #6c757d;">✓ Completed</h5>
            <div class="stat-number"><?= $completed ?></div>
        </div>

    </div>

    <!-- SERVICE-WISE REPORT -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 5px 18px rgba(0,0,0,0.08);">

        <h4 class="section-title">Service Breakdown</h4>

        <table class="queue-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Avg Wait</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($services as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['service_name']) ?></strong></td>
                    <td style="text-align: center;"><?= $s['total'] ?></td>
                    <td style="text-align: center;"><?= $s['completed'] ?></td>
                    <td style="text-align: center;"><?= round($s['avg_wait'] ?? 0, 1) ?> min</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>

<?php include "../includes/footer.php"; ?>
