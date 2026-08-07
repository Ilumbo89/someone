<?php
require_once __DIR__ . '/../includes/auth.php';
$user = current_user();
?>
<aside class="page-sidebar card">
    <h3>Navigation</h3>
    <nav class="page-nav">
        <a class="button" href="dashboard.php">Home</a>
        <?php if ($user && in_array($user['role'], ['teacher', 'admin'], true)): ?>
            <a class="button" href="student_statistics.php">Student Statistics</a>
        <?php endif; ?>
    </nav>
</aside>
