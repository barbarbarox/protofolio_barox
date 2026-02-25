<?php
if (!defined('ADMIN_PAGE')) define('ADMIN_PAGE', '');

$db = getDB();
$unreadCount = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ADMIN_PAGE ?> | Admin Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <span class="logo-dot"></span>
                    Portfolio Admin
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-label">Main</div>
                <a href="dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="nav-label">Content</div>
                <a href="projects.php" class="sidebar-link <?= $currentPage === 'projects' ? 'active' : '' ?>">
                    <i class="fas fa-folder-open"></i>
                    <span>Projects</span>
                </a>
                <a href="skills.php" class="sidebar-link <?= $currentPage === 'skills' ? 'active' : '' ?>">
                    <i class="fas fa-code"></i>
                    <span>Skills</span>
                </a>
                <a href="profile.php" class="sidebar-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user-edit"></i>
                    <span>Profile</span>
                </a>
                <a href="messages.php" class="sidebar-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>

                <div class="nav-label">Other</div>
                <a href="../index.php" class="sidebar-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Site</span>
                </a>
                <a href="logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h2><?= ADMIN_PAGE ?></h2>
                <div class="header-actions">
                    <div class="header-user">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                    </div>
                </div>
            </header>
            <div class="admin-content">
