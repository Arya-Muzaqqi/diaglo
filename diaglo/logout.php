<?php
// Tambahkan ini di baris awal
include 'includes/config.php';
include 'includes/functions.php';  // Pastikan fungsi redirect() ada di sini

// Hancurkan semua data session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman login
redirect('login.php');
?>