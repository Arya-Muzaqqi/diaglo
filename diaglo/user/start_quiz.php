<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../login.php');
}

// Get random 5 questions
$stmt = $conn->query("SELECT * FROM quizzes ORDER BY RAND() LIMIT 5");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($questions)) {
    die("Belum ada soal tersedia!");
}

// Store in session
$_SESSION['quiz_questions'] = $questions;
$_SESSION['current_question'] = 0;
$_SESSION['score'] = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mulai Kuis - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .question {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .options {
            margin: 25px 0;
        }
        .option {
            display: block;
            padding: 12px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .option:hover {
            background: #e9ecef;
        }
        .option input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container quiz-container">
        <h1>Kuis DIAGLO</h1>
        <div class="progress">
            Soal <?= $_SESSION['current_question'] + 1 ?> dari <?= count($questions) ?>
        </div>

        <form action="process_quiz.php" method="POST">
            <input type="hidden" name="question_id" value="<?= $questions[0]['id'] ?>">
            
            <div class="question">
                <?= htmlspecialchars($questions[0]['question']) ?>
            </div>

            <?php if (!empty($questions[0]['media'])): ?>
            <div class="media">
                <img src="../uploads/<?= htmlspecialchars($questions[0]['media']) ?>" 
                     alt="Gambar soal" style="max-width: 300px;">
            </div>
            <?php endif; ?>

            <div class="options">
                <?php 
                $optionLetters = ['A', 'B', 'C', 'D', 'E'];
                for ($i = 1; $i <= 5; $i++): 
                ?>
                <label class="option">
                    <input type="radio" name="answer" value="<?= $i ?>" required>
                    <b><?= $optionLetters[$i-1] ?>.</b> 
                    <?= htmlspecialchars($questions[0]["option$i"]) ?>
                </label>
                <?php endfor; ?>
            </div>

            <button type="submit" class="btn">Lanjut</button>
        </form>
    </div>
</body>
</html>