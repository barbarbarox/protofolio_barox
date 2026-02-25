<?php
// Admin front controller - redirect appropriately
require_once '../config/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
