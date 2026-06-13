<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$service_id = (int) ($_GET['service_id'] ?? 0);

// Get service details
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    redirect("dashboard.php");
}

// Get all tickets for this service today, ordered by status then position
$stmt = $pdo->prepare("
    SELECT * FROM queue_tickets
    WHERE service_id = ? AND DATE(created_at) = CURDATE()
    ORDER BY
        CASE status
            WHEN 'serving'   THEN 0
            WHEN 'waiting'   THEN 1
            WHEN 'completed' THEN 2
            WHEN 'skipped'   THEN 3
            ELSE 4
        END,
        queue_position ASC
");
$stmt->execute([$service_id]);
$tickets = $stmt->fetchAll();

$stats           = getQueueStats($pdo, $service_id);
$current_serving = getCurrentServingTicket($pdo, $service_id);

include "../includes/header.php";
?>

<div class="container" style="max-width:1200px; margin-top:30px;">

    <?php displayAlert(); ?>

    <!-- HEADER -->
    <div style="margin-bottom:25px; display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
        <a href="dashboard.php" class="btn btn-secondary">← Back</a>
        <h2 class="page-title" style="margin:0;">
            <?= htmlspecialchars($service['service_name']); ?> — Queue
        </h2>
    </div>

    <!-- STAT CARDS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin-bottom:25px;">
        <div class="stat-card">
            <h5>Waiting</h5>
            <div class="stat-number"><?= $stats['waiting']; ?></div>
        </div>
        <div class="stat-card" style="border-color:#28a745;">
            <h5>Now Serving</h5>
            <div class="stat-number" style="color:#28a745;"><?= $stats['serving']; ?></div>
        </div>
        <div class="stat-card" style="border-color:#6c757d;">
            <h5>Completed Today</h5>
            <div class="stat-number" style="color:#6c757d;"><?= $stats['completed']; ?></div>
        </div>
    </div>

    <!-- CALL NEXT BUTTON -->
    <div style="margin-bottom:25px; text-align:center;">
        <a href="call_next.php?service_id=<?= $service_id; ?>"
           class="btn btn-primary btn-main"
           style="max-width:280px; font-size:17px;"
           onclick="return confirm('Call the next waiting ticket?')">
            📢 Call Next Ticket
        </a>
    </div>

    <!-- QUEUE TABLE -->
    <div style="background:white; border-radius:12px; padding:20px; box-shadow:0 5px 18px rgba(0,0,0,0.08);">
        <h4 class="section-title">Queue Details</h4>

        <?php if ($tickets): ?>
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Position</th>
                    <th>Wait (min)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['ticket_number']); ?></strong></td>
                    <td><?= htmlspecialchars($t['customer_name'] ?? '—'); ?></td>
                    <td><?= $t['appointment_date'] ?? '—'; ?></td>
                    <td><?= $t['appointment_time'] ?? '—'; ?></td>
                    <td><?= getPriorityBadge($t['priority']); ?></td>
                    <td><?= getStatusBadge($t['status']); ?></td>
                    <td style="text-align:center; font-weight:600;"><?= $t['queue_position'] ?? '—'; ?></td>
                    <td style="text-align:center;"><?= $t['estimated_wait_time'] ?? 0; ?></td>
                    <td>
                        <div class="queue-controls">
                            <?php if ($t['status'] === 'serving'): ?>
                                <a href="complete_ticket.php?action=complete&ticket_id=<?= $t['id']; ?>&service_id=<?= $service_id; ?>"
                                   class="btn btn-success btn-action">Complete</a>
                            <?php endif; ?>

                            <?php if (in_array($t['status'], ['waiting', 'serving'])): ?>
                                <a href="complete_ticket.php?action=skip&ticket_id=<?= $t['id']; ?>&service_id=<?= $service_id; ?>"
                                   class="btn btn-warning btn-action"
                                   onclick="return confirm('Skip ticket <?= htmlspecialchars($t['ticket_number']); ?>?')">Skip</a>
                            <?php endif; ?>

                            <?php if (in_array($t['status'], ['waiting'])): ?>
                                <a href="complete_ticket.php?action=cancel&ticket_id=<?= $t['id']; ?>&service_id=<?= $service_id; ?>"
                                   class="btn btn-danger btn-action"
                                   onclick="return confirm('Cancel ticket <?= htmlspecialchars($t['ticket_number']); ?>?')">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div style="text-align:center; padding:40px; color:#999;">
            <p style="font-size:16px;">No tickets yet today.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
