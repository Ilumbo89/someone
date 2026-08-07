<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (!is_student()) {
    redirect_home();
}

$errors = [];
$success = null;

$teachers = $db->query('SELECT id, name FROM users WHERE role = "teacher" ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'] ?? null;
    $request_text = trim($_POST['request_text'] ?? '');

    if (!$teacher_id) {
        $errors[] = 'Please choose a teacher.';
    } elseif ($request_text === '') {
        $errors[] = 'Please describe your permission request.';
    } else {
        $stmt = $db->prepare('INSERT INTO permission_requests (student_id, teacher_id, request_text) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $teacher_id, $request_text]);
        $success = 'Permission request sent successfully.';
    }
}

$requests = $db->prepare(
    'SELECT pr.*, u.name AS teacher_name, pr.status, pr.response_text
     FROM permission_requests pr
     JOIN users u ON pr.teacher_id = u.id
     WHERE pr.student_id = ?
     ORDER BY pr.created_at DESC'
);
$requests->execute([$_SESSION['user_id']]);
$requests = $requests->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../templates/side_nav.php'; ?>
    <section class="dashboard-main">
        <div class="card">
            <h2>Permission Requests</h2>
            <p>Send a permission request to a teacher. Teachers and admins can view it, and the teacher can approve or reject it.</p>
            <?php if ($success): ?>
                <div class="alert" style="background:#e6ffed;border-left-color:#34d399;color:#065f46;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
            <?php endif; ?>
            <form method="post">
                <label>Select Teacher</label>
                <select name="teacher_id">
                    <option value="">Choose a teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo (int)$teacher['id']; ?>"><?php echo htmlspecialchars($teacher['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Request Details</label>
                <textarea name="request_text"></textarea>
                <button type="submit">Send Request</button>
            </form>
        </div>

        <div class="card">
            <h3>Your Requests</h3>
            <?php if ($requests): ?>
                <table class="table">
                    <thead>
                        <tr><th>Teacher</th><th>Requested At</th><th>Status</th><th>Response</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['teacher_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($request['status'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($request['response_text'] ?? 'Pending review')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No requests yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>