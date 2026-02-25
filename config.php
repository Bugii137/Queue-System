<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Define constants only if not already defined (safe re-includes)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'queue_system_db'); // your DB name
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

if (!defined('SITE_NAME')) define('SITE_NAME', 'Smart Queue Management System');
if (!defined('TICKET_PREFIX')) define('TICKET_PREFIX', 'Q');
if (!defined('ESTIMATED_TIME_PER_TICKET')) define('ESTIMATED_TIME_PER_TICKET', 5);
if (!defined('AUTO_REFRESH_INTERVAL')) define('AUTO_REFRESH_INTERVAL', 10);

require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
?>
