<?php
require_once '../includes/auth.php'; require_role('teacher'); require_once '../config/db.php';
$page_title='Teacher Dashboard'; $teacher=$_SESSION['username']; $msg=''; $err='';
require_once '../includes/teacher_context.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_teaching_context'])) {
    $semester=(int)($_POST['semester']??0);
    $valid=false;
    foreach($teacher_context_options as $o){ if((int)$o['semester']===$semester){$valid=true; break;} }
    if(!$valid){$err='Choose a semester assigned to you by the administrator.';}
    else{
        $_SESSION['teacher_semester']=$semester;
        unset($_SESSION['teacher_teaching_id']);
        $teacher_context_semester=$semester;
        $teacher_context_teaching_id=0;
        $teacher_context_assignment=null;
        foreach($teacher_context_options as $o){if((int)$o['batch_id']===$teacher_context_batch_id && (int)$o['semester']===$semester){$teacher_context_assignment=$o;$teacher_context_teaching_id=(int)$o['id'];break;}}
        $_SESSION['teacher_teaching_id']=$teacher_context_teaching_id;
        $msg='Semester '.$semester.' selected. Course and batch are managed by the administrator.';
    }
}
require_once '../includes/teacher_context.php';

$assignmentCount=0;$pendingCount=0;$noteCount=0;$todayClasses=[];
$st=$conn->prepare("SELECT COUNT(*) c FROM tbl_student_assignments WHERE teacher_username=?");$st->bind_param('s',$teacher);$st->execute();$assignmentCount=(int)$st->get_result()->fetch_assoc()['c'];
$st=$conn->prepare("SELECT COUNT(*) c FROM tbl_leave_requests WHERE status='Pending'");$st->execute();$pendingCount=(int)$st->get_result()->fetch_assoc()['c'];
$st=$conn->prepare("SELECT COUNT(*) c FROM tbl_notes WHERE teacher_username=?");$st->bind_param('s',$teacher);$st->execute();$noteCount=(int)$st->get_result()->fetch_assoc()['c'];
$todayNo=(int)date('N');
$st=$conn->prepare("SELECT tt.*,b.batch_name FROM tbl_timetable tt JOIN tbl_batches b ON b.id=tt.batch_id WHERE tt.teacher_username=? AND tt.semester=? AND tt.day_of_week=? ORDER BY tt.hour_no");$st->bind_param('sii',$teacher,$teacher_context_semester,$todayNo);$st->execute();$r=$st->get_result();while($x=$r->fetch_assoc())$todayClasses[]=$x;
$semesters=[]; foreach($teacher_context_options as $o)$semesters[(int)$o['semester']]=true; ksort($semesters);
require '../includes/header.php';
?>
<div class="glass-hero teacher-hero mb-4"><div><span class="eyebrow-dark">TEACHER WORKSPACE</span><h2>Good to see you, <?php echo e($_SESSION['name']); ?>.</h2><p>Your course and batch are assigned by the administrator. Change only the semester you are teaching now.</p></div><div class="hero-orb"><i class="fa-solid fa-chalkboard-user"></i></div></div>
<?php if($msg): ?><div class="alert alert-success glass-alert"><?php echo e($msg); ?></div><?php endif; ?><?php if($err): ?><div class="alert alert-danger glass-alert"><?php echo e($err); ?></div><?php endif; ?>
<div class="row g-4 mb-4">
<div class="col-xl-8"><div class="glass-card context-card"><div class="section-kicker">TEACHING CONTEXT</div><h5>Choose semester</h5><div class="context-readonly mb-3"><span><strong>Course</strong><small><?php echo e($teacher_context_batch['course']??'Not assigned'); ?></small></span><span><strong>Batch</strong><small><?php echo e($teacher_context_batch['batch_name']??'Not assigned'); ?></small></span></div><form method="POST" class="row g-3 align-items-end"><input type="hidden" name="save_teaching_context" value="1"><div class="col-md-8"><label class="form-label">Semester</label><select class="form-select" name="semester" required><?php foreach(array_keys($semesters) as $s): ?><option value="<?php echo $s;?>" <?php echo $teacher_context_semester===$s?'selected':''; ?>>Semester <?php echo $s;?> · Year <?php echo (int)ceil($s/2); ?></option><?php endforeach; ?></select></div><div class="col-md-4"><button class="btn btn-primary w-100">Use This Semester</button></div></form><div class="context-chip mt-3"><i class="fa-solid fa-layer-group"></i> <?php echo e(($teacher_context_batch['course']??''). ' · '.($teacher_context_batch['batch_name']??'').' · Semester '.$teacher_context_semester); ?></div><div class="subject-pills mt-3"><span class="small-muted me-2">Subjects assigned in this semester:</span><?php $has=false; foreach($teacher_context_options as $o){if((int)$o['batch_id']===$teacher_context_batch_id&&(int)$o['semester']===$teacher_context_semester){$has=true; ?><span class="subject-pill"><?php echo e($o['subject']); ?></span><?php }} if(!$has): ?><span class="small-muted">None yet</span><?php endif; ?></div></div></div>
<div class="col-xl-4"><div class="glass-card mini-info-card h-100"><div class="section-kicker">TODAY</div><h5><?php echo date('l, d M'); ?></h5><?php if(!$todayClasses): ?><p class="small-muted mb-0">No classes are assigned to you today for Semester <?php echo $teacher_context_semester; ?>.</p><?php else: ?><div class="today-class-list"><?php foreach($todayClasses as $c): ?><div class="today-class-item"><span class="time-pill">H<?php echo (int)$c['hour_no']; ?> · <?php echo e(substr($c['start_time'],0,5)); ?></span><div><strong><?php echo e($c['subject']); ?></strong><small><?php echo e($c['batch_name']); ?> · Sem <?php echo (int)$c['semester']; ?></small></div></div><?php endforeach; ?></div><?php endif; ?></div></div></div></div>
<div class="row g-4 mb-4"><div class="col-md-4"><div class="glass-card stat-card-new"><span class="stat-icon"><i class="fa-solid fa-file-arrow-up"></i></span><strong><?php echo $assignmentCount; ?></strong><span>Student submissions</span></div></div><div class="col-md-4"><div class="glass-card stat-card-new"><span class="stat-icon"><i class="fa-solid fa-note-sticky"></i></span><strong><?php echo $noteCount; ?></strong><span>Notes in my library</span></div></div><div class="col-md-4"><div class="glass-card stat-card-new"><span class="stat-icon"><i class="fa-solid fa-envelope-open-text"></i></span><strong><?php echo $pendingCount; ?></strong><span>Pending leave requests</span></div></div></div>
<div class="glass-card"><div class="section-kicker">QUICK ACTIONS</div><div class="quick-grid"><a class="quick-tile" href="notes.php"><i class="fa-solid fa-book-open"></i><strong>Publish Notes</strong><span>Choose the subject for Semester <?php echo $teacher_context_semester;?>.</span></a><a class="quick-tile" href="assignments.php"><i class="fa-solid fa-list-check"></i><strong>Assign Work</strong><span>Create work for this semester.</span></a><a class="quick-tile" href="attendance.php"><i class="fa-solid fa-user-check"></i><strong>Hourly Attendance</strong><span>Mark only your scheduled class hour.</span></a><a class="quick-tile" href="timetable.php"><i class="fa-solid fa-calendar-week"></i><strong>My Timetable</strong><span>See your assigned subjects and hours.</span></a></div></div>
<?php require '../includes/footer.php'; ?>
