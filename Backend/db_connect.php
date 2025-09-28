<?php
// Database connection helpers for the `mallas_trujillo` database (XAMPP / phpMyAdmin)
// Put this file in Backend/db_connect.php and include/require it where needed.

// Load configuration from project-level config.php (kept outside public)
$configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
if (file_exists($configPath)) {
	require $configPath; // provides $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $SHOW_DB_ERRORS
} else {
	// Fallback defaults (XAMPP typical defaults)
	$DB_HOST = '127.0.0.1';
	$DB_NAME = 'mallas_trujillo';
	$DB_USER = 'root';
	$DB_PASS = '';
	$SHOW_DB_ERRORS = true;
}

// Returns a PDO instance (singleton)
function get_pdo_connection()
{
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
	static $pdo = null;
	if ($pdo !== null) {
		return $pdo;
	}

	$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
	try {
		$pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		]);
		return $pdo;
	} catch (PDOException $e) {
		// In production, log errors instead of echoing them.
		http_response_code(500);
		if (!empty($SHOW_DB_ERRORS)) {
			die('Database connection failed: ' . $e->getMessage());
		}
		die('Database connection failed.');
	}
}

// Note: This project uses PDO only. The mysqli helper was removed to avoid
// mixing APIs. Use get_pdo_connection() above for all database access.

?>

