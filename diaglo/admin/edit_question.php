<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) redirect('../login.php');

// Ambil data soal yang akan diedit
$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    $_SESSION['error'] = "Soal tidak ditemukan";
    redirect('manage_questions.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses update media jika ada file baru
    $media = $question['media'];
    if ($_FILES['media']['error'] == 0) {
        // Hapus file lama jika ada
        if ($media && file_exists("../uploads/$media")) {
            unlink("../uploads/$media");
        }
        
        // Upload file baru
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['media']['name']);
        move_uploaded_file($_FILES['media']['tmp_name'], $target_file);
        $media = $_FILES['media']['name'];
    }

    // Update data soal
    $stmt = $conn->prepare("
        UPDATE quizzes SET 
        question = ?,
        option1 = ?,
        option2 = ?,
        option3 = ?,
        option4 = ?,
        option5 = ?,
        correct_answer = ?,
        media = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST['question'],
        $_POST['option1'],
        $_POST['option2'],
        $_POST['option3'],
        $_POST['option4'],
        $_POST['option5'],
        $_POST['correct_answer'],
        $media,
        $id
    ]);
    
    $_SESSION['success'] = "Soal berhasil diperbarui";
    redirect('manage_questions.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Soal - DIAGLO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #f8f9fc;
            --accent: #2e59d9;
        }
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            margin-bottom: 2rem;
        }
        .card-header {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 0.5rem 1.5rem;
        }
        .btn-secondary {
            padding: 0.5rem 1.5rem;
        }
        .btn-danger {
            padding: 0.5rem 1.5rem;
        }
        .btn-primary:hover {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .option-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-control, .form-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        .media-preview {
            max-width: 200px;
            border: 1px dashed #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .current-media {
            max-width: 100%;
            border-radius: 5px;
        }
        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <!-- Card Form Edit Soal -->
                <div class="card shadow-lg">
                    <div class="card-header">
                        <h5 class="m-0"><i class="fas fa-edit me-2"></i>EDIT SOAL #<?= $question['id'] ?></h5>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="editQuizForm">
                            <!-- Pertanyaan -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">PERTANYAAN</label>
                                <textarea name="question" class="form-control" rows="4" required 
                                          style="min-height: 120px;"><?= htmlspecialchars($question['question']) ?></textarea>
                            </div>
                            
                            <!-- Opsi Jawaban -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">OPSI JAWABAN</label>
                                <div class="row g-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-light h-100">
                                            <span class="option-label">Opsi <?= chr(64 + $i) ?></span>
                                            <input type="text" name="option<?= $i ?>" class="form-control" 
                                                   value="<?= htmlspecialchars($question["option$i"]) ?>" required>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <!-- Jawaban Benar -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">JAWABAN BENAR</label>
                                <select name="correct_answer" class="form-select" required>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($question['correct_answer'] == $i) ? 'selected' : '' ?>>
                                        Opsi <?= chr(64 + $i) ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <!-- Media -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">MEDIA SOAL</label>
                                
                                <?php if ($question['media']): ?>
                                <div class="mb-3">
                                    <p class="mb-2">Media saat ini:</p>
                                    <img src="../uploads/<?= $question['media'] ?>" class="current-media mb-2">
                                    <div>
                                        <a href="delete_media.php?id=<?= $question['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Hapus media ini?')">
                                            <i class="fas fa-trash me-1"></i> Hapus Media
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <label class="form-label"><?= $question['media'] ? 'Ganti Media' : 'Tambah Media' ?></label>
                                <input type="file" name="media" class="form-control" id="mediaUpload" accept="image/*">
                                <small class="text-muted d-block mt-1">Format: JPG/PNG (Maks. 2MB)</small>
                                <div id="mediaPreview" class="media-preview mt-3" style="display: none;">
                                    <img id="previewImage" src="#" alt="Preview" class="img-fluid rounded">
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                        <i class="fas fa-times me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="footer-actions">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-secondary me-2">
                                        <i class="fas fa-redo me-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script untuk Preview Gambar -->
    <script>
        // Preview image upload
        document.getElementById('mediaUpload').addEventListener('change', function(e) {
            const preview = document.getElementById('mediaPreview');
            const previewImage = document.getElementById('previewImage');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Fungsi hapus gambar preview
        function removeImage() {
            document.getElementById('mediaUpload').value = '';
            document.getElementById('mediaPreview').style.display = 'none';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>