<?php
require_once '../config/auth.php';
setSecurityHeaders();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (isLoginLocked($username)) {
        $remaining = getRemainingLockoutTime();
        $error = "Too many login attempts. Account locked for {$remaining} more minute(s).";
    } elseif (loginAdmin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        // Check if now locked after this attempt
        if (isLoginLocked($username)) {
            $error = 'Too many failed attempts. Account locked for ' . LOGIN_LOCKOUT_MINUTES . ' minutes.';
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    <div class="logo-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h1>Admin Panel</h1>
                    <p>Sign in to manage your portfolio</p>
                </div>

                <?php if ($error): ?>
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="login-form" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" id="username" placeholder="Enter username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus
                                   maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Enter password" required
                                   maxlength="128">
                        </div>
                    </div>
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="login-footer">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
