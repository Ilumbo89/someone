<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }
        $errors[] = 'Invalid email or password.';
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Login</h2>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <label>Password</label>
        <input type="password" name="password">
        <button type="submit">Login</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
