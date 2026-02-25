<?php
define('ADMIN_PAGE', 'Messages');
require_once '../config/auth.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: messages.php?msg=deleted');
    exit;
}

// Handle mark as read
if ($action === 'read' && $id) {
    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: messages.php?msg=marked');
    exit;
}

// Handle mark all as read
if ($action === 'read-all') {
    $db->exec("UPDATE messages SET is_read = 1");
    header('Location: messages.php?msg=all_read');
    exit;
}

// Flash messages
if (isset($_GET['msg'])) {
    $msgs = ['deleted' => 'Message deleted.', 'marked' => 'Marked as read.', 'all_read' => 'All messages marked as read.'];
    $message = $msgs[$_GET['msg']] ?? '';
    $messageType = 'success';
}

// View single message
$viewMessage = null;
if ($action === 'view' && $id) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $viewMessage = $stmt->fetch();
    if ($viewMessage && !$viewMessage['is_read']) {
        $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        $viewMessage['is_read'] = 1;
    }
}

$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

require_once 'includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <i class="fas fa-check-circle"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($viewMessage): ?>
<!-- View Single Message -->
<div class="message-card" style="max-width: 700px;">
    <div class="message-header">
        <div>
            <div class="message-sender"><?= htmlspecialchars($viewMessage['name']) ?></div>
            <div class="message-email"><?= htmlspecialchars($viewMessage['email']) ?></div>
        </div>
        <div class="message-date"><?= date('d M Y, H:i', strtotime($viewMessage['created_at'])) ?></div>
    </div>
    <?php if ($viewMessage['subject']): ?>
    <div class="message-subject"><?= htmlspecialchars($viewMessage['subject']) ?></div>
    <?php endif; ?>
    <div class="message-body"><?= nl2br(htmlspecialchars($viewMessage['message'])) ?></div>
    <div style="margin-top: 20px; display: flex; gap: 10px;">
        <a href="mailto:<?= htmlspecialchars($viewMessage['email']) ?>" class="btn-admin btn-admin-primary btn-admin-sm">
            <i class="fas fa-reply"></i> Reply via Email
        </a>
        <button onclick="confirmDelete('messages.php?action=delete&id=<?= $viewMessage['id'] ?>')" class="btn-admin btn-admin-sm btn-delete">
            <i class="fas fa-trash"></i> Delete
        </button>
        <a href="messages.php" class="btn-admin btn-admin-sm btn-cancel">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<?php else: ?>
<!-- Messages List -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px; gap: 10px;">
    <?php if (count($messages) > 0): ?>
    <a href="messages.php?action=read-all" class="btn-admin btn-admin-sm btn-view">
        <i class="fas fa-check-double"></i> Mark All Read
    </a>
    <?php endif; ?>
</div>

<?php if (empty($messages)): ?>
<div class="admin-table-wrapper">
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No messages yet</h3>
        <p>Messages from your contact form will appear here.</p>
    </div>
</div>
<?php else: ?>
<?php foreach ($messages as $msg): ?>
<div class="message-card <?= !$msg['is_read'] ? 'unread' : '' ?>">
    <div class="message-header">
        <div>
            <div class="message-sender">
                <?= htmlspecialchars($msg['name']) ?>
                <?php if (!$msg['is_read']): ?>
                <span class="status-badge unread" style="margin-left: 8px;">New</span>
                <?php endif; ?>
            </div>
            <div class="message-email"><?= htmlspecialchars($msg['email']) ?></div>
        </div>
        <div class="message-date"><?= date('d M Y, H:i', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php if ($msg['subject']): ?>
    <div class="message-subject"><?= htmlspecialchars($msg['subject']) ?></div>
    <?php endif; ?>
    <div class="message-body"><?= htmlspecialchars(substr($msg['message'], 0, 200)) ?><?= strlen($msg['message']) > 200 ? '...' : '' ?></div>
    <div style="margin-top: 14px; display: flex; gap: 8px;">
        <a href="messages.php?action=view&id=<?= $msg['id'] ?>" class="btn-admin btn-admin-sm btn-view">
            <i class="fas fa-eye"></i> View
        </a>
        <?php if (!$msg['is_read']): ?>
        <a href="messages.php?action=read&id=<?= $msg['id'] ?>" class="btn-admin btn-admin-sm btn-edit">
            <i class="fas fa-check"></i> Mark Read
        </a>
        <?php endif; ?>
        <button onclick="confirmDelete('messages.php?action=delete&id=<?= $msg['id'] ?>')" class="btn-admin btn-admin-sm btn-delete">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
