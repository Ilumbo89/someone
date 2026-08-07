<?php
require_once __DIR__ . '/config.php';

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $tableCheck = $db->query("SHOW TABLES LIKE 'users'");
    if (!$tableCheck->fetch()) {
        $db->exec(
            "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    $roleColumnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    if (!$roleColumnCheck->fetch()) {
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('student','teacher','admin') NOT NULL DEFAULT 'student'");
    }

    $assignmentSubmissionTable = $db->query("SHOW TABLES LIKE 'assignment_submissions'");
    if ($assignmentSubmissionTable->fetch()) {
        $markColumnCheck = $db->query("SHOW COLUMNS FROM assignment_submissions LIKE 'mark'");
        if (!$markColumnCheck->fetch()) {
            $db->exec('ALTER TABLE assignment_submissions ADD COLUMN mark INT NULL');
        }

        $gradeColumnCheck = $db->query("SHOW COLUMNS FROM assignment_submissions LIKE 'grade'");
        if (!$gradeColumnCheck->fetch()) {
            $db->exec("ALTER TABLE assignment_submissions ADD COLUMN grade VARCHAR(2) NULL");
        }
    }

    $quizAttemptsTable = $db->query("SHOW TABLES LIKE 'quiz_attempts'");
    if ($quizAttemptsTable->fetch()) {
        $quizMarkColumnCheck = $db->query("SHOW COLUMNS FROM quiz_attempts LIKE 'mark'");
        if (!$quizMarkColumnCheck->fetch()) {
            $db->exec('ALTER TABLE quiz_attempts ADD COLUMN mark INT NULL');
        }

        $quizGradeColumnCheck = $db->query("SHOW COLUMNS FROM quiz_attempts LIKE 'grade'");
        if (!$quizGradeColumnCheck->fetch()) {
            $db->exec("ALTER TABLE quiz_attempts ADD COLUMN grade VARCHAR(2) NULL");
        }
    }

    $permissionTableCheck = $db->query("SHOW TABLES LIKE 'permission_requests'");
    if (!$permissionTableCheck->fetch()) {
        $db->exec(
            "CREATE TABLE permission_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                teacher_id INT NOT NULL,
                request_text TEXT NOT NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                response_text TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
            )"
        );
    }

    $adminEmail = 'admin@university-portal.test';
    $adminPassword = 'Admin@123';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);

    $existingAdmin = $db->prepare('SELECT id FROM users WHERE email = ?');
    $existingAdmin->execute([$adminEmail]);
    $adminRow = $existingAdmin->fetch();

    if ($adminRow) {
        $updateAdmin = $db->prepare('UPDATE users SET password = ?, role = ? WHERE id = ?');
        $updateAdmin->execute([$adminHash, 'admin', $adminRow['id']]);
    } else {
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['System Admin', $adminEmail, $adminHash, 'admin']);
    }
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
