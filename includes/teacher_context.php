<?php
/* Teacher context: course/batch is fixed by admin assignment; teacher changes semester on dashboard. */
if (session_status() === PHP_SESSION_NONE) session_start();
$teacher_context_teacher = $_SESSION['username'] ?? '';
$teacher_context_semester = (int)($_SESSION['teacher_semester'] ?? 0);
$teacher_context_teaching_id = (int)($_SESSION['teacher_teaching_id'] ?? 0);
$teacher_context_batch_id = (int)($_SESSION['teacher_batch_id'] ?? 0);
$teacher_context_batch = null;
$teacher_context_assignment = null;
$teacher_context_options = [];
$teacher_context_subjects = [];

if ($teacher_context_teacher !== '') {
    $st = $conn->prepare("SELECT ta.id,ta.batch_id,ta.semester,ta.subject,b.batch_name,b.course,b.admission_year,b.duration_years
        FROM tbl_teacher_teaching ta
        JOIN tbl_batches b ON b.id=ta.batch_id
        WHERE ta.teacher_username=? AND ta.active=1 AND b.active=1
        ORDER BY b.course,b.batch_name,ta.semester,ta.subject");
    $st->bind_param('s', $teacher_context_teacher);
    $st->execute();
    $r = $st->get_result();
    while ($row = $r->fetch_assoc()) $teacher_context_options[] = $row;
}

/* Use the first active cohort assigned to the teacher. Course/batch is not changed by the teacher. */
if (!$teacher_context_batch_id && $teacher_context_options) {
    $teacher_context_batch_id = (int)$teacher_context_options[0]['batch_id'];
    $_SESSION['teacher_batch_id'] = $teacher_context_batch_id;
}
if (!$teacher_context_semester && $teacher_context_options) {
    $teacher_context_semester = (int)$teacher_context_options[0]['semester'];
    $_SESSION['teacher_semester'] = $teacher_context_semester;
}

foreach ($teacher_context_options as $row) {
    if ((int)$row['batch_id'] === $teacher_context_batch_id && $teacher_context_batch === null) {
        $teacher_context_batch = $row;
    }
    if ((int)$row['batch_id'] === $teacher_context_batch_id && (int)$row['semester'] === $teacher_context_semester) {
        $teacher_context_subjects[$row['id']] = $row['subject'];
        if ($teacher_context_assignment === null) {
            $teacher_context_assignment = $row;
            $teacher_context_teaching_id = (int)$row['id'];
        }
    }
}
if ($teacher_context_assignment) $_SESSION['teacher_teaching_id'] = $teacher_context_teaching_id;

function teacher_context_label(): string {
    global $teacher_context_batch, $teacher_context_semester, $teacher_context_assignment;
    if (!$teacher_context_batch) return 'Teaching course not assigned';
    $subject = $teacher_context_assignment['subject'] ?? 'Choose a subject on this page';
    return $teacher_context_batch['batch_name'].' · '.$teacher_context_batch['course'].' · Semester '.$teacher_context_semester.' · '.$subject;
}

function teacher_context_valid_assignment(mysqli $conn, string $teacher, int $assignmentId, int $semester): ?array {
    $st = $conn->prepare("SELECT ta.*,b.batch_name,b.course,b.admission_year,b.duration_years
        FROM tbl_teacher_teaching ta JOIN tbl_batches b ON b.id=ta.batch_id
        WHERE ta.id=? AND ta.teacher_username=? AND ta.semester=? AND ta.active=1 AND b.active=1");
    $st->bind_param('isi', $assignmentId, $teacher, $semester);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}
