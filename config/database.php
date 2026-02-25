<?php
// ============================================
// Database Configuration (Secured)
// ============================================

// Prevent direct browser access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    die('Access denied.');
}

define('DB_HOST', 'sql100.infinityfree.com');
define('DB_NAME', 'if0_41244839_portfolio_db');
define('DB_USER', 'if0_41244839');
define('DB_PASS', 'xIJxgmANLm');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Don't expose database error details in production
            error_log("Database connection failed: " . $e->getMessage());
            die("A system error occurred. Please try again later.");
        }
    }
    return $pdo;
}
