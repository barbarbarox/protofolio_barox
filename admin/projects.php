<?php
define('ADMIN_PAGE', 'Projects');
require_once '../config/auth.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Handle delete
if ($action === 'delete' && $id) {
    $token = $_GET['token'] ?? '';
    if (!verifyCSRFToken($token)) {
        header('Location: projects.php?msg=csrf_error');
        exit;
    }
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: projects.php?msg=deleted');
    exit;
}

// Handle add/edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please try again.';
        $messageType = 'error';
    } else {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tech_stack = trim($_POST['tech_stack'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $github_link = trim($_POST['github_link'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $editId = $_POST['id'] ?? null;

    if (empty($title)) {
        $message = 'Project title is required.';
        $messageType = 'error';
    } else {
        if ($editId) {
            $stmt = $db->prepare("UPDATE projects SET title=?, description=?, category=?, tech_stack=?, link=?, github_link=?, is_featured=? WHERE id=?");
            $stmt->execute([$title, $description, $category, $tech_stack, $link, $github_link, $is_featured, $editId]);
            header('Location: projects.php?msg=updated');
            exit;
        } else {
            $stmt = $db->prepare("INSERT INTO projects (title, description, category, tech_stack, link, github_link, is_featured) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $description, $category, $tech_stack, $link, $github_link, $is_featured]);
            header('Location: projects.php?msg=added');
            exit;
        }
    }
    }
}

// Flash messages
if (isset($_GET['msg'])) {
    $msgs = ['added' => 'Project added successfully!', 'updated' => 'Project updated!', 'deleted' => 'Project deleted.', 'csrf_error' => 'Security token invalid.'];
    $message = $msgs[$_GET['msg']] ?? '';
    $messageType = ($_GET['msg'] === 'csrf_error') ? 'error' : 'success';
}

// Fetch data for edit form
$editProject = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $editProject = $stmt->fetch();
    if (!$editProject) {
        header('Location: projects.php');
        exit;
    }
}

$projects = $db->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

require_once 'includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Add/Edit Form -->
<div class="admin-form-card">
    <h3><i class="fas fa-<?= $action === 'edit' ? 'pen' : 'plus' ?>"></i> <?= $action === 'edit' ? 'Edit' : 'Add New' ?> Project</h3>
    <form method="POST">
        <?= csrfField() ?>
        <?php if ($editProject): ?>
        <input type="hidden" name="id" value="<?= $editProject['id'] ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="title">Project Title *</label>
                <input type="text" name="title" id="title" class="form-control" required
                       value="<?= htmlspecialchars($editProject['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control">
                    <option value="Web App" <?= ($editProject['category'] ?? '') === 'Web App' ? 'selected' : '' ?>>Web App</option>
                    <option value="Website" <?= ($editProject['category'] ?? '') === 'Website' ? 'selected' : '' ?>>Website</option>
                    <option value="Mobile App" <?= ($editProject['category'] ?? '') === 'Mobile App' ? 'selected' : '' ?>>Mobile App</option>
                    <option value="UI/UX" <?= ($editProject['category'] ?? '') === 'UI/UX' ? 'selected' : '' ?>>UI/UX</option>
                    <option value="Other" <?= ($editProject['category'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($editProject['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="tech_stack">Tech Stack (comma separated)</label>
            <input type="text" name="tech_stack" id="tech_stack" class="form-control"
                   placeholder="e.g. PHP, MySQL, JavaScript"
                   value="<?= htmlspecialchars($editProject['tech_stack'] ?? '') ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="link">Live Demo Link</label>
                <input type="url" name="link" id="link" class="form-control" placeholder="https://..."
                       value="<?= htmlspecialchars($editProject['link'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="github_link">GitHub Link</label>
                <input type="url" name="github_link" id="github_link" class="form-control" placeholder="https://..."
                       value="<?= htmlspecialchars($editProject['github_link'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1" 
                       <?= ($editProject['is_featured'] ?? 0) ? 'checked' : '' ?>
                       style="width: 18px; height: 18px; accent-color: var(--admin-accent);">
                Featured Project
            </label>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 8px;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Update' : 'Add' ?> Project
            </button>
            <a href="projects.php" class="btn-admin btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Projects List -->
<div class="admin-table-wrapper">
    <div class="table-header">
        <h3>All Projects (<?= count($projects) ?>)</h3>
        <a href="projects.php?action=add" class="btn-admin btn-admin-primary">
            <i class="fas fa-plus"></i> Add Project
        </a>
    </div>
    <?php if (empty($projects)): ?>
    <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        <h3>No projects yet</h3>
        <p>Click "Add Project" to create your first project.</p>
    </div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Tech Stack</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $i => $proj): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($proj['title']) ?></strong></td>
                <td><?= htmlspecialchars($proj['category']) ?></td>
                <td><?= htmlspecialchars($proj['tech_stack']) ?></td>
                <td>
                    <?php if ($proj['is_featured']): ?>
                    <span class="status-badge featured">Featured</span>
                    <?php else: ?>
                    <span style="color: var(--admin-text-muted); font-size: 0.82rem;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="projects.php?action=edit&id=<?= $proj['id'] ?>" class="btn-admin btn-admin-sm btn-edit"><i class="fas fa-pen"></i></a>
                        <button onclick="confirmDelete('projects.php?action=delete&id=<?= $proj['id'] ?>&token=<?= htmlspecialchars(generateCSRFToken()) ?>')" class="btn-admin btn-admin-sm btn-delete"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
