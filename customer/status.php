<?php
include "../config.php";
include "../includes/header.php";

$ticket = null;
$error  = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_number = trim($_POST['ticket_number'] ?? '');

    if (!$ticket_number) {
        $error = "Please enter a ticket number.";
    } else {
        // getTicketByNumber() joins services so we get service_name directly
        $ticket = getTicketByNumber($pdo, strtoupper($ticket_number));
        if (!$ticket) {
            $error = "Ticket not found. Please check the number and try again.";
        }
    }
}
?>

<div class="container-box">
    <h1 class="page-title">Check Ticket Status</h1>

    <form method="POST" style="max-width:400px; margin:auto;">
        <div class="form-group">
            <label class="form-label">Enter Ticket Number</label>
            <input type="text"
                   name="ticket_number"
                   class="form-control"
                   placeholder="e.g. Q-0012"
                   value="<?= htmlspecialchars($_POST['ticket_number'] ?? ''); ?>"
                   required>
        </div>
        <div class="cta-group" style="margin-top:15px;">
            <button type="submit" class="btn btn-primary btn-main">Check Status</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-top:20px;">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($ticket): ?>
        <div class="ticket-info" style="margin-top:30px;">
            <div class="ticket-number" style="font-size:36px; padding:20px;">
                <?= htmlspecialchars($ticket['ticket_number']); ?>
            </div>

            <p><strong>Name:</strong> <?= htmlspecialchars($ticket['customer_name'] ?? '—'); ?></p>
            <p><strong>Service:</strong> <?= htmlspecialchars($ticket['service_name']); ?></p>
            <p><strong>Status:</strong> <?= getStatusBadge($ticket['status']); ?></p>

            <?php if ($ticket['status'] === 'waiting'): ?>
                <p><strong>Position in Queue:</strong> #<?= $ticket['queue_position']; ?></p>
                <p><strong>Estimated Wait:</strong> <?= $ticket['estimated_wait_time']; ?> minutes</p>
            <?php endif; ?>

            <?php if ($ticket['appointment_date']): ?>
                <p><strong>Appointment Date:</strong> <?= htmlspecialchars($ticket['appointment_date']); ?></p>
            <?php endif; ?>

            <?php if ($ticket['appointment_time']): ?>
                <p><strong>Appointment Time:</strong> <?= htmlspecialchars($ticket['appointment_time']); ?></p>
            <?php endif; ?>

            <?php if ($ticket['status'] === 'completed'): ?>
                <div class="alert alert-success" style="margin-top:15px;">
                    ✓ Your ticket has been completed. Thank you!
                </div>
            <?php elseif ($ticket['status'] === 'serving'): ?>
                <div class="alert alert-success" style="margin-top:15px;">
                    🟢 Your ticket is currently being served. Please proceed to the counter.
                </div>
            <?php elseif ($ticket['status'] === 'skipped'): ?>
                <div class="alert alert-warning" style="margin-top:15px;">
                    ⚠ Your ticket was skipped. Please visit the counter or get a new ticket.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div style="text-align:center; margin:20px 0;">
    <a href="index.php" class="btn btn-secondary btn-back-small">← Back</a>
</div>

<?php include "../includes/footer.php"; ?>
