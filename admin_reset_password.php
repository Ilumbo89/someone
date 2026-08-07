<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) {
    redirect_home();
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $new_password = $_POST['new_password'] ?? '';

    if (!$user_id) {
        $errors[] = 'Please select a user.';
    } elseif ($new_password === '') {
        $errors[] = 'Password cannot be empty.';
    } else {
        $stmt = $db->prepare('SELECT id, role FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors[] = 'User not found.';
        } elseif (!in_array($user['role'], ['student', 'teacher'], true)) {
            $errors[] = 'Only student and teacher accounts can be reset from this page.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([$hash, $user_id]);
            $success = 'Password reset successfully.';
        }
    }
}

$users = $db->query('SELECT id, name, email, role FROM users WHERE role IN ("student", "teacher") ORDER BY name ASC')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Reset User Password</h2>
    <?php if ($success): ?>
        <div class="alert" style="background:#e6ffed;border-left-color:#34d399;color:#065f46;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <p>Only administrators can reset student and teacher passwords.</p>
    <form method="post">
        <label>Select User</label>
        <select name="user_id">
            <option value="">Choose a user</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo (int)$user['id']; ?>">
                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ' - ' . $user['role'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>New Password</label>
        <input type="password" name="new_password">
        <button type="submit">Reset Password</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>