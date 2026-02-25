<?php
define('ADMIN_PAGE', 'Profile');
require_once '../config/auth.php';
requireLogin();

$db = getDB();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $cv_link = trim($_POST['cv_link'] ?? '');

    if (empty($name)) {
        $message = 'Name is required.';
        $messageType = 'error';
    } else {
        $check = $db->query("SELECT COUNT(*) FROM profile")->fetchColumn();
        if ($check > 0) {
            $stmt = $db->prepare("UPDATE profile SET name=?, title=?, bio=?, email=?, phone=?, location=?, github=?, linkedin=?, instagram=?, cv_link=? WHERE id=1");
        } else {
            $stmt = $db->prepare("INSERT INTO profile (name, title, bio, email, phone, location, github, linkedin, instagram, cv_link) VALUES (?,?,?,?,?,?,?,?,?,?)");
        }
        $stmt->execute([$name, $title, $bio, $email, $phone, $location, $github, $linkedin, $instagram, $cv_link]);
        $message = 'Profile updated successfully!';
        $messageType = 'success';
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass)) {
        $message = 'Please fill in all password fields.';
        $messageType = 'error';
    } elseif ($newPass !== $confirmPass) {
        $message = 'New passwords do not match.';
        $messageType = 'error';
    } elseif (strlen($newPass) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } else {
        $admin = $db->prepare("SELECT password FROM admin WHERE id = ?");
        $admin->execute([$_SESSION['admin_id']]);
        $adminData = $admin->fetch();

        if (password_verify($currentPass, $adminData['password'])) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $update->execute([$newHash, $_SESSION['admin_id']]);
            $message = 'Password changed successfully!';
            $messageType = 'success';
        } else {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        }
    }
}

$profile = $db->query("SELECT * FROM profile LIMIT 1")->fetch();

require_once 'includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Profile Form -->
<div class="admin-form-card" style="margin-bottom: 28px;">
    <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" name="name" id="name" class="form-control" required
                       value="<?= htmlspecialchars($profile['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="title">Professional Title</label>
                <input type="text" name="title" id="title" class="form-control"
                       placeholder="e.g. Full Stack Developer"
                       value="<?= htmlspecialchars($profile['title'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="bio">Bio / About</label>
            <textarea name="bio" id="bio" class="form-control" rows="5"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control"
                       value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="form-control"
                   placeholder="e.g. Jakarta, Indonesia"
                   value="<?= htmlspecialchars($profile['location'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="github">GitHub URL</label>
                <input type="url" name="github" id="github" class="form-control" placeholder="https://github.com/..."
                       value="<?= htmlspecialchars($profile['github'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="linkedin">LinkedIn URL</label>
                <input type="url" name="linkedin" id="linkedin" class="form-control" placeholder="https://linkedin.com/in/..."
                       value="<?= htmlspecialchars($profile['linkedin'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="instagram">Instagram URL</label>
                <input type="url" name="instagram" id="instagram" class="form-control" placeholder="https://instagram.com/..."
                       value="<?= htmlspecialchars($profile['instagram'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="cv_link">CV Download Link</label>
                <input type="url" name="cv_link" id="cv_link" class="form-control" placeholder="https://..."
                       value="<?= htmlspecialchars($profile['cv_link'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn-admin btn-admin-primary" style="margin-top: 8px;">
            <i class="fas fa-save"></i> Save Profile
        </button>
    </form>
</div>

<!-- Change Password -->
<div class="admin-form-card">
    <h3><i class="fas fa-lock"></i> Change Password</h3>
    <form method="POST">
        <input type="hidden" name="change_password" value="1">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn-admin btn-admin-primary" style="margin-top: 8px;">
            <i class="fas fa-key"></i> Change Password
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
