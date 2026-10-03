<?php
require_once '../includes/auth.php'; require_role('student'); require_once '../config/db.php'; $page_title='Leave Calendar';
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $date=$_POST['leave_date']??''; if(!$date)$err='Select a leave date.';
 else { $status='Pending'; $stmt=$conn->prepare("INSERT INTO tbl_leave_requests(username,leave_date,status) VALUES(?,?,?)"); $stmt->bind_param("sss",$_SESSION['username'],$date,$status); $stmt->execute(); $msg='Leave request submitted.'; }
}
$stmt=$conn->prepare("SELECT * FROM tbl_leave_requests WHERE username=? ORDER BY leave_date DESC");$stmt->bind_param("s",$_SESSION['username']);$stmt->execute();$rows=$stmt->get_result();
require '../includes/header.php'; ?>
<div class="row g-4"><div class="col-lg-4"><div class="panel"><h5>Mark Leave</h5><?php if($msg):?><div class="alert alert-success"><?php echo e($msg);?></div><?php endif;?><?php if($err):?><div class="alert alert-danger"><?php echo e($err);?></div><?php endif;?><form method="POST"><label class="form-label">Leave Date</label><input class="form-control" type="date" name="leave_date" required><button class="btn btn-primary w-100 mt-3">Submit Leave Request</button></form></div></div>
<div class="col-lg-8"><div class="panel"><h5>My Leave Requests</h5><div class="table-responsive mt-3"><table class="table"><thead><tr><th>Date</th><th>Status</th></tr></thead><tbody><?php while($r=$rows->fetch_assoc()):?><tr><td><?php echo e($r['leave_date']);?></td><td><span class="badge <?php echo $r['status']==='Approved'?'text-bg-success':($r['status']==='Rejected'?'text-bg-danger':'text-bg-warning'); ?>"><?php echo e($r['status']);?></span></td></tr><?php endwhile;?></tbody></table></div></div></div></div>
<?php require '../includes/footer.php'; ?>
