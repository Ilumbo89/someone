<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_teacher()) {
    header('Location: dashboard.php');
    exit;
}

$quiz_id = $_GET['id'] ?? null;
$quiz = null;
$errors = [];

if ($quiz_id) {
    $stmt = $db->prepare('SELECT * FROM quizzes WHERE id = ? AND created_by = ?');
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    $quiz = $stmt->fetch();
}

if (!$quiz) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_option = $_POST['correct_option'] ?? 'a';

    if ($question_text === '' || $option_a === '' || $option_b === '' || $option_c === '' || $option_d === '') {
        $errors[] = 'All fields are required.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option]);
        header('Location: add_quiz_questions.php?id=' . $quiz_id);
        exit;
    }
}

$questions = $db->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC');
$questions->execute([$quiz_id]);

include __DIR__ . '/../templates/header.php';
?>
<div class="card">
    <h2>Add Questions to Quiz: <?php echo htmlspecialchars($quiz['title']); ?></h2>
    <?php if ($errors): ?>
        <div class="alert"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Question</label>
        <textarea name="question_text"><?php echo htmlspecialchars($question_text ?? ''); ?></textarea>
        <label>Option A</label>
        <input type="text" name="option_a" value="<?php echo htmlspecialchars($option_a ?? ''); ?>">
        <label>Option B</label>
        <input type="text" name="option_b" value="<?php echo htmlspecialchars($option_b ?? ''); ?>">
        <label>Option C</label>
        <input type="text" name="option_c" value="<?php echo htmlspecialchars($option_c ?? ''); ?>">
        <label>Option D</label>
        <input type="text" name="option_d" value="<?php echo htmlspecialchars($option_d ?? ''); ?>">
        <label>Correct Option</label>
        <select name="correct_option">
            <option value="a">A</option>
            <option value="b">B</option>
            <option value="c">C</option>
            <option value="d">D</option>
        </select>
        <button type="submit">Add Question</button>
    </form>
</div>

<div class="card">
    <h3>Existing Questions</h3>
    <?php if ($questions->rowCount()): ?>
        <table class="table">
            <thead>
                <tr><th>#</th><th>Question</th><th>Correct</th></tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo $question['id']; ?></td>
                        <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                        <td><?php echo strtoupper($question['correct_option']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No questions added yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
