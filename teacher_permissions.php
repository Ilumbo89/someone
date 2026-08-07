<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (!is_teacher() && !is_admin()) {
    redirect_home();
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $response_text = trim($_POST['response_text'] ?? '');

    if ($request_id && in_array($status, ['approved', 'rejected'], true)) {
        $stmt = $db->prepare('UPDATE permission_requests SET status = ?, response_text = ? WHERE id = ?');
        $stmt->execute([$status, $response_text, $request_id]);
        $success = 'Request updated successfully.';
    } else {
        $errors[] = 'Invalid request update.';
    }
}

$query = 'SELECT pr.*, s.name AS student_name, s.email AS student_email, t.name AS teacher_name
          FROM permission_requests pr
          JOIN users s ON pr.student_id = s.id
          JOIN users t ON pr.teacher_id = t.id
          ORDER BY pr.created_at DESC';
$requests = $db->query($query)->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../templates/side_nav.php'; ?>
    <section class="dashboard-main">
        <div class="card">
            <h2>Permission Requests</h2>
            <p>Teachers review and approve or reject student permission requests. Admins can also view them.</p>
            <?php if ($success): ?>
                <div class="alert" style="background:#e6ffed;border-left-color:#34d399;color:#065f46;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
            <?php endif; ?>
            <?php if ($requests): ?>
                <table class="table">
                    <thead>
                        <tr><th>Student</th><th>Teacher</th><th>Request</th><th>Status</th><th>Response</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['teacher_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($request['request_text'])); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($request['status'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($request['response_text'] ?? '')); ?></td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <input type="text" name="response_text" placeholder="Response">
                                            <button type="submit">Approve</button>
                                        </form>
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="text" name="response_text" placeholder="Response">
                                            <button type="submit" style="background:#dc2626;">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span>Handled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No permission requests found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>