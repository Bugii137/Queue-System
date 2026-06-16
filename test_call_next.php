<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate admin session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['username'] = 'admin';

include "config.php";

echo "<h1>Call Next Function Test</h1>";
echo "<hr>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $test = $pdo->query("SELECT 1");
    echo "<span style='color:green;'>✓ Database connected successfully</span><br>";
} catch (PDOException $e) {
    echo "<span style='color:red;'>✗ Database connection failed: " . $e->getMessage() . "</span><br>";
    exit;
}

// Test 2: Check queue_tickets table structure
echo "<h2>Test 2: Table Structure Check</h2>";
try {
    $columns = $pdo->query("DESC queue_tickets")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? '—') . "</td>";
        echo "<td>" . ($col['Extra'] ?? '—') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<span style='color:red;'>✗ Error: " . $e->getMessage() . "</span>";
}

// Test 3: Check for waiting tickets
echo "<h2>Test 3: Check Waiting Tickets</h2>";
try {
    $tickets = $pdo->query("
        SELECT id, ticket_number, status, queue_position, service_id
        FROM queue_tickets
        WHERE status = 'waiting' AND DATE(created_at) = CURDATE()
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if ($tickets) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Ticket Number</th><th>Status</th><th>Position</th><th>Service ID</th></tr>";
        foreach ($tickets as $t) {
            echo "<tr>";
            echo "<td>" . $t['id'] . "</td>";
            echo "<td>" . $t['ticket_number'] . "</td>";
            echo "<td>" . $t['status'] . "</td>";
            echo "<td>" . $t['queue_position'] . "</td>";
            echo "<td>" . $t['service_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<span style='color:orange;'>ℹ No waiting tickets found for today</span>";
    }
} catch (PDOException $e) {
    echo "<span style='color:red;'>✗ Error: " . $e->getMessage() . "</span>";
}

// Test 4: Simulate callNextTicket function
echo "<h2>Test 4: Simulate callNextTicket Function</h2>";

// Pick a service for testing (use first available service)
$services = $pdo->query("SELECT id FROM services WHERE is_active = 1 LIMIT 1")->fetch();

if ($services) {
    $service_id = $services['id'];
    echo "<p>Using Service ID: <strong>$service_id</strong></p>";
    
    // Get a waiting ticket
    $stmt = $pdo->prepare("
        SELECT * FROM queue_tickets
        WHERE service_id = ? AND status = 'waiting' AND DATE(created_at) = CURDATE()
        ORDER BY priority DESC, queue_position ASC
        LIMIT 1
    ");
    $stmt->execute([$service_id]);
    $ticket = $stmt->fetch();
    
    if ($ticket) {
        echo "<p>Found ticket: <strong>" . $ticket['ticket_number'] . "</strong></p>";
        echo "<p>Attempting to mark as 'serving'...</p>";
        
        try {
            // Check if called_at and served_by columns exist
            $info = $pdo->query("DESC queue_tickets")->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $missing_columns = [];
            if (!isset($info['called_at'])) $missing_columns[] = 'called_at';
            if (!isset($info['served_by'])) $missing_columns[] = 'served_by';
            if (!isset($info['served_at'])) $missing_columns[] = 'served_at';
            
            if (!empty($missing_columns)) {
                echo "<span style='color:red;'>✗ Missing columns: " . implode(', ', $missing_columns) . "</span><br>";
            } else {
                echo "<span style='color:green;'>✓ All required columns exist</span><br>";
            }
            
            // Try the update (don't actually execute, just show the query)
            $query = "
                UPDATE queue_tickets
                SET status = 'serving', called_at = NOW(), served_at = NOW(), served_by = ?
                WHERE id = ?
            ";
            echo "<p><strong>Query that would execute:</strong></p>";
            echo "<pre>$query</pre>";
            echo "<p><strong>Parameters:</strong> user_id=" . $_SESSION['user_id'] . ", ticket_id=" . $ticket['id'] . "</p>";
            
        } catch (Exception $e) {
            echo "<span style='color:red;'>✗ Error: " . $e->getMessage() . "</span>";
        }
    } else {
        echo "<span style='color:orange;'>ℹ No waiting tickets found to test</span>";
    }
} else {
    echo "<span style='color:orange;'>ℹ No active services found</span>";
}

echo "<hr>";
echo "<h2>Conclusion</h2>";
echo "<p>Run the tests above to identify issues with the callNextTicket function.</p>";
?>
