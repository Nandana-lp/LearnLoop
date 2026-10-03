<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function require_role($role) {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== $role) {
        header('Location: ../login.php');
        exit;
    }
}
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
