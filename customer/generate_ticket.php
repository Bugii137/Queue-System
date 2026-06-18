<?php
include "../config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    redirect("index.php");
}

// Sanitize inputs
$service_id       = (int) $_POST['service_id'];
$name             = trim($_POST['customer_name']);
$phone            = trim($_POST['customer_phone'] ?? '');
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

// Validate service exists
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Invalid service selected.");
}

try {
    // Generate ticket number using daily counter (e.g. Q-0001)
    $ticket_number = generateTicketNumber($pdo, $service_id);

    // Queue position = waiting tickets for this service today + 1
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM queue_tickets
         WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()"
    );
    $stmt->execute([$service_id]);
    $position = (int) $stmt->fetchColumn() + 1;

    // Estimated wait based on position
    $estimated_wait = calculateWaitTime($position, $service['avg_service_minutes']);

    // Insert ticket (single INSERT)
    $stmt = $pdo->prepare(
        "INSERT INTO queue_tickets
         (ticket_number, service_id, customer_name, customer_phone,
          appointment_date, appointment_time, status, queue_position, estimated_wait_time)
         VALUES (?, ?, ?, ?, ?, ?, 'waiting', ?, ?)"
    );
    $stmt->execute([
        $ticket_number,
        $service_id,
        $name,
        $phone,
        $appointment_date,
        $appointment_time,
        $position,
        $estimated_wait
    ]);
} catch (Exception $e) {
    include "../includes/header.php";
    echo '<div class="container-box" style="padding:40px; text-align:center;">
            <h1 class="page-title">Ticket Error</h1>
            <div class="alert alert-danger" style="margin:20px auto; max-width:600px;">
                Could not generate a unique ticket. Please try again.
            </div>
            <a href="index.php" class="btn btn-primary btn-main">Back to ticket form</a>
          </div>';
    include "../includes/footer.php";
    exit;
}

include "../includes/header.php";
?>

<div class="container-box">
    <h1 class="page-title">Ticket Issued</h1>

    <div class="ticket-number"><?= htmlspecialchars($ticket_number); ?></div>

    <div class="ticket-info">
        <p><strong>Name:</strong> <?= htmlspecialchars($name); ?></p>
        <p><strong>Service:</strong> <?= htmlspecialchars($service['service_name']); ?></p>
        <p><strong>Your Position:</strong> #<?= $position; ?></p>
        <p><strong>Estimated Wait:</strong> <?= $estimated_wait; ?> minutes</p>
        <?php if ($appointment_date): ?>
            <p><strong>Date:</strong> <?= htmlspecialchars($appointment_date); ?></p>
        <?php endif; ?>
        <?php if ($appointment_time): ?>
            <p><strong>Time:</strong> <?= htmlspecialchars($appointment_time); ?></p>
        <?php endif; ?>
    </div>

    <div class="note-box">
        <strong>Note:</strong> Keep your ticket number safe.
        Check the display screen for real-time updates.
    </div>

    <div class="cta-group">
        <button id="copyBtn" class="btn btn-outline-primary btn-main">Copy Ticket</button>
        <button id="printBtn" class="btn btn-primary btn-main">Print</button>
        <a href="index.php" class="btn btn-outline-secondary btn-main">Get Another Ticket</a>
        <a href="display.php" class="btn btn-outline-primary btn-main" target="_blank">View Queue Display</a>
    </div>
</div>

<script>
document.getElementById('copyBtn').addEventListener('click', function () {
    navigator.clipboard.writeText('<?= $ticket_number; ?>')
        .then(() => alert('Copied: <?= $ticket_number; ?>'))
        .catch(() => alert('Copy failed. Please copy manually.'));
});
document.getElementById('printBtn').addEventListener('click', () => window.print());
</script>

<?php include "../includes/footer.php"; ?>
