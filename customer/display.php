<?php
include "../config.php";

$auto_refresh = true; // enables auto refresh meta

// get currently serving
$stmt = $pdo->query("
    SELECT ticket_number
    FROM queue_tickets
    WHERE status = 'serving'
    ORDER BY served_at DESC
    LIMIT 1
");
$serving = $stmt->fetchColumn();

// get next waiting tickets
$stmt = $pdo->query("
    SELECT ticket_number
    FROM queue_tickets
    WHERE status = 'waiting'
    ORDER BY created_at ASC
    LIMIT 5
");
$upcoming = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include "../includes/header.php"; ?>

<div class="container-box">

    <!-- HEADER -->
    <div style="text-align: center; margin-bottom: 10px;">
        <h1 class="page-title" style="margin-bottom:6px;">DIGITAL QUEUE DISPLAY</h1>
        <p style="color: #666; font-size: 13px; margin-bottom: 18px;">Last updated: <span id="time"></span></p>
    </div>

    <div class="display-grid" style="align-items:stretch;">

        <!-- LEFT: NOW SERVING -->
        <div class="display-panel display-left">
            <h2 style="text-align: center; color: #666; margin-bottom: 12px; font-size: 16px;">NOW SERVING</h2>
            <div class="display-now">
                <?= $serving ?? '---'; ?>
            </div>
        </div>

        <!-- RIGHT: NEXT TICKETS -->
        <div class="display-panel display-right">
            <h2 style="text-align: center; color: #666; margin-bottom: 12px; font-size: 16px;">NEXT IN QUEUE</h2>
            
            <?php if ($upcoming): ?>
                <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                    <?php foreach($upcoming as $idx => $t): ?>
                        <div class="display-next">
                            <span style="opacity: 0.6; font-size: 12px;">#<?= $idx + 1 ?></span>
                            <div style="font-size: 24px; font-weight: 900;"><?= $t; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999;">No more tickets today</p>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- AUTO-REFRESH SCRIPT -->
<script>
    // Update time display
    function updateTime() {
        let now = new Date();
        document.getElementById('time').textContent = now.toLocaleTimeString();
    }
    
    // Update every second
    setInterval(updateTime, 1000);
    updateTime();
    
    // Refresh page every 5 seconds
    setInterval(function() {
        location.reload();
    }, 5000);
</script>

<?php include "../includes/footer.php"; ?>
