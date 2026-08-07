<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM assignments WHERE id = ?');
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();
if (!$assignment) {
    header('Location: dashboard.php');
    exit;
}

if (is_teacher()) {
    include __DIR__ . '/../templates/header.php';
    ?>
    <div class="dashboard-layout">
        <?php include __DIR__ . '/../templates/side_nav.php'; ?>
        <section class="dashboard-main">
            <div class="card">
                <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
                <p>Teachers prepare assignments for students. Students are the ones who submit answers for grading.</p>
            </div>
        </section>
    </div>
    <?php include __DIR__ . '/../templates/footer.php';
    exit;
}

if (!is_student()) {
    redirect_home();
}

$submission = null;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = trim($_POST['submission_text'] ?? '');
    $file_path = null;

    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = basename($_FILES['submission_file']['name']);
        $targetFile = $uploadDir . uniqid('submission_', true) . '_' . $filename;
        move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetFile);
        $file_path = str_replace(__DIR__ . '/../', '', $targetFile);
    }

    if ($submission_text === '' && $file_path === null) {
        $errors[] = 'Provide submission text or upload a file.';
    }

    if (empty($errors)) {
        // enforce single submission
        $existing = $db->prepare('SELECT id FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?');
        $existing->execute([$assignment_id, $_SESSION['user_id']]);
        if ($existing->fetch()) {
            $errors[] = 'You have already submitted this assignment.';
        } else {
            // check time limit
            $timeLimit = $assignment['time_limit'] ?? null;
            $startKey = 'assignment_start_' . $assignment_id;
            if ($timeLimit && isset($_SESSION[$startKey])) {
                $elapsed = time() - (int)$_SESSION[$startKey];
                if ($elapsed > $timeLimit * 60) {
                    $errors[] = 'Time is up. You can no longer submit.';
                }
            }
            if (empty($errors)) {
                $stmt = $db->prepare('INSERT INTO assignment_submissions (assignment_id, user_id, submission_text, file_path) VALUES (?, ?, ?, ?)');
                $stmt->execute([$assignment_id, $_SESSION['user_id'], $submission_text, $file_path]);
            }
        }
        header('Location: view_assignment.php?id=' . $assignment_id);
        exit;
    }
}

$submissionStmt = $db->prepare('SELECT * FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?');
$submissionStmt->execute([$assignment_id, $_SESSION['user_id']]);
$submission = $submissionStmt->fetch();

// start timer for assignment when first viewed
$timeLimit = $assignment['time_limit'] ?? null;
$startKey = 'assignment_start_' . $assignment_id;
if ($timeLimit && !$submission && !isset($_SESSION[$startKey])) {
    $_SESSION[$startKey] = time();
}

include __DIR__ . '/../templates/header.php';
?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../templates/side_nav.php'; ?>
    <section class="dashboard-main">
    <div class="card">
    <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
    <p><strong>Due:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
</div>

<div class="card">
    <h3>Your Submission</h3>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <?php if ($submission): ?>
        <?php unset($_SESSION['assignment_start_' . $assignment_id]); ?>
        <p><strong>Submitted at:</strong> <?php echo htmlspecialchars($submission['submitted_at']); ?></p>
        <p><strong>Mark:</strong> <?php echo $submission['mark'] !== null ? htmlspecialchars((string)$submission['mark']) . '/100' : 'Pending'; ?></p>
        <p><strong>Grade:</strong> <?php echo htmlspecialchars($submission['grade'] ?? 'Pending'); ?></p>
        <p><strong>Feedback:</strong> <?php echo nl2br(htmlspecialchars($submission['feedback'] ?? 'None')); ?></p>
        <?php if ($submission['file_path']): ?>
            <p><a href="download.php?file=<?php echo urlencode($submission['file_path']); ?>">Download submission file</a></p>
        <?php endif; ?>
        <p><?php echo nl2br(htmlspecialchars($submission['submission_text'])); ?></p>
    <?php else: ?>
        <form id="assignment-form" method="post" enctype="multipart/form-data">
            <label>Submission Text</label>
            <textarea id="assignment-textarea" name="submission_text"><?php echo htmlspecialchars($submission_text ?? ''); ?></textarea>
            <label>Upload File</label>
            <input type="file" name="submission_file">
            <?php if ($timeLimit): ?>
                <?php $remaining = ($timeLimit * 60) - (time() - ($_SESSION['assignment_start_' . $assignment_id] ?? time())); ?>
                <div class="card"><strong>Time remaining:</strong> <span id="assignment-timer"><?php echo max(0, $remaining); ?></span></div>
                <script>
                    (function(){
                        var remaining = <?php echo (int)max(0, $remaining); ?>;
                        window.addEventListener('DOMContentLoaded', function(){
                            startCountdown('assignment-timer', remaining, 'assignment-form');
                            // auto-save draft
                            startAutoSave('assignment-textarea', 'assignment_draft_<?php echo (int)$assignment_id; ?>', 10);
                            // show modal shortly before auto-submit
                            attachPreAutoSubmitWarning('assignment-timer', remaining, 'assignment-form', 15);
                            // clear draft on submit
                            var form = document.getElementById('assignment-form');
                            if (form) form.addEventListener('submit', function(){ clearAutoSave('assignment_draft_<?php echo (int)$assignment_id; ?>'); });
                        });
                    })();
                </script>
            <?php endif; ?>
            <button type="submit">Submit Assignment</button>
        </form>
    <?php endif; ?>
</div>
    </section>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
