<?php
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/user_settings.php';
require_once __DIR__ . '/academic.php';

$role = $_SESSION['user_type'] ?? '';
if (!in_array($role, ['student','teacher','admin'], true)) {
    header('Location: ../login.php');
    exit;
}
$user = $_SESSION['username'];
$page_title = 'Settings';
$msg = '';
$err = '';
$settings = get_user_settings($conn, $user, $role);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_theme') {
        $theme = ($_POST['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $st = $conn->prepare("UPDATE tbl_user_settings SET theme=? WHERE username=? AND role=?");
        $st->bind_param('sss', $theme, $user, $role);
        $st->execute();
        $settings = get_user_settings($conn, $user, $role);
        $msg = 'Theme updated successfully.';
    }

    if ($action === 'save_notifications') {
        $email = isset($_POST['email_notifications']) ? 1 : 0;
        $assign = isset($_POST['assignment_notifications']) ? 1 : 0;
        $attendance = isset($_POST['attendance_notifications']) ? 1 : 0;
        $showAcademic = ($role === 'student' && isset($_POST['show_academic_info'])) ? 1 : 0;
        $st = $conn->prepare("UPDATE tbl_user_settings SET email_notifications=?, assignment_notifications=?, attendance_notifications=?, show_academic_info=? WHERE username=? AND role=?");
        $st->bind_param('iiiiss', $email, $assign, $attendance, $showAcademic, $user, $role);
        $st->execute();
        $settings = get_user_settings($conn, $user, $role);
        $msg = 'Preferences saved.';
    }

    if ($action === 'save_profile') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $err = 'Name cannot be empty.';
        } else {
            if ($role === 'student') {
                $reg = trim($_POST['reg_number'] ?? '');
                $st = $conn->prepare("UPDATE tbl_student SET name=?, reg_number=? WHERE username=?");
                $st->bind_param('sss', $name, $reg, $user);
                $st->execute();
            } elseif ($role === 'teacher') {
                $st = $conn->prepare("UPDATE tbl_trs SET name=? WHERE username=?");
                $st->bind_param('ss', $name, $user);
                $st->execute();
            } else {
                $st = $conn->prepare("UPDATE tbl_admin SET name=? WHERE username=?");
                $st->bind_param('ss', $name, $user);
                $st->execute();
            }
            $_SESSION['name'] = $name;
            $msg = 'Profile details updated.';
        }
    }

    if ($action === 'upload_profile') {
        if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            $err = 'Please choose an image.';
        } else {
            $file = $_FILES['profile_pic'];
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $err = 'Only JPG, JPEG, PNG and WEBP images are allowed.';
            } elseif ($file['size'] > 3 * 1024 * 1024) {
                $err = 'Profile picture must be 3 MB or smaller.';
            } else {
                $dir = __DIR__ . '/../uploads/profile/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $newName = $role . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $user) . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . $newName)) {
                    $old = basename((string)$settings['profile_pic']);
                    $st = $conn->prepare("UPDATE tbl_user_settings SET profile_pic=? WHERE username=? AND role=?");
                    $st->bind_param('sss', $newName, $user, $role);
                    $st->execute();
                    if ($old && is_file($dir . $old)) @unlink($dir . $old);
                    $settings = get_user_settings($conn, $user, $role);
                    $msg = 'Profile picture updated.';
                } else {
                    $err = 'Could not save the profile picture.';
                }
            }
        }
    }

    if ($action === 'remove_profile') {
        $old = basename((string)$settings['profile_pic']);
        $st = $conn->prepare("UPDATE tbl_user_settings SET profile_pic='' WHERE username=? AND role=?");
        $st->bind_param('ss', $user, $role);
        $st->execute();
        $dir = __DIR__ . '/../uploads/profile/';
        if ($old && is_file($dir . $old)) @unlink($dir . $old);
        $settings = get_user_settings($conn, $user, $role);
        $msg = 'Profile picture removed.';
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $table = $role === 'student' ? 'tbl_student' : ($role === 'teacher' ? 'tbl_trs' : 'tbl_admin');
        $st = $conn->prepare("SELECT password FROM {$table} WHERE username=? LIMIT 1");
        $st->bind_param('s', $user);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        if (!$row || !password_verify($current, $row['password'])) {
            $err = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $err = 'New password must contain at least 6 characters.';
        } elseif ($new !== $confirm) {
            $err = 'New passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $st = $conn->prepare("UPDATE {$table} SET password=? WHERE username=?");
            $st->bind_param('ss', $hash, $user);
            $st->execute();
            $msg = 'Password changed successfully.';
        }
    }
}

