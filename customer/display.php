<?php
include "../config.php";

// Now serving — today only
$stmt = $pdo->query("
    SELECT qt.ticket_number, s.service_name
    FROM queue_tickets qt
    JOIN services s ON s.id = qt.service_id
    WHERE qt.status = 'serving' AND DATE(qt.created_at) = CURDATE()
    ORDER BY qt.served_at DESC
    LIMIT 1
");
$serving = $stmt->fetch();

// Next 5 waiting — today only, priority first
$stmt = $pdo->query("
    SELECT qt.ticket_number, s.service_name, qt.queue_position
    FROM queue_tickets qt
    JOIN services s ON s.id = qt.service_id
    WHERE qt.status = 'waiting' AND DATE(qt.created_at) = CURDATE()
    ORDER BY qt.priority DESC, qt.queue_position ASC
    LIMIT 5
");
$upcoming = $stmt->fetchAll();

include "../includes/header.php";
?>

<div class="container-box">

    <!-- HEADER -->
    <div style="text-align:center; margin-bottom:20px;">
        <h1 class="page-title" style="margin-bottom:4px;">DIGITAL QUEUE DISPLAY</h1>
        <p style="color:#999; font-size:13px;">Last updated: <span id="clock">—</span></p>
    </div>

    <div class="display-grid">

        <!-- NOW SERVING -->
        <div class="display-panel display-left" id="serving-panel">
            <h2 style="text-align:center; color:#666; font-size:15px; margin-bottom:12px;">NOW SERVING</h2>
            <div class="display-now" id="serving-number">
                <?= $serving ? htmlspecialchars($serving['ticket_number']) : '---'; ?>
            </div>
            <?php if ($serving): ?>
                <p style="text-align:center; color:#666; font-size:13px; margin-top:8px;">
                    <?= htmlspecialchars($serving['service_name']); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- NEXT IN QUEUE -->
        <div class="display-panel display-right">
            <h2 style="text-align:center; color:#666; font-size:15px; margin-bottom:12px;">NEXT IN QUEUE</h2>

            <?php if ($upcoming): ?>
                <div id="upcoming-list" style="display:grid; gap:10px;">
                    <?php foreach ($upcoming as $idx => $t): ?>
                        <div class="display-next">
                            <span style="opacity:0.5; font-size:11px;">#<?= $idx + 1; ?></span>
                            <div style="font-size:22px; font-weight:900;"><?= htmlspecialchars($t['ticket_number']); ?></div>
                            <span style="font-size:12px; color:#666;"><?= htmlspecialchars($t['service_name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#999;" id="upcoming-list">No tickets waiting</p>
            <?php endif; ?>
        </div>

    </div>

    <div style="text-align:center; margin-top:25px;">
        <a href="index.php" class="btn btn-secondary btn-back-small">← Back</a>
    </div>

</div>

<script>
// Update clock every second
function updateClock() {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString();
}
setInterval(updateClock, 1000);
updateClock();

// AJAX refresh queue data every 10 seconds (no full page reload)
function refreshQueue() {
    fetch('get_queue_data.php')
        .then(r => r.json())
        .then(data => {
            // Update serving number
            document.getElementById('serving-number').textContent = data.serving || '---';

            // Update upcoming list
            const list = document.getElementById('upcoming-list');
            if (data.upcoming && data.upcoming.length > 0) {
                list.innerHTML = data.upcoming.map((t, i) =>
                    `<div class="display-next">
                        <span style="opacity:0.5;font-size:11px;">#${i + 1}</span>
                        <div style="font-size:22px;font-weight:900;">${t.ticket_number}</div>
                        <span style="font-size:12px;color:#666;">${t.service_name}</span>
                    </div>`
                ).join('');
            } else {
                list.innerHTML = '<p style="text-align:center;color:#999;">No tickets waiting</p>';
            }
        })
        .catch(() => {}); // silent fail — page still works
}

setInterval(refreshQueue, 10000);
</script>

<?php include "../includes/footer.php"; ?>
