<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_teacher()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $time_limit = is_numeric($_POST['time_limit'] ?? '') ? (int)$_POST['time_limit'] : null;

    if ($title === '' || $description === '' || $due_date === '') {
        $errors[] = 'All fields are required.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO assignments (title, description, due_date, time_limit, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $due_date, $time_limit, $_SESSION['user_id']]);
        header('Location: dashboard.php');
        exit;
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Create Assignment</h2>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>">
        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        <label>Due Date</label>
        <input type="date" name="due_date" value="<?php echo htmlspecialchars($due_date ?? ''); ?>">
        <label>Time limit (minutes, optional)</label>
        <input type="text" name="time_limit" value="<?php echo htmlspecialchars($time_limit ?? ''); ?>" placeholder="e.g. 60">
        <button type="submit">Create assignment</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
