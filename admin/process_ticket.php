<?php
include "../config.php";
include "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$ticket_id = $_GET['ticket_id'] ?? 0;
$action = $_GET['action'] ?? '';

if (!$ticket_id || !$action) {
    redirect('dashboard.php');
}

switch ($action) {

    case 'call':
        $status = 'called';
        $field = 'called_at';
        break;

    case 'serve':
        $status = 'serving';
        $field = 'served_at';
        break;

    case 'complete':
        $status = 'completed';
        $field = 'completed_at';
        break;

    case 'cancel':
        $status = 'cancelled';
        $field = null;
        break;

    default:
        redirect('dashboard.php');
}

if ($field) {
    $stmt = $pdo->prepare("
        UPDATE queue_tickets
        SET status = ?, $field = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $ticket_id]);
} else {
    $stmt = $pdo->prepare("
        UPDATE queue_tickets
        SET status = ?
        WHERE id = ?
    ");
    $stmt->execute([$status, $ticket_id]);
}

redirect($_SERVER['HTTP_REFERER']);
?>
