<?php
require_once '../includes/auth.php'; require_role('student'); require_once '../config/db.php'; $page_title='Resume';
$msg='';$err='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['resume'])){
 $f=$_FILES['resume'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
 if($f['error']!==UPLOAD_ERR_OK || !in_array($ext,['pdf','doc','docx'],true))$err='Upload a PDF, DOC or DOCX file.';
 else{$dir=__DIR__.'/../uploads/resumes/';if(!is_dir($dir))mkdir($dir,0777,true);$name=time().'_'.preg_replace('/[^A-Za-z0-9._-]/','_',basename($f['name']));move_uploaded_file($f['tmp_name'],$dir.$name);$stmt=$conn->prepare("INSERT INTO tbl_resume(username,resume_file,approved) VALUES(?,?,0)");$stmt->bind_param("ss",$_SESSION['username'],$name);$stmt->execute();$msg='Resume uploaded and submitted for approval.';}
}
$stmt=$conn->prepare("SELECT * FROM tbl_resume WHERE username=? ORDER BY id DESC");$stmt->bind_param("s",$_SESSION['username']);$stmt->execute();$rows=$stmt->get_result();
require '../includes/header.php'; ?><div class="panel"><h5>Upload Resume</h5><?php if($msg):?><div class="alert alert-success"><?php echo e($msg);?></div><?php endif;?><?php if($err):?><div class="alert alert-danger"><?php echo e($err);?></div><?php endif;?><div class="upload-box"><form method="POST" enctype="multipart/form-data"><input class="form-control" type="file" name="resume" required><button class="btn btn-primary mt-3">Upload Resume</button></form></div><hr><h6>Uploaded Resumes</h6><div class="table-responsive"><table class="table"><thead><tr><th>File</th><th>Uploaded</th><th>Status</th></tr></thead><tbody><?php while($r=$rows->fetch_assoc()):?><tr><td><a target="_blank" href="../uploads/resumes/<?php echo rawurlencode(basename($r['resume_file']));?>">Open</a></td><td><?php echo e($r['Uploaded_at']);?></td><td><?php echo $r['approved']?'Approved':'Pending';?></td></tr><?php endwhile;?></tbody></table></div></div><?php require '../includes/footer.php'; ?>
