<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$action = $_GET['action'] ?? 'complete';
$ticket_id = $_GET['ticket_id'] ?? 0;
$service_id = $_GET['service_id'] ?? 0;

/* Get ticket details */
$stmt = $pdo->prepare("SELECT * FROM queue_tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    showAlert('danger', 'Ticket not found');
    redirect("dashboard.php");
}

/* Handle actions */
if ($action === 'complete') {
    $success = completeTicket($pdo, $ticket_id);
    $message = $success ? 'Ticket ' . $ticket['ticket_number'] . ' completed!' : 'Error completing ticket';
    $type = $success ? 'success' : 'danger';
} elseif ($action === 'skip') {
    $success = skipTicket($pdo, $ticket_id);
    $message = $success ? 'Ticket ' . $ticket['ticket_number'] . ' skipped' : 'Error skipping ticket';
    $type = $success ? 'warning' : 'danger';
}

showAlert($type, $message);

$referer = $_SERVER['HTTP_REFERER'] ?? "view_queue.php?service_id=$service_id";
redirect($referer);
?>
