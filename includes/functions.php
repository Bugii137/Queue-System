<?php
// ============================================
// UTILITIES
// ============================================

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function showAlert($type, $message) {
    $_SESSION['alert'] = ['type' => $type, 'message' => $message];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        echo "<div class='alert alert-{$a['type']} alert-dismissible fade show'>
                {$a['message']}
                <button class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['alert']);
    }
}

function formatTimeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return 'just now';
    if ($diff < 3600)  return round($diff / 60) . 'm ago';
    if ($diff < 86400) return round($diff / 3600) . 'h ago';
    return round($diff / 86400) . 'd ago';
}

// ============================================
// BADGES
// ============================================

function getStatusBadge($status) {
    $map = [
        'waiting'   => 'badge-waiting',
        'serving'   => 'badge-serving',
        'completed' => 'badge-completed',
        'skipped'   => 'badge-skipped',
        'cancelled' => 'badge-cancelled',
    ];
    $class = $map[$status] ?? 'badge-waiting';
    return "<span class='{$class}'>" . ucfirst($status) . "</span>";
}

function getPriorityBadge($priority) {
    return $priority ? "<span class='badge-priority'>⭐ PRIORITY</span>" : '';
}

// ============================================
// TICKET GENERATION
// ============================================

function generateTicketNumber($pdo, $service_id) {
    $today = date('Y-m-d');

    // Upsert counter for today
    $stmt = $pdo->prepare("
        INSERT INTO daily_counters (service_id, counter_date, last_number)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE last_number = last_number + 1
    ");
    $stmt->execute([$service_id, $today]);

    // Get the new number
    $stmt = $pdo->prepare("
        SELECT last_number FROM daily_counters
        WHERE service_id = ? AND counter_date = ?
    ");
    $stmt->execute([$service_id, $today]);
    $num = $stmt->fetchColumn();

    return TICKET_PREFIX . '-' . str_pad($num, 4, '0', STR_PAD_LEFT); // e.g. Q-0001
}

function calculateWaitTime($position, $avg_minutes = ESTIMATED_TIME_PER_TICKET) {
    return max(0, ($position - 1) * $avg_minutes);
}

// ============================================
// QUEUE STATS
// ============================================

function getQueueStats($pdo, $service_id = null) {
    $and = $service_id ? "AND service_id = " . (int)$service_id : "";

    $row = $pdo->query("
        SELECT
            SUM(status = 'waiting')                                    AS waiting,
            SUM(status = 'serving')                                    AS serving,
            SUM(status = 'completed' AND DATE(created_at) = CURDATE()) AS completed
        FROM queue_tickets
        WHERE DATE(created_at) = CURDATE() $and
    ")->fetch(PDO::FETCH_ASSOC);

    $row['waiting']   = (int)$row['waiting'];
    $row['serving']   = (int)$row['serving'];
    $row['completed'] = (int)$row['completed'];
    $row['total']     = $row['waiting'] + $row['serving'] + $row['completed'];
    return $row;
}

// ============================================
// QUEUE ACTIONS
// ============================================

function callNextTicket($pdo, $service_id) {
    // Get next ticket: priority first, then oldest
    $stmt = $pdo->prepare("
        SELECT * FROM queue_tickets
        WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()
        ORDER BY priority DESC, queue_position ASC
        LIMIT 1
    ");
    $stmt->execute([$service_id]);
    $ticket = $stmt->fetch();

    if (!$ticket) return null;

    // Mark as serving
    $pdo->prepare("
        UPDATE queue_tickets
        SET status = 'serving', called_at = NOW(), served_at = NOW(),
            served_by = ?
        WHERE id = ?
    ")->execute([$_SESSION['user_id'] ?? null, $ticket['id']]);

    // Log it
    logActivity($pdo, $ticket['id'], 'serving');

    // Recalculate positions for remaining waiting tickets
    recalculatePositions($pdo, $service_id);

    return $ticket;
}

function completeTicket($pdo, $ticket_id) {
    $pdo->prepare("
        UPDATE queue_tickets SET status = 'completed', completed_at = NOW()
        WHERE id = ?
    ")->execute([$ticket_id]);
    logActivity($pdo, $ticket_id, 'completed');
}

function skipTicket($pdo, $ticket_id) {
    $pdo->prepare("
        UPDATE queue_tickets SET status = 'skipped' WHERE id = ?
    ")->execute([$ticket_id]);
    logActivity($pdo, $ticket_id, 'skipped');
}

function cancelTicket($pdo, $ticket_id) {
    $pdo->prepare("
        UPDATE queue_tickets SET status = 'cancelled' WHERE id = ?
    ")->execute([$ticket_id]);
    logActivity($pdo, $ticket_id, 'cancelled');
}

// ============================================
// DISPLAY HELPERS
// ============================================

function getCurrentServingTicket($pdo, $service_id = null) {
    $and = $service_id ? "AND qt.service_id = " . (int)$service_id : "";
    return $pdo->query("
        SELECT qt.*, s.service_name
        FROM queue_tickets qt
        JOIN services s ON s.id = qt.service_id
        WHERE qt.status = 'serving' $and
        ORDER BY qt.served_at DESC LIMIT 1
    ")->fetch();
}

function getNextTickets($pdo, $limit = 5, $service_id = null) {
    $and = $service_id ? "AND service_id = " . (int)$service_id : "";
    $stmt = $pdo->prepare("
        SELECT ticket_number, queue_position, estimated_wait_time
        FROM queue_tickets
        WHERE status = 'waiting' AND DATE(created_at) = CURDATE() $and
        ORDER BY priority DESC, queue_position ASC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getTicketByNumber($pdo, $ticket_number) {
    $stmt = $pdo->prepare("
        SELECT qt.*, s.service_name, s.avg_service_minutes
        FROM queue_tickets qt
        JOIN services s ON s.id = qt.service_id
        WHERE qt.ticket_number = ?
    ");
    $stmt->execute([$ticket_number]);
    return $stmt->fetch();
}

function getAverageWaitTime($pdo, $service_id) {
    $stmt = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, served_at, completed_at))
        FROM queue_tickets
        WHERE service_id = ? AND status = 'completed' AND DATE(completed_at) = CURDATE()
    ");
    $stmt->execute([$service_id]);
    return round($stmt->fetchColumn()) ?: ESTIMATED_TIME_PER_TICKET;
}

function getPeakHourStats($pdo, $service_id = null) {
    $and = $service_id ? "AND service_id = " . (int)$service_id : "";
    return $pdo->query("
        SELECT HOUR(created_at) AS hour, COUNT(*) AS count
        FROM queue_tickets
        WHERE DATE(created_at) = CURDATE() $and
        GROUP BY HOUR(created_at)
        ORDER BY hour ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// INTERNAL HELPERS
// ============================================

function recalculatePositions($pdo, $service_id) {
    $stmt = $pdo->prepare("
        SELECT id FROM queue_tickets
        WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()
        ORDER BY priority DESC, id ASC
    ");
    $stmt->execute([$service_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $avg = $pdo->prepare("SELECT avg_service_minutes FROM services WHERE id = ?");
    $avg->execute([$service_id]);
    $mins = $avg->fetchColumn() ?: ESTIMATED_TIME_PER_TICKET;

    $update = $pdo->prepare("
        UPDATE queue_tickets SET queue_position = ?, estimated_wait_time = ? WHERE id = ?
    ");
    foreach ($tickets as $pos => $id) {
        $update->execute([$pos + 1, $pos * $mins, $id]);
    }
}

function logActivity($pdo, $ticket_id, $action) {
    $pdo->prepare("
        INSERT INTO activity_log (ticket_id, action, performed_by)
        VALUES (?, ?, ?)
    ")->execute([$ticket_id, $action, $_SESSION['user_id'] ?? null]);
}