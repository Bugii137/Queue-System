<?php
// Customer Ticket Status Checker
include "../config.php";
include "../includes/header.php";

$status = null;
$ticket = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_number = trim($_POST['ticket_number']);
    if ($ticket_number) {
        $stmt = $pdo->prepare("SELECT * FROM queue_tickets WHERE ticket_number = ?");
        $stmt->execute([$ticket_number]);
        $ticket = $stmt->fetch();
        if ($ticket) {
            // Calculate position in queue
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM queue_tickets WHERE service_id = ? AND status = 'waiting' AND queue_position < ?");
            $stmt->execute([$ticket['service_id'], $ticket['queue_position']]);
            $ahead = $stmt->fetchColumn();
            $status = $ticket['status'];
        } else {
            $error = "Ticket not found.";
        }
    } else {
        $error = "Please enter a ticket number.";
    }
}
?>

<div class="container-box">
    <h1 class="page-title">Check Ticket Status</h1>
    <form method="POST" style="max-width:400px;margin:auto;">
        <div class="form-group">
            <label class="form-label">Enter Ticket Number</label>
            <input type="text" name="ticket_number" class="form-control" required>
        </div>
        <div class="cta-group" style="margin-top:20px;">
            <button type="submit" class="btn btn-primary btn-main">Check Status</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-top:20px;"> <?= htmlspecialchars($error); ?> </div>
    <?php endif; ?>

    <?php if ($ticket): ?>
        <div class="status-box" style="margin-top:30px;">
            <h3>Ticket: <strong><?= htmlspecialchars($ticket['ticket_number']); ?></strong></h3>
            <p><strong>Service:</strong> <?= htmlspecialchars($ticket['service_id']); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($status); ?></p>
            <p><strong>Position in Queue:</strong> <?= $ahead + 1; ?></p>
            <p><strong>Estimated Wait:</strong> <?= $ticket['estimated_wait_time']; ?> min</p>
            <p><strong>Appointment Date:</strong> <?= htmlspecialchars($ticket['appointment_date']); ?></p>
            <p><strong>Appointment Time:</strong> <?= htmlspecialchars($ticket['appointment_time']); ?></p>
        </div>
    <?php endif; ?>
</div>


    <?php // Place back button at the bottom of the page ?>
    <div style="width:100%;text-align:center;margin:40px 0 0 0;">
        <a href="index.php" class="btn btn-secondary btn-back-small">← Back</a>
    </div>

    <?php include "../includes/footer.php"; ?>
