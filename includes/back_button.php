<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Do not show a "Back to Dashboard" button on the dashboard itself.
if ($currentPage === 'dashboard.php') {
    return;
}
?>
<a href="dashboard.php" class="back-button">
    <i class="fa-solid fa-arrow-left"></i>
    <span>Back to Dashboard</span>
</a>
