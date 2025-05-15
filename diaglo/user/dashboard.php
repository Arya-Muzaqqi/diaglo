<?php
include '../includes/config.php';
include '../includes/functions.php';

// Cek apakah user sudah login dan role = user
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Peserta - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="menu">
            <a href="start_quiz.php" class="btn">Mulai Kuis</a>
            <a href="view_scores.php" class="btn">Lihat Skor</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</body>
</html>