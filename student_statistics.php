<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (!is_teacher() && !is_admin()) {
    redirect_home();
}

$stmt = $db->prepare(
    'SELECT u.id, u.name, u.email,
            COUNT(DISTINCT CASE WHEN a.user_id IS NOT NULL THEN a.assignment_id END) AS completed_assignments,
            COUNT(DISTINCT CASE WHEN q.user_id IS NOT NULL THEN q.quiz_id END) AS completed_quizzes
     FROM users u
     LEFT JOIN assignment_submissions a ON a.user_id = u.id
     LEFT JOIN quiz_attempts q ON q.user_id = u.id
     WHERE u.role = ?
     GROUP BY u.id, u.name, u.email
     ORDER BY u.name ASC'
);
$stmt->execute(['student']);
$students = $stmt->fetchAll();

include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../templates/side_nav.php'; ?>
    <section class="dashboard-main">
        <div class="card">
            <h2>Student Statistics</h2>
            <p>Track how many assignments and quizzes students have completed and review their results.</p>
        </div>

        <div class="card">
            <h3>Student Progress</h3>
            <?php if ($students): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Assignments Completed</th>
                            <th>Quizzes Completed</th>
                            <th>Results</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo (int)$student['completed_assignments']; ?></td>
                                <td><?php echo (int)$student['completed_quizzes']; ?></td>
                                <td>
                                    <?php
                                    $assignmentStmt = $db->prepare(
                                        'SELECT a.title, s.mark, s.grade
                                         FROM assignment_submissions s
                                         JOIN assignments a ON s.assignment_id = a.id
                                         WHERE s.user_id = ?
                                         ORDER BY s.submitted_at DESC'
                                    );
                                    $assignmentStmt->execute([$student['id']]);
                                    $assignments = $assignmentStmt->fetchAll();

                                    $quizStmt = $db->prepare(
                                        'SELECT q.title, qa.mark, qa.grade
                                         FROM quiz_attempts qa
                                         JOIN quizzes q ON qa.quiz_id = q.id
                                         WHERE qa.user_id = ?
                                         ORDER BY qa.finished_at DESC'
                                    );
                                    $quizStmt->execute([$student['id']]);
                                    $quizzes = $quizStmt->fetchAll();
                                    ?>
                                    <strong>Assignments:</strong>
                                    <?php if ($assignments): ?>
                                        <ul>
                                            <?php foreach ($assignments as $assignment): ?>
                                                <li><?php echo htmlspecialchars($assignment['title']); ?> — Mark: <?php echo $assignment['mark'] !== null ? htmlspecialchars((string)$assignment['mark']) . '/100' : 'Pending'; ?>, Grade: <?php echo htmlspecialchars($assignment['grade'] ?? 'Pending'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No assignments submitted.</p>
                                    <?php endif; ?>

                                    <strong>Quizzes:</strong>
                                    <?php if ($quizzes): ?>
                                        <ul>
                                            <?php foreach ($quizzes as $quiz): ?>
                                                <li><?php echo htmlspecialchars($quiz['title']); ?> — Mark: <?php echo $quiz['mark'] !== null ? htmlspecialchars((string)$quiz['mark']) . '/100' : 'Pending'; ?>, Grade: <?php echo htmlspecialchars($quiz['grade'] ?? 'Pending'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No quizzes completed.</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No students found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>