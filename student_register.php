<?php
session_start();
require_once __DIR__ . '/config/db.php';
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '' || $age < 1 || $phone === '' || $course === '' || strlen($password) < 6) {
        $error = 'Please fill every field. Password must contain at least 6 characters.';
    } else {
        $check = $conn->prepare("SELECT username FROM tbl_student WHERE username=?");
        $check->bind_param("s", $username); $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO tbl_student(username,name,age,password,phone,course,approved) VALUES(?,?,?,?,?,?,0)");
            $stmt->bind_param("ssisss", $username,$name,$age,$hash,$phone,$course);
            if ($stmt->execute()) $success = 'Registration completed. Please wait for administrator approval.';
            else $error = 'Registration failed: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration | LearnLoop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head><body class="auth-page">
<div class="auth-card wide">
<a href="index.php" class="back-link">← Back to LearnLoop</a>
<h2>Student Registration</h2><p class="text-muted">Create your LearnLoop student account.</p>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<form method="POST">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
<div class="col-md-6"><label class="form-label">Username / Email</label><input class="form-control" name="username" type="email" required></div>
<div class="col-md-4"><label class="form-label">Age</label><input class="form-control" name="age" type="number" min="1" required></div>
<div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
<div class="col-md-4"><label class="form-label">Course</label><select class="form-select" name="course" required><option value="">Select</option><option>BCA</option><option>BBA</option><option>BCom</option><option>BSC Computer Science</option></select></div>
<div class="col-12"><label class="form-label">Password</label><input class="form-control" name="password" type="password" minlength="6" required></div>
</div>
<button class="btn btn-primary w-100 mt-4">Register</button>
</form>
</div></body></html>
