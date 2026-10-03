<?php
session_start();

if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'student') {
        header('Location: student/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'teacher') {
        header('Location: teacher/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/dashboard.php');
    }
    exit;
}

$today = date('d');
$month = date('F');
$year  = date('Y');
$fullDate = date('l, d F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LearnLoop | Academic Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing">

<nav class="landing-nav">
    <a class="landing-brand" href="index.php" aria-label="LearnLoop home">
        <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
        <span>LearnLoop</span>
    </a>

    <div class="landing-actions">
        <a class="nav-link-glass" href="login.php">Sign in</a>
        <a class="nav-link-solid" href="student_register.php">Register</a>
    </div>
</nav>

<main class="landing-main">
    <section class="landing-hero">

        <div class="ambient ambient-one"></div>
        <div class="ambient ambient-two"></div>
        <div class="ambient ambient-three"></div>

        <div class="hero-content">

            <div class="date-pill">
                <span class="date-dot"></span>
                <span><?php echo htmlspecialchars($fullDate); ?></span>
            </div>

            <p class="hero-kicker">ACADEMIC MANAGEMENT · SIMPLE · CONNECTED</p>

            <h1 class="learnloop-title">LearnLoop</h1>

            <p class="hero-subtitle">
                A calm, connected academic space for students,
                teachers and administrators.
            </p>

            <p class="hero-description">
                Timetables, attendance, notes, assignments, academic events
                and everyday college workflows — brought together in one
                simple place.
            </p>

            <div class="hero-actions">
                <a href="login.php" class="hero-primary">
                    Enter LearnLoop
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="student_register.php" class="hero-secondary">
                    Create student account
                </a>
            </div>

            <div class="month-card">
                <div class="month-number">
                    <?php echo htmlspecialchars($today); ?>
                </div>
                <div class="month-copy">
                    <span><?php echo htmlspecialchars($month); ?></span>
                    <small><?php echo htmlspecialchars($year); ?> · Academic workspace</small>
                </div>
                <i class="fa-regular fa-calendar month-icon"></i>
            </div>

        </div>

        <div class="glass-orbit orbit-one"></div>
        <div class="glass-orbit orbit-two"></div>
        <div class="glass-orbit orbit-three"></div>

    </section>
</main>

<footer class="landing-footer">
    <span>LearnLoop</span>
    <span class="footer-separator">·</span>
    <span>Academic Management Platform</span>
    <span class="footer-separator">·</span>
    <!-- <span><?php echo htmlspecialchars($year); ?></span> -->
</footer>

</body>
</html>
