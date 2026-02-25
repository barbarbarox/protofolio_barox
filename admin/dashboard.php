<?php
define('ADMIN_PAGE', 'Dashboard');
require_once '../config/auth.php';
requireLogin();

$db = getDB();

$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalSkills = $db->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unreadMessages = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentProjects = $db->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-folder-open"></i></div>
        <div class="stat-info">
            <h3><?= $totalProjects ?></h3>
            <p>Total Projects</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon cyan"><i class="fas fa-code"></i></div>
        <div class="stat-info">
            <h3><?= $totalSkills ?></h3>
            <p>Total Skills</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <h3><?= $totalMessages ?></h3>
            <p>Total Messages</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-info">
            <h3><?= $unreadMessages ?></h3>
            <p>Unread Messages</p>
        </div>
    </div>
</div>

<!-- Recent Messages -->
<div class="admin-table-wrapper" style="margin-bottom: 28px;">
    <div class="table-header">
        <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
        <a href="messages.php" class="btn-admin btn-admin-sm btn-admin-primary">View All</a>
    </div>
    <?php if (empty($recentMessages)): ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No messages yet</h3>
        <p>Messages from your contact form will appear here.</p>
    </div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentMessages as $msg): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($msg['name']) ?></strong><br>
                    <small style="color: var(--admin-text-muted);"><?= htmlspecialchars($msg['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($msg['subject'] ?: '(No Subject)') ?></td>
                <td><?= date('d M Y', strtotime($msg['created_at'])) ?></td>
                <td>
                    <span class="status-badge <?= $msg['is_read'] ? 'read' : 'unread' ?>">
                        <?= $msg['is_read'] ? 'Read' : 'Unread' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Recent Projects -->
<div class="admin-table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-folder-open"></i> Recent Projects</h3>
        <a href="projects.php" class="btn-admin btn-admin-sm btn-admin-primary">View All</a>
    </div>
    <?php if (empty($recentProjects)): ?>
    <div class="empty-state">
        <i class="fas fa-folder"></i>
        <h3>No projects yet</h3>
        <p>Add your first project to get started.</p>
    </div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Tech Stack</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentProjects as $proj): ?>
            <tr>
                <td><strong><?= htmlspecialchars($proj['title']) ?></strong></td>
                <td><?= htmlspecialchars($proj['category']) ?></td>
                <td><?= htmlspecialchars($proj['tech_stack']) ?></td>
                <td>
                    <?php if ($proj['is_featured']): ?>
                    <span class="status-badge featured">Featured</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
