<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$service_id = (int) ($_GET['service_id'] ?? 0);

if (!$service_id) {
    redirect("dashboard.php");
}

// Uses callNextTicket() from functions.php:
// - serves priority customers first
// - logs to activity_log
// - recalculates positions for remaining tickets
$ticket = callNextTicket($pdo, $service_id);

if ($ticket) {
    showAlert('success', 'Now serving ticket: <strong>' . $ticket['ticket_number'] . '</strong>');
} else {
    showAlert('warning', 'No waiting tickets in this queue.');
}

redirect("view_queue.php?service_id=$service_id");
