<?php
// ============================================
// Authentication & Security (Hardened)
// ============================================

// Prevent direct browser access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    die('Access denied.');
}

// --- Session Security Configuration ---
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

// Use secure cookies if HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

require_once __DIR__ . '/database.php';

// --- Security Constants ---
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('SESSION_TIMEOUT_SECONDS', 1800); // 30 minutes
define('MAX_CONTACT_PER_HOUR', 3);

// --- Security Headers ---
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header_remove('X-Powered-By');
}

// --- Get Client IP ---
function getClientIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// --- Session Management ---
function isLoggedIn() {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS) {
            // Session expired
            destroySession();
            return false;
        }
    }

    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

function requireLogin() {
    setSecurityHeaders();
    if (!isLoggedIn()) {
        // Build redirect URL relative to admin/
        header('Location: login.php');
        exit;
    }

    // Regenerate session ID periodically (every 5 minutes)
    if (!isset($_SESSION['regenerated_at']) || time() - $_SESSION['regenerated_at'] > 300) {
        session_regenerate_id(true);
        $_SESSION['regenerated_at'] = time();
    }
}

// --- Login Rate Limiting ---
function isLoginLocked($username = null) {
    $db = getDB();
    $ip = getClientIP();
    $cutoff = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60));

    // Check by IP
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > ? AND is_successful = 0");
    $stmt->execute([$ip, $cutoff]);
    $ipAttempts = $stmt->fetchColumn();

    if ($ipAttempts >= MAX_LOGIN_ATTEMPTS) {
        return true;
    }

    // Check by username if provided
    if ($username) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND attempted_at > ? AND is_successful = 0");
        $stmt->execute([$username, $cutoff]);
        $userAttempts = $stmt->fetchColumn();

        if ($userAttempts >= MAX_LOGIN_ATTEMPTS) {
            return true;
        }
    }

    return false;
}

function recordLoginAttempt($username, $success) {
    $db = getDB();
    $ip = getClientIP();
    $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, username, is_successful) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $username, $success ? 1 : 0]);
}

function getRemainingLockoutTime() {
    $db = getDB();
    $ip = getClientIP();
    $cutoff = date('Y-m-d H:i:s', time() - (LOGIN_LOCKOUT_MINUTES * 60));

    $stmt = $db->prepare("SELECT MAX(attempted_at) FROM login_attempts WHERE ip_address = ? AND attempted_at > ? AND is_successful = 0");
    $stmt->execute([$ip, $cutoff]);
    $lastAttempt = $stmt->fetchColumn();

    if ($lastAttempt) {
        $unlockTime = strtotime($lastAttempt) + (LOGIN_LOCKOUT_MINUTES * 60);
        $remaining = $unlockTime - time();
        return max(0, ceil($remaining / 60));
    }
    return 0;
}

// --- Login ---
function loginAdmin($username, $password) {
    // Check rate limiting
    if (isLoginLocked($username)) {
        return false;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        // Successful login
        recordLoginAttempt($username, true);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['last_activity'] = time();
        $_SESSION['regenerated_at'] = time();
        $_SESSION['login_ip'] = getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return true;
    }

    // Failed login
    recordLoginAttempt($username, false);
    return false;
}

// --- Logout ---
function logoutAdmin() {
    destroySession();
    header('Location: login.php');
    exit;
}

function destroySession() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

// --- CSRF Protection ---
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// --- Contact Form Rate Limiting ---
function isContactRateLimited() {
    $db = getDB();
    $ip = getClientIP();
    $oneHourAgo = date('Y-m-d H:i:s', time() - 3600);

    $stmt = $db->prepare("SELECT COUNT(*) FROM contact_rate_limit WHERE ip_address = ? AND created_at > ?");
    $stmt->execute([$ip, $oneHourAgo]);
    $count = $stmt->fetchColumn();

    return $count >= MAX_CONTACT_PER_HOUR;
}

function recordContactAttempt() {
    $db = getDB();
    $ip = getClientIP();
    $stmt = $db->prepare("INSERT INTO contact_rate_limit (ip_address) VALUES (?)");
    $stmt->execute([$ip]);
}

// --- Input Sanitization ---
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

// --- Validate Session Integrity ---
function validateSession() {
    // Check if IP changed (possible session hijack)
    if (isset($_SESSION['login_ip']) && $_SESSION['login_ip'] !== getClientIP()) {
        destroySession();
        return false;
    }

    // Check if user agent changed
    $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $currentUA) {
        destroySession();
        return false;
    }

    return true;
}

// --- Cleanup Old Records ---
function cleanupOldRecords() {
    // Run cleanup ~1% of requests to avoid performance impact
    if (mt_rand(1, 100) === 1) {
        $db = getDB();
        $oneDayAgo = date('Y-m-d H:i:s', time() - 86400);
        $db->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$oneDayAgo]);
        $db->prepare("DELETE FROM contact_rate_limit WHERE created_at < ?")->execute([$oneDayAgo]);
    }
}

cleanupOldRecords();
