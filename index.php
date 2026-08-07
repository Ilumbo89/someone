<?php
require_once __DIR__ . '/../includes/config.php';
?>
<?php include __DIR__ . '/../templates/header.php'; ?>
<div class="card">
    <h2>Welcome to <?php echo htmlspecialchars(SITE_NAME); ?></h2>
    <p>Admin users create student and teacher accounts. If you already have credentials, please log in.</p>
    <p>
        <a class="button" href="login.php">Login</a>
    </p>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
