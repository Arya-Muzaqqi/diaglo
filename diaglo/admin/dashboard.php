<?php
include '../includes/config.php';
include '../includes/functions.php';

// Cek role admin
if (!isAdmin()) {
    redirect('../login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>HAI, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="menu">
            <a href="manage_questions.php" class="btn">Kelola Soal</a>
            <a href="view_scores.php" class="btn">Lihat Skor Peserta</a>
            <a href="manage_users.php" class="btn">Kelola User</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</body>
</html>