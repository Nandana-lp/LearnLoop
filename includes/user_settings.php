<?php
/* User settings/profile helpers. Requires $conn and auth.php/e(). */
function ensure_user_settings(mysqli $conn, string $username, string $role): void {
    $st = $conn->prepare("INSERT IGNORE INTO tbl_user_settings (username, role) VALUES (?, ?)");
    $st->bind_param('ss', $username, $role);
    $st->execute();
}

function get_user_settings(mysqli $conn, string $username, string $role): array {
    ensure_user_settings($conn, $username, $role);
    $st = $conn->prepare("SELECT * FROM tbl_user_settings WHERE username=? AND role=? LIMIT 1");
    $st->bind_param('ss', $username, $role);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: [
        'theme'=>'light','profile_pic'=>'','email_notifications'=>1,
        'assignment_notifications'=>1,'attendance_notifications'=>1,
        'show_academic_info'=>1
    ];
}

function profile_image_src(array $settings, string $prefix='../uploads/profile/'): string {
    $file = basename((string)($settings['profile_pic'] ?? ''));
    return $file !== '' ? $prefix . rawurlencode($file) : '';
}
