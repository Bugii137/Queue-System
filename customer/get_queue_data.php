<?php
include "../config.php";

header('Content-Type: application/json');

// Currently serving
$stmt = $pdo->query("
    SELECT qt.ticket_number, s.service_name
    FROM queue_tickets qt
    JOIN services s ON s.id = qt.service_id
    WHERE qt.status = 'serving' AND DATE(qt.created_at) = CURDATE()
    ORDER BY qt.served_at DESC
    LIMIT 1
");
$serving = $stmt->fetch(PDO::FETCH_ASSOC);

// Next 5 waiting
$stmt = $pdo->query("
    SELECT qt.ticket_number, s.service_name, qt.queue_position
    FROM queue_tickets qt
    JOIN services s ON s.id = qt.service_id
    WHERE qt.status = 'waiting' AND DATE(qt.created_at) = CURDATE()
    ORDER BY qt.priority DESC, qt.queue_position ASC
    LIMIT 5
");
$upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'serving'  => $serving ? $serving['ticket_number'] : null,
    'upcoming' => $upcoming
]);
