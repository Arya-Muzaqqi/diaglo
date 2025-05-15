<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Ambil semua skor peserta + nama user
$scores = $conn->query("
    SELECT u.username, s.score, s.total_questions, s.completed_at 
    FROM user_scores s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.completed_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Tampilkan data dalam tabel -->
<table>
    <tr>
        <th>Nama Peserta</th>
        <th>Skor</th>
        <th>Tanggal</th>
    </tr>
    <?php foreach ($scores as $score): ?>
    <tr>
        <td><?php echo htmlspecialchars($score['username']); ?></td>
        <td><?php echo $score['score']; ?>/<?php echo $score['total_questions']; ?></td>
        <td><?php echo $score['completed_at']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Tombol Export Excel -->
<a href="export_scores.php" class="btn">Export ke Excel</a>