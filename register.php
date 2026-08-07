<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Registration Closed</h2>
    <p>Student and teacher accounts must be created by an administrator.</p>
    <p>Please contact your admin to request access.</p>
    <?php if (is_logged_in() && is_admin()): ?>
        <p><a class="button" href="admin_create_user.php">Create a new user</a></p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
