<?php
// ============================================
// HELPER FUNCTIONS
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
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
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

function generateTicketNumber($service_code) {
    $date = date('Ymd');
    $random = rand(100, 999);
    return TICKET_PREFIX . '-' . $service_code . '-' . $date . $random;
}

function calculateWaitTime($position, $avg_minutes) {
    return $position * $avg_minutes;
}

// ============================================
// QUEUE STATISTICS
// ============================================

function getQueueStats($pdo, $service_id = null) {
    // when filtering by service, append as an AND clause (we already have WHERE in each query)
    $where = $service_id ? "AND service_id = ?" : "";
    
    // Total waiting
    $sql = "SELECT COUNT(*) FROM queue_tickets WHERE status = 'waiting' $where";
    $stmt = $pdo->prepare($sql);
    $params = $service_id ? [$service_id] : [];
    $stmt->execute($params);
    $waiting = $stmt->fetchColumn();
    
    // Currently serving
    $sql = "SELECT COUNT(*) FROM queue_tickets WHERE status = 'serving' $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $serving = $stmt->fetchColumn();
    
    // Completed today
    $sql = "SELECT COUNT(*) FROM queue_tickets WHERE status = 'completed' AND DATE(created_at) = CURDATE() $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $completed = $stmt->fetchColumn();
    
    return [
        'waiting' => $waiting,
        'serving' => $serving,
        'completed' => $completed,
        'total' => $waiting + $serving + $completed
    ];
}

// ============================================
// STATUS BADGE GENERATOR
// ============================================

function getStatusBadge($status) {
    $badges = [
        'waiting' => '<span class="badge-waiting">Waiting</span>',
        'serving' => '<span class="badge-serving">Serving</span>',
        'completed' => '<span class="badge-completed">Completed</span>'
    ];
    return $badges[$status] ?? '<span class="badge-waiting">Unknown</span>';
}

// ============================================
// PRIORITY BADGE
// ============================================

function getPriorityBadge($priority) {
    $badges = [
        'normal' => '',
        'priority' => '<span class="badge-priority">⭐ PRIORITY</span>'
    ];
    return $badges[$priority] ?? '';
}

// ============================================
// GET CURRENT SERVING TICKET
// ============================================

function getCurrentServingTicket($pdo, $service_id = null) {
    $where = "WHERE status = 'serving'";
    if ($service_id) {
        $where .= " AND service_id = ?";
    }
    
    $sql = "SELECT * FROM queue_tickets $where ORDER BY served_at DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($service_id ? [$service_id] : []);
    return $stmt->fetch();
}

// ============================================
// GET NEXT WAITING TICKETS
// ============================================

function getNextTickets($pdo, $limit = 5, $service_id = null) {
    $where = "WHERE status = 'waiting'";
    if ($service_id) {
        $where .= " AND service_id = ?";
    }
    
    $sql = "SELECT ticket_number, service_id FROM queue_tickets $where ORDER BY created_at ASC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $params = $service_id ? [$service_id, $limit] : [$limit];
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================
// CALCULATE AVERAGE WAIT TIME
// ============================================

function getAverageWaitTime($pdo, $service_id) {
    $sql = "SELECT AVG(estimated_wait_time) FROM queue_tickets 
            WHERE service_id = ? AND status = 'completed' AND DATE(completed_at) = CURDATE()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$service_id]);
    $avg = $stmt->fetchColumn();
    return round($avg) ?: 0;
}

// ============================================
// GET PEAK HOUR DATA
// ============================================

function getPeakHourStats($pdo, $service_id = null) {
    $where = "WHERE DATE(created_at) = CURDATE()";
    if ($service_id) {
        $where .= " AND service_id = ?";
    }
    
    $sql = "SELECT HOUR(created_at) as hour, COUNT(*) as count 
            FROM queue_tickets $where 
            GROUP BY HOUR(created_at) 
            ORDER BY hour ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($service_id ? [$service_id] : []);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// SKIP TICKET
// ============================================

function skipTicket($pdo, $ticket_id) {
    // Removed `updated_at` to support schemas without that column
    $sql = "UPDATE queue_tickets SET status = 'skipped' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ticket_id]);
}

// ============================================
// CALL NEXT TICKET
// ============================================

function callNextTicket($pdo, $service_id) {
    // Get next waiting ticket
    $sql = "SELECT * FROM queue_tickets 
            WHERE service_id = ? AND status = 'waiting' 
            ORDER BY created_at ASC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$service_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) return null;
    
    // Mark as serving
    $sql = "UPDATE queue_tickets SET status = 'serving', served_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ticket['id']]);
    
    return $ticket;
}

// ============================================
// COMPLETE TICKET
// ============================================

function completeTicket($pdo, $ticket_id) {
    // Removed `updated_at` to support schemas without that column
    $sql = "UPDATE queue_tickets 
            SET status = 'completed', completed_at = NOW() 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ticket_id]);
}

// ============================================
// FORMAT TIME
// ============================================

function formatTimeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return round($diff / 60) . 'm ago';
    if ($diff < 86400) return round($diff / 3600) . 'h ago';
    return round($diff / 86400) . 'd ago';
}

?>
