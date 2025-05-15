<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $user_answer = (int)$_POST['answer'];
    
    // Validate answer (1-5)
    if ($user_answer < 1 || $user_answer > 5) {
        die("Jawaban tidak valid!");
    }

    // Get correct answer
    $stmt = $conn->prepare("SELECT correct_answer FROM quizzes WHERE id = ?");
    $stmt->execute([$question_id]);
    $correct_answer = (int)$stmt->fetchColumn();

    // Update score if correct
    if ($user_answer == $correct_answer) {
        $_SESSION['score']++;
    }

    // Move to next question
    $_SESSION['current_question']++;

    // Check if quiz completed
    if ($_SESSION['current_question'] >= count($_SESSION['quiz_questions'])) {
        // Save score to database
        $stmt = $conn->prepare("
            INSERT INTO user_scores 
            (user_id, score, total_questions) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['score'],
            count($_SESSION['quiz_questions'])
        ]);

        // Redirect to result
        redirect('quiz_result.php');
    } else {
        // Auto-submit to next question
        $next_question = $_SESSION['quiz_questions'][$_SESSION['current_question']];
        ?>
        <form id="autoSubmit" action="process_quiz.php" method="POST">
            <input type="hidden" name="question_id" value="<?= $next_question['id'] ?>">
            <input type="hidden" name="answer" value="0">
        </form>
        <script>
            document.getElementById('autoSubmit').submit();
        </script>
        <?php
    }
} else {
    redirect('start_quiz.php');
}
?>