<?php
header('Content-Type: application/json');
require_once '../config/auth.php';
setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// --- Honeypot check (anti-bot) ---
if (!empty($_POST['website_url'])) {
    // Bot detected - silently appear successful
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    exit;
}

// --- Rate limiting ---
if (isContactRateLimited()) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many messages. Please try again later (max 3 per hour).']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// --- Validation ---
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (strlen($name) > 100 || strlen($email) > 100 || strlen($subject) > 200 || strlen($message) > 5000) {
    echo json_encode(['success' => false, 'message' => 'Input exceeds maximum length.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Block common spam patterns
$spamPatterns = ['http://', 'https://', '<script', 'javascript:', 'onclick=', 'onerror='];
foreach ($spamPatterns as $pattern) {
    if (stripos($message, $pattern) !== false || stripos($name, $pattern) !== false) {
        echo json_encode(['success' => false, 'message' => 'Invalid content detected.']);
        exit;
    }
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        $email,
        htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
    ]);
    
    // Record contact attempt for rate limiting
    recordContactAttempt();
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully! I will get back to you soon.']);
} catch (PDOException $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
}
