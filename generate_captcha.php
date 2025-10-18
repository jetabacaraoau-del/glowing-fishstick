<?php
session_start();
function generateCaptcha() {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $_SESSION['captcha_code'] = substr(str_shuffle($characters), 0, 6);
}
if (!isset($_SESSION['captcha_code'])) {
    generateCaptcha();
}

echo generateCaptcha();
?>
