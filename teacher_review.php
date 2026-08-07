<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_teacher()) {
    redirect_home();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'] ?? null;
    $mark = trim($_POST['mark'] ?? '');
    $feedback = trim($_POST['feedback'] ?? '');

    if ($submission_id && $mark !== '') {
        if (!is_numeric($mark) || (int)$mark < 0 || (int)$mark > 100) {
            $errors[] = 'Mark must be a number between 0 and 100.';
        } else {
            $grade = calculate_grade_from_mark((int)$mark);
            $stmt = $db->prepare(
                'UPDATE assignment_submissions s
                 JOIN assignments a ON s.assignment_id = a.id
                 SET s.mark = ?, s.grade = ?, s.feedback = ?
                 WHERE s.id = ? AND a.created_by = ?'
            );
            $stmt->execute([(int)$mark, $grade, $feedback, $submission_id, $_SESSION['user_id']]);
            if ($stmt->rowCount() === 0) {
                $errors[] = 'Unable to update submission. Please refresh and try again.';
            }
        }
    } else {
        $errors[] = 'Mark is required.';
    }
}

$stmt = $db->prepare(
    'SELECT s.*, a.title AS assignment_title, u.name AS student_name
     FROM assignment_submissions s
     JOIN assignments a ON s.assignment_id = a.id
     JOIN users u ON s.user_id = u.id
     WHERE a.created_by = ?
     ORDER BY s.submitted_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Review Submissions</h2>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <p>Teachers can review student assignments, enter a mark out of 100, and leave feedback. The system will assign the grade automatically.</p>
    <?php if ($submissions): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Student</th>
                    <th>Submitted At</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Feedback</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                        <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($submission['submitted_at']); ?></td>
                        <td><?php echo $submission['mark'] !== null ? htmlspecialchars((string)$submission['mark']) . '/100' : 'Pending'; ?></td>
                        <td><?php echo htmlspecialchars($submission['grade'] ?? 'Pending'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($submission['feedback'] ?? '')); ?></td>
                        <td>
                            <form method="post" style="display:inline-block; min-width:320px;">
                                <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                                <label>Mark</label>
                                <input type="number" name="mark" min="0" max="100" placeholder="Mark" value="<?php echo htmlspecialchars((string)($submission['mark'] ?? '')); ?>">
                                <label>Feedback</label>
                                <input type="text" name="feedback" placeholder="Feedback" value="<?php echo htmlspecialchars($submission['feedback'] ?? ''); ?>">
                                <button type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No submissions available yet.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
