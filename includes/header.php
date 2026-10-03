<?php
if (!isset($page_title)) $page_title = 'LearnLoop';
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user_type'] ?? '';
require_once __DIR__ . '/user_settings.php';
$userSettings = ['theme'=>'light','profile_pic'=>''];
if (isset($conn) && isset($_SESSION['username']) && $role !== '') {
    $userSettings = get_user_settings($conn, $_SESSION['username'], $role);
}
$themeClass = (($userSettings['theme'] ?? 'light') === 'dark') ? 'theme-dark' : 'theme-light';
$profileFile = basename((string)($userSettings['profile_pic'] ?? ''));
$profileSrc = $profileFile !== '' ? '../uploads/profile/' . rawurlencode($profileFile) : '';
function nav_active($page){ global $currentPage; return $currentPage === $page ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?> | LearnLoop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="portal-body role-<?php echo e($role); ?> <?php echo e($themeClass); ?>">
<div class="ambient ambient-a"></div><div class="ambient ambient-b"></div><div class="ambient ambient-c"></div>
<div class="app-shell">
<aside class="sidebar glass-surface">
    <a class="brand" href="dashboard.php"><span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span><span>LearnLoop</span></a>
    <div class="user-mini"><div class="avatar">
<?php if ($profileSrc): ?><img src="<?php echo e($profileSrc); ?>" alt="Profile" class="profile-avatar-img"><?php else: ?><?php echo strtoupper(substr($_SESSION['name'] ?? 'U',0,1)); ?><?php endif; ?>
</div><div><strong><?php echo e($_SESSION['name'] ?? 'User'); ?></strong><small><?php echo ucfirst(e($role)); ?></small></div></div>
    <nav class="side-nav">
        <a class="<?php echo nav_active('dashboard.php'); ?>" href="dashboard.php"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
        <?php if ($role === 'student'): ?>
            <a class="<?php echo nav_active('notes.php'); ?>" href="notes.php"><i class="fa-solid fa-book-open"></i><span>Notes</span></a>
            <a class="<?php echo nav_active('assignments.php'); ?>" href="assignments.php"><i class="fa-solid fa-file-arrow-up"></i><span>Assignments</span></a>
            <a class="<?php echo nav_active('attendance.php'); ?>" href="attendance.php"><i class="fa-solid fa-calendar-check"></i><span>Attendance</span></a>
            <a class="<?php echo nav_active('timetable.php'); ?>" href="timetable.php"><i class="fa-solid fa-calendar-week"></i><span>Timetable</span></a>
            <a class="<?php echo nav_active('leave.php'); ?>" href="leave.php"><i class="fa-solid fa-envelope-open-text"></i><span>Leave</span></a>
            <a class="<?php echo nav_active('events.php'); ?>" href="events.php"><i class="fa-solid fa-calendar-days"></i><span>Calendar</span></a>
            <a class="<?php echo nav_active('resume.php'); ?>" href="resume.php"><i class="fa-solid fa-file-lines"></i><span>Resume</span></a>
        <?php elseif ($role === 'teacher'): ?>
            <a class="<?php echo nav_active('notes.php'); ?>" href="notes.php"><i class="fa-solid fa-book-open"></i><span>Notes</span></a>
            <a class="<?php echo nav_active('assignments.php'); ?>" href="assignments.php"><i class="fa-solid fa-list-check"></i><span>Assignments</span></a>
            <a class="<?php echo nav_active('attendance.php'); ?>" href="attendance.php"><i class="fa-solid fa-user-check"></i><span>Hourly Attendance</span></a>
            <a class="<?php echo nav_active('timetable.php'); ?>" href="timetable.php"><i class="fa-solid fa-calendar-week"></i><span>My Timetable</span></a>
            <a class="<?php echo nav_active('leaves.php'); ?>" href="leaves.php"><i class="fa-solid fa-envelope-open-text"></i><span>Leave Requests</span></a>
            <a class="<?php echo nav_active('events.php'); ?>" href="events.php"><i class="fa-solid fa-calendar-days"></i><span>Calendar</span></a>
        <?php else: ?>
            <a class="<?php echo nav_active('students.php'); ?>" href="students.php"><i class="fa-solid fa-user-graduate"></i><span>Students</span></a>
            <a class="<?php echo nav_active('teachers.php'); ?>" href="teachers.php"><i class="fa-solid fa-chalkboard-user"></i><span>Teachers</span></a>
            <a class="<?php echo nav_active('timetable.php'); ?>" href="timetable.php"><i class="fa-solid fa-calendar-week"></i><span>Timetable</span></a>
            <a class="<?php echo nav_active('events.php'); ?>" href="events.php"><i class="fa-solid fa-calendar-days"></i><span>Calendar</span></a>
        <?php endif; ?>
    </nav>
    <a class="settings-link <?php echo nav_active('settings.php'); ?>" href="settings.php"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
    <a class="logout-link" href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sign out</span></a>
</aside>
<main class="main-content">
<header class="topbar glass-surface">
    <div><div class="topbar-kicker">LEARNLOOP · ACADEMIC SPACE</div><h1><?php echo e($page_title); ?></h1></div>
    <div class="topbar-right"><div class="date-pill"><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y'); ?></div><div class="top-avatar"><?php if ($profileSrc): ?><img src="<?php echo e($profileSrc); ?>" alt="Profile" class="profile-avatar-img"><?php else: ?><?php echo strtoupper(substr($_SESSION['name'] ?? 'U',0,1)); ?><?php endif; ?></div></div>
</header>
<div class="content-area">
<?php include __DIR__ . '/back_button.php'; ?>
