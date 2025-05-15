<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) redirect('../login.php');

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT media FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$media = $stmt->fetchColumn();

if ($media && file_exists("../uploads/$media")) {
    unlink("../uploads/$media");
}

$conn->prepare("UPDATE quizzes SET media = NULL WHERE id = ?")->execute([$id]);
$_SESSION['success'] = "Media berhasil dihapus";
redirect('edit_question.php?id='.$id);
?>