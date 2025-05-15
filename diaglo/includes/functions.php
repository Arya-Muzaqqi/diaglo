<?php
// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>