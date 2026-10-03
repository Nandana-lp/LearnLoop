<?php
require_once '../includes/auth.php'; require_role('student'); require_once '../config/db.php'; $page_title='Academic Calendar';
$events=$conn->query("SELECT * FROM tbl_events ORDER BY date ASC");
require '../includes/header.php'; ?><div class="panel"><h5>Academic Calendar</h5><div class="table-responsive mt-3"><table class="table"><thead><tr><th>Date</th><th>Title</th><th>Description</th><th>Created By</th></tr></thead><tbody><?php while($e1=$events->fetch_assoc()):?><tr><td><?php echo e($e1['date']);?></td><td><?php echo e($e1['title']);?></td><td><?php echo e($e1['description']);?></td><td><?php echo e($e1['created_by']);?></td></tr><?php endwhile;?></tbody></table></div></div><?php require '../includes/footer.php'; ?>
