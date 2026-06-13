<?php
include "../config.php";
include "../includes/auth.php";

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$ticket_id  = (int) ($_GET['ticket_id'] ?? 0);
$action     = $_GET['action'] ?? '';
$service_id = (int) ($_GET['service_id'] ?? 0);

if (!$ticket_id || !$action) {
    redirect('dashboard.php');
}

switch ($action) {
    case 'serve':
    case 'call':       // treat 'call' same as 'serve'
        callNextTicket($pdo, $service_id);
        break;
    case 'complete':
        completeTicket($pdo, $ticket_id);
        break;
    case 'skip':
        skipTicket($pdo, $ticket_id);
        break;
    case 'cancel':
        cancelTicket($pdo, $ticket_id);
        break;
    default:
        redirect('dashboard.php');
}

$back = $_SERVER['HTTP_REFERER'] ?? "view_queue.php?service_id=$service_id";
redirect($back);
