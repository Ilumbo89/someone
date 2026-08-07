<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="logo" href="index.php"><?php echo htmlspecialchars(SITE_NAME); ?></a>
        <nav class="site-nav">
            <?php require_once __DIR__ . '/../includes/auth.php'; ?>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php $nav_user = current_user(); ?>
                <a href="dashboard.php">Dashboard</a>
                <?php if ($nav_user && $nav_user['role'] === 'teacher'): ?>
                    <a href="teacher_review.php">Review</a>
                <?php endif; ?>
                <?php if ($nav_user && $nav_user['role'] === 'student'): ?>
                    <a href="student_history.php">History</a>
                <?php endif; ?>
                <?php if ($nav_user && $nav_user['role'] === 'admin'): ?>
                    <a href="admin_panel.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