$academic = null;
$studentRow = null;
if ($role === 'student') {
    $academic = academic_context($conn, $user);
    $st = $conn->prepare("SELECT reg_number, phone FROM tbl_student WHERE username=?");
    $st->bind_param('s', $user);
    $st->execute();
    $studentRow = $st->get_result()->fetch_assoc() ?: [];
}

$profileFile = basename((string)($settings['profile_pic'] ?? ''));
$profileSrc = $profileFile ? '../uploads/profile/' . rawurlencode($profileFile) : '';
require __DIR__ . '/header.php';
?>

<div class="settings-page">
    <?php if ($msg): ?><div class="alert alert-success settings-alert"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger settings-alert"><?php echo e($err); ?></div><?php endif; ?>

    <div class="settings-hero glass-card mb-4">
        <div class="settings-profile-head">
            <div class="settings-large-avatar">
                <?php if ($profileSrc): ?><img src="<?php echo e($profileSrc); ?>" alt="Profile picture"><?php else: ?><span><?php echo strtoupper(substr($_SESSION['name'] ?? 'U',0,1)); ?></span><?php endif; ?>
            </div>
            <div>
                <div class="section-kicker">ACCOUNT SETTINGS</div>
                <h2><?php echo e($_SESSION['name'] ?? 'User'); ?></h2>
                <p class="mb-0">Manage your profile, appearance, notifications and account security.</p>
            </div>
        </div>
        <div class="settings-role-badge"><i class="fa-solid fa-shield-halved"></i> <?php echo ucfirst(e($role)); ?></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="glass-card settings-section mb-4">
                <div class="settings-section-title"><div><span class="section-kicker">PROFILE</span><h5>Personal details</h5></div><i class="fa-regular fa-id-card"></i></div>
                <form method="POST">
                    <input type="hidden" name="action" value="save_profile">
                    <div class="row g-3">
                        <div class="col-md-7"><label class="form-label">Full name</label><input class="form-control" name="name" value="<?php echo e($_SESSION['name'] ?? ''); ?>" required></div>
                        <div class="col-md-5"><label class="form-label">Username / Email</label><input class="form-control" value="<?php echo e($user); ?>" readonly></div>
                        <?php if ($role === 'student'): ?>
                        <div class="col-md-6"><label class="form-label">Registration number</label><input class="form-control" name="reg_number" value="<?php echo e($studentRow['reg_number'] ?? ''); ?>" placeholder="e.g. BCA24A001"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" value="<?php echo e($studentRow['phone'] ?? ''); ?>" readonly></div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-primary mt-3"><i class="fa-solid fa-floppy-disk me-1"></i> Save profile</button>
                </form>
            </div>

            <?php if ($role === 'student' && $academic): ?>
            <div class="glass-card settings-section mb-4">
                <div class="settings-section-title"><div><span class="section-kicker">ACADEMIC PROFILE</span><h5>Your current academic details</h5></div><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="academic-settings-grid">
                    <div><small>Course</small><strong><?php echo e($academic['course'] ?? 'Not assigned'); ?></strong></div>
                    <div><small>Year</small><strong><?php echo (int)($academic['current_year'] ?? 0); ?><?php echo ((int)($academic['current_year'] ?? 0)===1?'st':((int)($academic['current_year'] ?? 0)===2?'nd':((int)($academic['current_year'] ?? 0)===3?'rd':'th'))); ?> Year</strong></div>
                    <div><small>Semester</small><strong>Semester <?php echo (int)($academic['current_semester'] ?? 0); ?></strong></div>
                    <div><small>Cohort</small><strong><?php echo e($academic['batch_name'] ?? 'Not assigned'); ?></strong></div>
                    <div><small>Registration No.</small><strong><?php echo e($studentRow['reg_number'] ?? 'Not added'); ?></strong></div>
                </div>
                <p class="small-muted mt-3 mb-0"><i class="fa-solid fa-circle-info me-1"></i>Year and semester are calculated automatically from your course cohort and academic calendar.</p>
            </div>
            <?php endif; ?>

            <div class="glass-card settings-section mb-4">
                <div class="settings-section-title"><div><span class="section-kicker">PROFILE PHOTO</span><h5>Profile picture</h5></div><i class="fa-regular fa-image"></i></div>
                <div class="profile-photo-row">
                    <div class="settings-large-avatar small-avatar"><?php if ($profileSrc): ?><img src="<?php echo e($profileSrc); ?>" alt="Profile picture"><?php else: ?><span><?php echo strtoupper(substr($_SESSION['name'] ?? 'U',0,1)); ?></span><?php endif; ?></div>
                    <div class="flex-grow-1"><form method="POST" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap"><input type="hidden" name="action" value="upload_profile"><input class="form-control" type="file" name="profile_pic" accept="image/png,image/jpeg,image/webp" required><button class="btn btn-primary">Upload</button></form><small class="small-muted d-block mt-2">JPG, PNG or WEBP · maximum 3 MB.</small></div>
                    <?php if ($profileSrc): ?><form method="POST" onsubmit="return confirm('Remove your profile picture?');"><input type="hidden" name="action" value="remove_profile"><button class="btn btn-light" type="submit">Remove</button></form><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="glass-card settings-section mb-4">
                <div class="settings-section-title"><div><span class="section-kicker">APPEARANCE</span><h5>Theme</h5></div><i class="fa-solid fa-circle-half-stroke"></i></div>
                <form method="POST" class="theme-choice-grid">
                    <input type="hidden" name="action" value="save_theme">
                    <label class="theme-choice <?php echo ($settings['theme']==='light'?'selected':''); ?>"><input type="radio" name="theme" value="light" <?php echo $settings['theme']==='light'?'checked':''; ?> onchange="this.form.submit()"><span class="theme-preview light-preview"></span><strong>Light</strong><small>White glassmorphism</small></label>
                    <label class="theme-choice <?php echo ($settings['theme']==='dark'?'selected':''); ?>"><input type="radio" name="theme" value="dark" <?php echo $settings['theme']==='dark'?'checked':''; ?> onchange="this.form.submit()"><span class="theme-preview dark-preview"></span><strong>Dark</strong><small>Low-light glass</small></label>
                </form>
            </div>

            <div class="glass-card settings-section mb-4">
                <div class="settings-section-title"><div><span class="section-kicker">NOTIFICATIONS</span><h5>College updates</h5></div><i class="fa-regular fa-bell"></i></div>
                <form method="POST">
                    <input type="hidden" name="action" value="save_notifications">
                    <label class="settings-toggle"><span><strong>Email notifications</strong><small>Important college and account updates.</small></span><input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications']?'checked':''; ?>></label>
                    <label class="settings-toggle"><span><strong>Assignment notifications</strong><small>New assignments and submission updates.</small></span><input type="checkbox" name="assignment_notifications" <?php echo $settings['assignment_notifications']?'checked':''; ?>></label>
                    <label class="settings-toggle"><span><strong>Attendance notifications</strong><small>Attendance-related reminders and updates.</small></span><input type="checkbox" name="attendance_notifications" <?php echo $settings['attendance_notifications']?'checked':''; ?>></label>
                    <?php if ($role === 'student'): ?><label class="settings-toggle"><span><strong>Show academic details</strong><small>Show your course, year and semester on your student dashboard.</small></span><input type="checkbox" name="show_academic_info" <?php echo $settings['show_academic_info']?'checked':''; ?>></label><?php endif; ?>
                    <button class="btn btn-light mt-3">Save preferences</button>
                </form>
            </div>

            <div class="glass-card settings-section">
                <div class="settings-section-title"><div><span class="section-kicker">SECURITY</span><h5>Change password</h5></div><i class="fa-solid fa-lock"></i></div>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <input class="form-control mb-2" type="password" name="current_password" placeholder="Current password" required>
                    <input class="form-control mb-2" type="password" name="new_password" placeholder="New password" minlength="6" required>
                    <input class="form-control" type="password" name="confirm_password" placeholder="Confirm new password" minlength="6" required>
                    <button class="btn btn-primary mt-3">Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
