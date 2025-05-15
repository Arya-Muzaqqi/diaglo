<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="skor_peserta.xls"');

// Query data skor
$scores = $conn->query("
    SELECT u.username, s.score, s.total_questions, s.completed_at 
    FROM user_scores s
    JOIN users u ON s.user_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1">
    <tr>
        <th>Nama Peserta</th>
        <th>Skor</th>
        <th>Tanggal</th>
    </tr>
    <?php foreach ($scores as $score): ?>
    <tr>
        <td><?php echo $score['username']; ?></td>
        <td><?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?></td>
        <td><?php echo $score['completed_at']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>