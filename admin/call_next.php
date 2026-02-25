<?php
include "../config.php";
include "../includes/auth.php";

requireLogin();
requireAdmin();

$service_id = $_GET['service_id'] ?? 0;

/*
1. Find first waiting ticket
2. Change to serving
*/

$stmt = $pdo->prepare("
    SELECT * FROM queue_tickets
    WHERE service_id = ? AND status = 'waiting'
    ORDER BY created_at ASC
    LIMIT 1
");
$stmt->execute([$service_id]);

$ticket = $stmt->fetch();

if ($ticket) {

    // mark as serving
    $update = $pdo->prepare("
        UPDATE queue_tickets
        SET status = 'serving',
            served_at = NOW(),
            served_by = ?
        WHERE id = ?
    ");

    $update->execute([
        $_SESSION['user_id'],
        $ticket['id']
    ]);

    showAlert('success', 'Ticket ' . $ticket['ticket_number'] . ' is now serving');

} else {
    showAlert('warning', 'No waiting tickets');
}

redirect("view_queue.php?service_id=$service_id");
