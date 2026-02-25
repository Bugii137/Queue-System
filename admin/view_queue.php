<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$service_id = $_GET['service_id'] ?? 0;

/* ===============================
   GET SERVICE DETAILS
================================ */
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    redirect("dashboard.php");
}

/* ===============================
   GET QUEUE TICKETS
================================ */
$stmt = $pdo->prepare("
    SELECT * FROM queue_tickets
    WHERE service_id = ?
    ORDER BY queue_position ASC, created_at ASC
");
$stmt->execute([$service_id]);

$tickets = $stmt->fetchAll();
?>

<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$service_id = $_GET['service_id'] ?? 0;

/* Get service details */
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    redirect("dashboard.php");
}

/* Get all tickets for this service */
$stmt = $pdo->prepare("
    SELECT * FROM queue_tickets
    WHERE service_id = ?
    ORDER BY 
        CASE WHEN status = 'serving' THEN 0
             WHEN status = 'waiting' THEN 1
             ELSE 2 
        END, 
        created_at ASC
");
$stmt->execute([$service_id]);
$tickets = $stmt->fetchAll();

/* Get queue statistics */
$stats = getQueueStats($pdo, $service_id);
$current_serving = getCurrentServingTicket($pdo, $service_id);
?>

<?php include "../includes/header.php"; ?>

<div class="container" style="max-width: 1200px; margin-top: 30px;">

    <?php displayAlert(); ?>

    <!-- HEADER -->
    <div style="margin-bottom: 30px;">
        <a href="dashboard.php" class="btn btn-secondary me-2">← Back</a>
        <h2 class="page-title" style="margin-bottom: 10px;">
            <?= htmlspecialchars($service['service_name']); ?> Queue
        </h2>
    </div>

    <!-- STATISTICS CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 30px;">
        
        <div class="stat-card">
            <h5>Waiting</h5>
            <div class="stat-number"><?= $stats['waiting']; ?></div>
        </div>

        <div class="stat-card">
            <h5>Now Serving</h5>
            <div class="stat-number"><?= $stats['serving']; ?></div>
        </div>

        <div class="stat-card">
            <h5>Completed Today</h5>
            <div class="stat-number"><?= $stats['completed']; ?></div>
        </div>

    </div>

    <!-- CALL NEXT ACTION -->
    <div style="margin-bottom: 30px; text-align: center;">
        <a href="call_next.php?service_id=<?= $service_id; ?>"
           class="btn btn-primary btn-main"
           style="max-width: 300px; font-size: 18px; padding: 15px;">
           Call Next Ticket
        </a>
    </div>

    <!-- QUEUE TABLE -->
    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 5px 18px rgba(0,0,0,0.08);">

        <h4 class="section-title">Queue Details</h4>

        <?php if ($tickets): ?>

            <table class="queue-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Position</th>
                        <th>Wait Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach($tickets as $t): ?>

                    <tr>
                        <td><strong><?= $t['ticket_number']; ?></strong></td>
                        <td><?= htmlspecialchars($t['customer_name']); ?></td>
                        <td><?= getStatusBadge($t['status']); ?></td>
                        <td style="text-align: center; font-weight: 600;"><?= $t['queue_position']; ?></td>
                        <td><?= $t['estimated_wait_time']; ?> min</td>
                        <td>
                            <div class="queue-controls">

                                <?php if ($t['status'] == 'waiting' && !$current_serving): ?>
                                    <a href="call_next.php?service_id=<?= $service_id; ?>"
                                       class="btn btn-success btn-action"
                                       title="Call this ticket">
                                       Call
                                    </a>
                                <?php endif; ?>

                                <?php if ($t['status'] == 'serving'): ?>
                                    <a href="complete_ticket.php?action=complete&ticket_id=<?= $t['id']; ?>&service_id=<?= $service_id; ?>"
                                       class="btn btn-success btn-action"
                                       title="Mark as completed">
                                       Complete
                                    </a>
                                <?php endif; ?>

                                <?php if ($t['status'] != 'completed' && $t['status'] != 'skipped'): ?>
                                    <a href="complete_ticket.php?action=skip&ticket_id=<?= $t['id']; ?>&service_id=<?= $service_id; ?>"
                                       class="btn btn-danger btn-action"
                                       title="Skip this ticket"
                                       onclick="return confirm('Skip ticket <?= $t['ticket_number']; ?>?')">
                                       Skip
                                    </a>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div style="text-align: center; padding: 40px; color: #666;">
                <p style="font-size: 16px;">All tickets completed!</p>
                <p style="font-size: 14px; color: #999;">No tickets in queue</p>
            </div>

        <?php endif; ?>

    </div>

    </div>

</div>

<?php include "../includes/footer.php"; ?>

        </tbody>

    </table>

</div>

<?php include "../includes/footer.php"; ?>
