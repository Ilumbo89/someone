<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) {
    redirect_home();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['student', 'teacher', 'admin'], true) ? $_POST['role'] : 'student';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, $role]);
        $success = 'User created successfully.';
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Create New User</h2>
    <p>Passwords are stored securely. Do not insert users manually into the database with plain-text passwords.</p>
    <?php if (!empty($errors)): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert" style="background:#e6ffed;border-left-color:#34d399;color:#065f46;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <label>Password</label>
        <input type="password" name="password">
        <label>Role</label>
        <select name="role">
            <option value="student" <?php echo (isset($role) && $role === 'student') ? 'selected' : ''; ?>>Student</option>
            <option value="teacher" <?php echo (isset($role) && $role === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
            <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
        <button type="submit">Create User</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
