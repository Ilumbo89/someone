<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user();

if ($user['role'] === 'teacher') {
    $assignments = $db->prepare('SELECT * FROM assignments WHERE created_by = ? ORDER BY created_at DESC');
    $assignments->execute([$user['id']]);
    $quizzes = $db->prepare('SELECT * FROM quizzes WHERE created_by = ? ORDER BY created_at DESC');
    $quizzes->execute([$user['id']]);
} elseif ($user['role'] === 'admin') {
    $assignments = $db->query('SELECT * FROM assignments ORDER BY created_at DESC');
    $quizzes = $db->query('SELECT * FROM quizzes ORDER BY created_at DESC');
} else {
    $assignments = $db->query('SELECT * FROM assignments ORDER BY due_date ASC');
    $quizzes = $db->query('SELECT * FROM quizzes ORDER BY created_at DESC');
}

include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-layout">
    <aside class="dashboard-sidebar card">
        <h3>Navigation</h3>
        <nav class="dashboard-nav">
            <a class="button" href="dashboard.php">Dashboard Home</a>
            <?php if ($user['role'] === 'teacher'): ?>
                <a class="button" href="create_assignment.php">Create Assignment</a>
                <a class="button" href="create_quiz.php">Create Quiz</a>
                <a class="button" href="teacher_review.php">Review Submissions</a>
                <a class="button" href="student_statistics.php">Student Statistics</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a class="button" href="admin_panel.php">System Admin</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'student'): ?>
                <a class="button" href="student_history.php">View History</a>
            <?php endif; ?>
        </nav>
    </aside>
    <section class="dashboard-main">
        <div class="card">
            <h2>Dashboard</h2>
            <p>Hello, <?php echo htmlspecialchars($user['name']); ?>. You are logged in as a <?php echo htmlspecialchars($user['role']); ?>.</p>
        </div>

        <?php if ($user['role'] === 'teacher'): ?>
            <div class="card">
                <h3>Teacher Actions</h3>
                <a class="button" href="create_assignment.php">Create Assignment</a>
                <a class="button" href="create_quiz.php">Create Quiz</a>
                <a class="button" href="teacher_review.php">Review Submissions</a>
                <a class="button" href="student_statistics.php">Student Statistics</a>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'admin'): ?>
            <div class="card">
                <h3>Admin Actions</h3>
                <a class="button" href="admin_panel.php">Open Admin Panel</a>
                <a class="button" href="student_statistics.php">Student Statistics</a>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'student'): ?>
            <div class="card">
                <h3>Student Actions</h3>
                <a class="button" href="student_history.php">View History</a>
            </div>
        <?php endif; ?>

        <div class="card">
    <h3>Assignments</h3>
    <?php if ($assignments->rowCount()): ?>
        <table class="table">
            <thead>
                <tr><th>Title</th><th>Due Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                        <td><a href="view_assignment.php?id=<?php echo $assignment['id']; ?>">View</a></td>
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
    <?php if ($quizzes->rowCount()): ?>
        <table class="table">
            <thead>
                <tr><th>Title</th><th>Created At</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['created_at']); ?></td>
                        <td><a href="view_quiz.php?id=<?php echo $quiz['id']; ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No quizzes found.</p>
    <?php endif; ?>
</div>

    </section>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
