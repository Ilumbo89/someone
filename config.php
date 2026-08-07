<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'university_portal');

define('SITE_NAME', 'University Portal');
define('SITE_URL', 'http://localhost/university-portal/public/');
define('UPLOAD_DIR', '../uploads/');
define('ADMIN_SECRET', 'supersecretadmincode');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
