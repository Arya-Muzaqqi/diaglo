<?php
include '../includes/config.php';
include '../includes/functions.php';

// Cek login dan role user
if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../login.php');
}

// Ambil data skor user yang sedang login
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT score, total_questions, completed_at 
    FROM user_scores 
    WHERE user_id = ? 
    ORDER BY completed_at DESC
");
$stmt->execute([$user_id]);
$scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Skor - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .score-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .score-value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .score-date {
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Skor Anda</h1>
        
        <?php if (empty($scores)): ?>
            <p>Anda belum mengerjakan kuis apapun.</p>
        <?php else: ?>
            <div class="score-list">
                <?php foreach ($scores as $score): ?>
                <div class="score-card">
                    <div class="score-value">
                        <?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?>
                        <span>(<?php echo round(($score['score']/$score['total_questions'])*100); ?>%)</span>
                    </div>
                    <div class="score-date">
                        Diselesaikan pada: <?php echo date('d M Y H:i', strtotime($score['completed_at'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
    </div>
</body>
</html>