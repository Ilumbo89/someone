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
    $action = $_POST['action'] ?? null;

    if ($action === 'update_user_role') {
        $user_id = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? '';
        if ($user_id && in_array($role, ['student', 'teacher', 'admin'], true)) {
            $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$role, $user_id]);
            $success = 'User role updated successfully.';
        } else {
            $errors[] = 'Invalid user or role.';
        }
    }

    if ($action === 'delete_user') {
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id && $user_id != $_SESSION['user_id']) {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $success = 'User deleted successfully.';
        } else {
            $errors[] = 'You cannot delete your own admin account.';
        }
    }

    if ($action === 'delete_assignment') {
        $assignment_id = $_POST['assignment_id'] ?? null;
        if ($assignment_id) {
            $stmt = $db->prepare('DELETE FROM assignments WHERE id = ?');
            $stmt->execute([$assignment_id]);
            $success = 'Assignment deleted successfully.';
        }
    }

    if ($action === 'delete_quiz') {
        $quiz_id = $_POST['quiz_id'] ?? null;
        if ($quiz_id) {
            $stmt = $db->prepare('DELETE FROM quizzes WHERE id = ?');
            $stmt->execute([$quiz_id]);
            $success = 'Quiz deleted successfully.';
        }
    }
}

$users = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$assignments = $db->query('SELECT a.id, a.title, a.due_date, a.created_at, u.name AS author FROM assignments a JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC')->fetchAll();
$quizzes = $db->query('SELECT q.id, q.title, q.created_at, u.name AS author FROM quizzes q JOIN users u ON q.created_by = u.id ORDER BY q.created_at DESC')->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Admin Panel</h2>
    <?php if ($success): ?>
        <div class="alert" style="background:#e6ffed;border-left-color:#34d399;color:#065f46;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <p>Manage users, assignments, and quizzes from one place.</p>
    <p>
        <a class="button" href="admin_create_user.php">Create New User</a>
        <a class="button" href="admin_reset_password.php">Reset Student/Teacher Password</a>
    </p>
</div>

<div class="card">
    <h3>Users</h3>
    <?php if ($users): ?>
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <form method="post" style="display:inline-block; margin-right:8px;">
                                <input type="hidden" name="action" value="update_user_role">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role">
                                    <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                    <option value="teacher" <?php echo $user['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="post" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" style="background:#dc2626;">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Assignments</h3>
    <?php if ($assignments): ?>
        <table class="table">
            <thead>
                <tr><th>Title</th><th>Author</th><th>Due Date</th><th>Created At</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['author']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['created_at']); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="delete_assignment">
                                <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                <button type="submit" style="background:#dc2626;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignments found.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Quizzes</h3>
    <?php if ($quizzes): ?>
        <table class="table">
            <thead>
                <tr><th>Title</th><th>Author</th><th>Created At</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['author']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['created_at']); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="delete_quiz">
                                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                <button type="submit" style="background:#dc2626;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quizzes found.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
