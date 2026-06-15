<?php
include "../config.php";
include "../includes/auth.php";
requireLogin();
requireAdmin();

$today = date('Y-m-d');

// Overall stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM queue_tickets WHERE DATE(created_at)=?");
$stmt->execute([$today]); $total_today = $stmt->fetchColumn();

function countByStatus($pdo, $status, $today) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM queue_tickets WHERE status=? AND DATE(created_at)=?");
    $stmt->execute([$status, $today]);
    return $stmt->fetchColumn();
}

$waiting   = countByStatus($pdo,'waiting',$today);
$serving   = countByStatus($pdo,'serving',$today);
$completed = countByStatus($pdo,'completed',$today);
$cancelled = countByStatus($pdo,'cancelled',$today);

// Per-service breakdown
$services = $pdo->query("
    SELECT
        s.service_name,
        s.service_code,
        COUNT(q.id) AS total,
        SUM(CASE WHEN q.status='completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN q.status='waiting'   THEN 1 ELSE 0 END) AS waiting,
        SUM(CASE WHEN q.status='serving'   THEN 1 ELSE 0 END) AS serving,
        ROUND(AVG(q.estimated_wait_time),1) AS avg_wait
    FROM services s
    LEFT JOIN queue_tickets q ON s.id = q.service_id AND DATE(q.created_at) = CURDATE()
    GROUP BY s.id
    ORDER BY total DESC
")->fetchAll();

// Peak hours
$peak = $pdo->query("
    SELECT HOUR(created_at) AS hour, COUNT(*) AS count
    FROM queue_tickets
    WHERE DATE(created_at) = CURDATE()
    GROUP BY HOUR(created_at)
    ORDER BY hour ASC
")->fetchAll(PDO::FETCH_ASSOC);

$max_count = max(array_column($peak, 'count') ?: [1]);

include "../includes/header.php";
?>

<div class="container" style="max-width:1200px; margin:30px auto; padding:0 20px;">

    <h1 class="page-title">System Reports</h1>
    <p style="text-align:center; color:#666; margin-top:-20px; margin-bottom:28px; font-size:14px;">
        <?= date('l, d F Y'); ?> — Live data
    </p>

    <!-- STAT CARDS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:30px;">
        <div class="stat-card">
            <h5>Total Tickets</h5>
            <div class="stat-number"><?= $total_today; ?></div>
        </div>
        <div class="stat-card" style="border-color:#ffc107;">
            <h5>Waiting</h5>
            <div class="stat-number" style="color:#ffc107;"><?= $waiting; ?></div>
        </div>
        <div class="stat-card" style="border-color:#28a745;">
            <h5>Serving</h5>
            <div class="stat-number" style="color:#28a745;"><?= $serving; ?></div>
        </div>
        <div class="stat-card" style="border-color:#6c757d;">
            <h5>Completed</h5>
            <div class="stat-number" style="color:#6c757d;"><?= $completed; ?></div>
        </div>
        <div class="stat-card" style="border-color:#dc3545;">
            <h5>Cancelled</h5>
            <div class="stat-number" style="color:#dc3545;"><?= $cancelled; ?></div>
        </div>
    </div>

    <!-- SERVICE BREAKDOWN WITH VISUAL BARS -->
    <div style="background:white; border-radius:14px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,0.07); margin-bottom:24px;">
        <h4 class="section-title">Service Breakdown</h4>

        <div style="display:grid; gap:16px;">
            <?php foreach ($services as $s):
                $pct = $total_today > 0 ? round(($s['total'] / $total_today) * 100) : 0;
            ?>
            <div style="background:#f8f9fa; border-radius:10px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                    <strong style="color:#1a3a52; font-size:14px;"><?= htmlspecialchars($s['service_name']); ?></strong>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <span style="background:#e8f0fe; color:#0d6efd; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                            <?= $s['total']; ?> total
                        </span>
                        <span style="background:#d4edda; color:#155724; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                            <?= $s['completed']; ?> done
                        </span>
                        <span style="background:#fff3cd; color:#856404; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                            <?= $s['waiting']; ?> waiting
                        </span>
                        <span style="background:#f8f9fa; color:#666; padding:3px 10px; border-radius:999px; font-size:12px; border:1px solid #dee2e6;">
                            ~<?= $s['avg_wait'] ?? 0; ?> min avg wait
                        </span>
                    </div>
                </div>
                <!-- Progress bar -->
                <div style="background:#e9ecef; border-radius:999px; height:10px; overflow:hidden;">
                    <div style="height:100%; width:<?= $pct; ?>%; background:linear-gradient(90deg,#0d6efd,#0a58ca); border-radius:999px; transition:width 0.5s ease;"></div>
                </div>
                <div style="font-size:11px; color:#999; margin-top:4px; text-align:right;"><?= $pct; ?>% of today's tickets</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PEAK HOURS CHART -->
    <div style="background:white; border-radius:14px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,0.07); margin-bottom:24px;">
        <h4 class="section-title">Peak Hours Today</h4>

        <?php if ($peak): ?>
        <div style="display:flex; align-items:flex-end; gap:8px; height:160px; padding:10px 0;">
            <?php for ($h = 8; $h <= 17; $h++):
                $count = 0;
                foreach ($peak as $p) { if ((int)$p['hour'] === $h) { $count = $p['count']; break; } }
                $bar_h = $max_count > 0 ? round(($count / $max_count) * 120) : 0;
                $is_peak = $count === $max_count && $count > 0;
            ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                <span style="font-size:11px; color:#666; font-weight:<?= $is_peak ? '700' : '400'; ?>;">
                    <?= $count > 0 ? $count : ''; ?>
                </span>
                <div style="width:100%; height:<?= $bar_h; ?>px; background:<?= $is_peak ? '#0d6efd' : '#c5d9f8'; ?>; border-radius:4px 4px 0 0; transition:height 0.3s;"></div>
                <span style="font-size:10px; color:#999;"><?= $h; ?>:00</span>
            </div>
            <?php endfor; ?>
        </div>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#999;">No ticket data for today yet.</div>
        <?php endif; ?>
    </div>

    <!-- DETAILED TABLE -->
    <div style="background:white; border-radius:14px; padding:28px; box-shadow:0 4px 16px rgba(0,0,0,0.07);">
        <h4 class="section-title">Detailed Service Table</h4>
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">Waiting</th>
                    <th style="text-align:center;">Serving</th>
                    <th style="text-align:center;">Completed</th>
                    <th style="text-align:center;">Avg Wait</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['service_name']); ?></strong></td>
                    <td style="text-align:center;"><?= $s['total']; ?></td>
                    <td style="text-align:center;"><?= $s['waiting']; ?></td>
                    <td style="text-align:center;"><?= $s['serving']; ?></td>
                    <td style="text-align:center;"><?= $s['completed']; ?></td>
                    <td style="text-align:center;"><?= $s['avg_wait'] ?? 0; ?> min</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include "../includes/footer.php"; ?>