<?php
session_start();
require_once __DIR__ . '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($username === '' || $password === '' || !in_array($role, ['student','teacher','admin'], true)) {
        $error = 'Please enter all login details.';
    } else {
        if ($role === 'student') {
            $stmt = $conn->prepare("SELECT username,name,password,approved FROM tbl_student WHERE username=? LIMIT 1");
        } elseif ($role === 'teacher') {
            $stmt = $conn->prepare("SELECT username,name,password,approved FROM tbl_trs WHERE username=? LIMIT 1");
        } else {
            $stmt = $conn->prepare("SELECT username,name,password FROM tbl_admin WHERE username=? LIMIT 1");
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if ($role !== 'admin' && (int)$user['approved'] !== 1) {
                $error = 'Your account is waiting for approval.';
            } else {
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['user_type'] = $role;
                if ($role === 'student') header('Location: student/dashboard.php');
                elseif ($role === 'teacher') header('Location: teacher/dashboard.php');
                else header('Location: admin/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid username, password or role.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | LearnLoop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
<a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to LearnLoop</a>
<div class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></div>
<h2>Welcome back</h2><p class="text-muted">Sign in to your LearnLoop account.</p>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<div class="mb-3"><label class="form-label">Username / Email</label><input class="form-control" name="username" required></div>
<div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
<div class="mb-4"><label class="form-label">Login as</label>
<select class="form-select" name="role" required><option value="">Select role</option><option value="student">Student</option><option value="teacher">Teacher</option><option value="admin">Administrator</option></select></div>
<button class="btn btn-primary w-100 py-2">Login <i class="fa-solid fa-right-to-bracket ms-2"></i></button>
</form>
<p class="text-center mt-4 mb-0">New student? <a href="student_register.php">Create an account</a></p>
</div>
</body></html>
