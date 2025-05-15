<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../login.php');
}

// Ambil skor dari session
$score = $_SESSION['score'];
$total_questions = count($_SESSION['quiz_questions']);

// Hapus data quiz dari session
unset($_SESSION['quiz_questions']);
unset($_SESSION['current_question']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hasil Kuis - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Hasil Kuis</h1>
        <p>Skor Anda: <strong><?php echo $score; ?>/<?php echo $total_questions; ?></strong></p>
        <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
    </div>
</body>
</html>