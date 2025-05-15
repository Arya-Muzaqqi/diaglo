<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) redirect('../login.php');

// Handle delete question
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Delete associated media first
    $stmt = $conn->prepare("SELECT media FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetchColumn();
    
    if ($media && file_exists("../uploads/$media")) {
        unlink("../uploads/$media");
    }
    
    $conn->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = "Soal berhasil dihapus";
    redirect('manage_questions.php');
}

// Get all questions
$questions = $conn->query("SELECT * FROM quizzes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Soal - DIAGLO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #f8f9fc;
            --accent: #2e59d9;
            --light-gray: #f8f9fa;
        }
        body {
            background-color: var(--secondary);
            font-family: 'Nunito', sans-serif;
        }
        .question-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .question-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.15);
        }
        .question-header {
            background-color: var(--primary);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0;
            font-weight: 600;
        }
        .question-body {
            padding: 1.5rem;
            background-color: white;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .option-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: var(--light-gray);
            border-radius: 0.35rem;
            position: relative;
            padding-left: 2.5rem;
        }
        .option-letter {
            position: absolute;
            left: 1rem;
            font-weight: bold;
            color: var(--primary);
        }
        .correct-option {
            background-color: #e7f5e9;
            border-left: 4px solid #28a745;
        }
        .question-media {
            max-width: 150px;
            border-radius: 0.35rem;
            margin-top: 1rem;
            border: 1px solid #eee;
        }
        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }
        .truncate-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .footer-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <h2 class="mb-0 text-center flex-grow-1"><i class="fas fa-tasks me-2"></i>Kelola Soal</h2>
            <a href="add_question.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Soal
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($questions as $question): ?>
            <div class="col-md-6 mb-4">
                <div class="question-card">
                    <div class="question-header">
                        Soal #<?= $question['id'] ?>
                        <span class="float-end">
                            <i class="fas fa-check-circle"></i> 
                            <?= chr(64 + $question['correct_answer']) ?>
                        </span>
                    </div>
                    <div class="question-body">
                        <!-- Question Text -->
                        <div class="mb-3">
                            <h6 class="fw-bold">Pertanyaan:</h6>
                            <p class="truncate-text"><?= htmlspecialchars($question['question']) ?></p>
                        </div>
                        
                        <!-- Options -->
                        <h6 class="fw-bold mb-2">Opsi Jawaban:</h6>
                        <div class="mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="option-item <?= $question['correct_answer'] == $i ? 'correct-option' : '' ?>">
                                <span class="option-letter"><?= chr(64 + $i) ?>.</span>
                                <span class="option-text"><?= htmlspecialchars($question["option$i"]) ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Media -->
                        <?php if ($question['media']): ?>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-2">Media:</h6>
                            <img src="../uploads/<?= $question['media'] ?>" class="question-media">
                        </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons mt-3">
                            <a href="edit_question.php?id=<?= $question['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?= $question['id'] ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Hapus soal ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>


        <!-- Pagination (optional) -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm before delete
        document.querySelectorAll('.btn-outline-danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Yakin ingin menghapus soal ini?')) {
                    e.preventDefault();
                }
            });
        });

        // Tooltip for options
        document.querySelectorAll('.option-text').forEach(el => {
            if (el.scrollWidth > el.clientWidth) {
                el.setAttribute('title', el.textContent);
            }
        });
    </script>
</body>
</html>