<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_student()) {
    redirect_home();
}

$stmt = $db->prepare(
    'SELECT a.title AS assignment_title, s.submission_text, s.file_path, s.submitted_at, s.mark, s.grade, s.feedback
     FROM assignment_submissions s
     JOIN assignments a ON s.assignment_id = a.id
     WHERE s.user_id = ?
     ORDER BY s.submitted_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll();

$stmt = $db->prepare(
    'SELECT q.title AS quiz_title, qa.score, qa.total_questions, qa.finished_at
     FROM quiz_attempts qa
     JOIN quizzes q ON qa.quiz_id = q.id
     WHERE qa.user_id = ?
     ORDER BY qa.finished_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$attempts = $stmt->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Submission History</h2>
    <?php if ($submissions): ?>
        <table class="table">
            <thead>
                <tr><th>Assignment</th><th>Submitted At</th><th>Mark</th><th>Grade</th><th>Feedback</th><th>File</th></tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                        <td><?php echo htmlspecialchars($submission['submitted_at']); ?></td>
                        <td><?php echo $submission['mark'] !== null ? htmlspecialchars((string)$submission['mark']) . '/100' : 'Pending'; ?></td>
                        <td><?php echo htmlspecialchars($submission['grade'] ?? 'Pending'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($submission['feedback'] ?? '')); ?></td>
                        <td>
                            <?php if ($submission['file_path']): ?>
                                <a href="download.php?file=<?php echo urlencode($submission['file_path']); ?>">Download</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignment submissions found.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Quiz History</h2>
    <?php if ($attempts): ?>
        <table class="table">
            <thead>
                <tr><th>Quiz</th><th>Score</th><th>Mark</th><th>Grade</th><th>Finished At</th></tr>
            </thead>
            <tbody>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                        <td><?php echo htmlspecialchars($attempt['score']); ?> / <?php echo htmlspecialchars($attempt['total_questions']); ?></td>
                        <td><?php echo $attempt['mark'] !== null ? htmlspecialchars((string)$attempt['mark']) . '/100' : 'Pending'; ?></td>
                        <td><?php echo htmlspecialchars($attempt['grade'] ?? 'Pending'); ?></td>
                        <td><?php echo htmlspecialchars($attempt['finished_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quiz attempts found.</p>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
