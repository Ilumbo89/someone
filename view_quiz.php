<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$quiz_id = $_GET['id'] ?? null;
if (!$quiz_id) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $db->prepare('SELECT * FROM quizzes WHERE id = ?');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
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
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p>Teachers prepare quizzes for students. Students are the ones who answer them and receive marks and grades.</p>
            </div>
        </section>
    </div>
    <?php include __DIR__ . '/../templates/footer.php';
    exit;
}

if (!is_student()) {
    redirect_home();
}

$qStmt = $db->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC');
$qStmt->execute([$quiz_id]);
$questions = $qStmt->fetchAll();

$errors = [];
$attempt = null;

// check if already attempted
$attemptCheck = $db->prepare('SELECT id FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?');
$attemptCheck->execute([$quiz_id, $_SESSION['user_id']]);
$alreadyAttempted = (bool)$attemptCheck->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($alreadyAttempted) {
        $errors[] = 'You have already attempted this quiz.';
    }

    // enforce time limit on server
    $timeLimit = $quiz['time_limit'] ?? null;
    $startKey = 'quiz_start_' . $quiz_id;
    if ($timeLimit && isset($_SESSION[$startKey])) {
        $elapsed = time() - (int)$_SESSION[$startKey];
        if ($elapsed > $timeLimit * 60) {
            $errors[] = 'Time is up. You can no longer submit this quiz.';
        }
    }

    $selected = $_POST['answer'] ?? [];
    $total = 0;
    $score = 0;

    foreach ($questions as $question) {
        $total++;
        $choice = $selected[$question['id']] ?? null;
        $isCorrect = $choice === $question['correct_option'];
        if ($isCorrect) {
            $score++;
        }
    }

    if (empty($errors) && $total > 0) {
        $mark = $total > 0 ? (int)round(($score / $total) * 100) : 0;
        $grade = calculate_grade_from_mark($mark);

        $stmt = $db->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, score, total_questions, mark, grade, finished_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$quiz_id, $_SESSION['user_id'], $score, $total, $mark, $grade]);
        $attempt_id = $db->lastInsertId();

        foreach ($questions as $question) {
            $choice = $selected[$question['id']] ?? null;
            $isCorrect = $choice === $question['correct_option'] ? 1 : 0;
            $stmt = $db->prepare('INSERT INTO quiz_answers (attempt_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)');
            $stmt->execute([$attempt_id, $question['id'], $choice, $isCorrect]);
        }

        header('Location: view_quiz.php?id=' . $quiz_id . '&completed=1&score=' . $score . '&total=' . $total);
        exit;
    }
}

$completed = isset($_GET['completed']);
$score = $_GET['score'] ?? null;
$total = $_GET['total'] ?? null;

include __DIR__ . '/../templates/header.php';
?>
<?php
// start timer for quiz if not already started and not attempted
$timeLimit = $quiz['time_limit'] ?? null;
$startKey = 'quiz_start_' . $quiz_id;
if (!$alreadyAttempted && !isset($_SESSION[$startKey])) {
    $_SESSION[$startKey] = time();
}
?>
<div class="dashboard-layout">
    <?php include __DIR__ . '/../templates/side_nav.php'; ?>
    <section class="dashboard-main">
    <div class="card">
    <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
</div>

<?php if ($completed): ?>
    <?php unset($_SESSION['quiz_start_' . $quiz_id]); ?>
    <div class="card">
        <h3>Quiz Completed</h3>
        <p>Your score: <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total); ?></p>
        <p><a class="button" href="dashboard.php">Back to dashboard</a></p>
    </div>
    <?php else: ?>
    <?php if ($alreadyAttempted): ?>
        <div class="card">
            <h3>Quiz already attempted</h3>
            <p>You cannot reattempt this quiz.</p>
        </div>
    <?php else: ?>
    <form id="quiz-form" method="post">
        <?php foreach ($questions as $index => $question): ?>
            <div class="card">
                <h4>Question <?php echo $index + 1; ?></h4>
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                <?php foreach (['a','b','c','d'] as $option): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="<?php echo $option; ?>">
                        <?php echo strtoupper($option); ?>. <?php echo htmlspecialchars($question['option_' . $option]); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($timeLimit): ?>
            <?php $remaining = ($timeLimit * 60) - (time() - ($_SESSION['quiz_start_' . $quiz_id] ?? time())); ?>
            <div class="card"><strong>Time remaining:</strong> <span id="quiz-timer"><?php echo max(0, $remaining); ?></span></div>
            <script>
                (function(){
                    var remaining = <?php echo (int)max(0, $remaining); ?>;
                    // initialize countdown, auto-submit when time expires
                    window.addEventListener('DOMContentLoaded', function(){
                        startCountdown('quiz-timer', remaining, 'quiz-form');
                        attachPreAutoSubmitWarning('quiz-timer', remaining, 'quiz-form', 15);
                        var form = document.getElementById('quiz-form');
                        if (form) form.addEventListener('submit', function(){ /* nothing to clear for quiz */ });
                        // wire "submit now" button in modal
                        document.addEventListener('click', function(e){
                            if (e.target && e.target.id === 'auto-submit-now') {
                                if (form) { form.submit(); }
                            }
                        });
                    });
                })();
            </script>
        <?php endif; ?>
        <button type="submit">Submit Quiz</button>
    </form>
    <?php endif; ?>
<?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
