<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied.');
}

$path = $_GET['file'] ?? '';
$baseDir = realpath(__DIR__ . '/../uploads');
$file = realpath(__DIR__ . '/../' . $path);

if (!$file || strpos($file, $baseDir) !== 0 || !is_file($file)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
