<?php
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    redirect("index.php");
}

$service_id = $_POST['service_id'];
$name = trim($_POST['customer_name']);
$phone = trim($_POST['customer_phone']);

// get service info
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Invalid service selected");
}

// count waiting tickets
$stmt = $pdo->prepare("SELECT COUNT(*) FROM queue_tickets WHERE service_id = ? AND status = 'waiting'");
$stmt->execute([$service_id]);
$position = $stmt->fetchColumn() + 1;

// generate ticket number
$ticket_number = generateTicketNumber($service['service_code']);

// calculate estimated wait
$estimated_wait = calculateWaitTime($position, $service['average_time_minutes']);

// insert ticket
$stmt = $pdo->prepare("
    INSERT INTO queue_tickets
    (ticket_number, service_id, customer_name, customer_phone, status, queue_position, estimated_wait_time)
    VALUES (?, ?, ?, ?, 'waiting', ?, ?)
");

$stmt->execute([
    $ticket_number,
    $service_id,
    $name,
    $phone,
    $position,
    $estimated_wait
]);
?>

<?php include "../includes/header.php"; ?>

<div class="container-box">

    <h1 class="page-title">Ticket Issued</h1>

    <div class="ticket-number">
        <?= $ticket_number; ?>
    </div>

    <div class="ticket-info">
        <p><strong>Service:</strong> <?= htmlspecialchars($service['service_name']); ?></p>
        <p><strong>Your Position:</strong> #<?= $position; ?></p>
        <p><strong>Estimated Wait Time:</strong> <?= $estimated_wait; ?> minutes</p>
    </div>

    <div class="note-box">
        <p style="margin: 0;">
            <strong>Note:</strong> Please keep your ticket number safe. Check the display screen for real-time updates.
        </p>
    </div>

    <div class="cta-group">
        <button id="copyBtn" class="btn btn-action btn-outline-primary">Copy Ticket</button>
        <button id="printBtn" class="btn btn-action btn-primary">Print</button>
        <a href="index.php" class="btn btn-outline-secondary btn-main">Get Another Ticket</a>
        <a href="display.php" class="btn btn-outline-primary btn-main" target="_blank">View Queue Status</a>
    </div>

</div>

<script>
    document.getElementById('copyBtn').addEventListener('click', function() {
        const text = `<?= $ticket_number; ?>`;
        navigator.clipboard.writeText(text).then(function() {
            alert('Ticket copied to clipboard: ' + text);
        }).catch(function() {
            alert('Copy failed.');
        });
    });

    document.getElementById('printBtn').addEventListener('click', function() {
        window.print();
    });
</script>

<?php include "../includes/footer.php"; ?>
