<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) redirect('../login.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    $media = null;
    if (!empty($_FILES['media']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($file_ext, $allowed_ext) && $_FILES['media']['size'] <= $max_size) {
            $new_filename = 'quiz_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
                $media = $new_filename;
            } else {
                $_SESSION['error'] = "Gagal mengupload file.";
            }
        } else {
            $_SESSION['error'] = "File harus berupa gambar (JPG/PNG/GIF) dan maksimal 2MB.";
        }
    }

    // Validate required fields
    $required = ['question', 'option1', 'option2', 'option3', 'option4', 'option5', 'correct_answer'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "Semua field wajib diisi!";
            redirect('add_question.php');
        }
    }

    // If no errors, proceed with database insertion
    if (!isset($_SESSION['error'])) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO quizzes 
                (question, option1, option2, option3, option4, option5, correct_answer, media, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                trim($_POST['question']),
                trim($_POST['option1']),
                trim($_POST['option2']),
                trim($_POST['option3']),
                trim($_POST['option4']),
                trim($_POST['option5']),
                (int)$_POST['correct_answer'],
                $media
            ]);

            $_SESSION['success'] = "Soal berhasil ditambahkan!";
            redirect('manage_questions.php');

        } catch (PDOException $e) {
            // Delete uploaded file if database fails
            if ($media && file_exists("../uploads/$media")) {
                unlink("../uploads/$media");
            }
            $_SESSION['error'] = "Error database: " . $e->getMessage();
            redirect('add_question.php');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Soal Baru - DIAGLO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #f8f9fc;
            --accent: #2e59d9;
            --danger: #e74a3b;
        }
        body {
            background-color: var(--secondary);
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
            background-color: var(--danger);
            border-color: var(--danger);
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
            border: 1px solid #ddd;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        .media-preview {
            max-width: 100%;
            border: 1px dashed #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        .alert {
            border-radius: 0.35rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <!-- Error/Success Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Card Form -->
                <div class="card shadow-lg">
                    <div class="card-header">
                        <h5 class="m-0"><i class="fas fa-plus-circle me-2"></i>TAMBAH SOAL BARU</h5>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="quizForm">
                            <!-- Question -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">PERTANYAAN</label>
                                <textarea name="question" class="form-control" rows="4" required 
                                          style="min-height: 120px;"><?= isset($_POST['question']) ? htmlspecialchars($_POST['question']) : '' ?></textarea>
                            </div>
                            
                            <!-- Options -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">OPSI JAWABAN</label>
                                <div class="row g-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded bg-light h-100">
                                            <span class="option-label">Opsi <?= chr(64 + $i) ?></span>
                                            <input type="text" name="option<?= $i ?>" class="form-control" required
                                                   value="<?= isset($_POST["option$i"]) ? htmlspecialchars($_POST["option$i"]) : '' ?>">
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <!-- Correct Answer -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">JAWABAN BENAR</label>
                                <select name="correct_answer" class="form-select" required>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>" <?= (isset($_POST['correct_answer']) && $_POST['correct_answer'] == $i) ? 'selected' : '' ?>>
                                        Opsi <?= chr(64 + $i) ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <!-- Media -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">MEDIA SOAL (Opsional)</label>
                                <input type="file" name="media" class="form-control" id="mediaUpload" accept="image/*">
                                <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF (Maks. 2MB)</small>
                                <div id="mediaPreview" class="media-preview mt-3">
                                    <img id="previewImage" src="#" alt="Preview" class="img-fluid rounded">
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                        <i class="fas fa-times me-1"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="footer-actions">
                                <a href="manage_questions.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-secondary me-2">
                                        <i class="fas fa-redo me-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Soal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
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

        // Remove image
        function removeImage() {
            document.getElementById('mediaUpload').value = '';
            document.getElementById('mediaPreview').style.display = 'none';
        }

        // Form validation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            let valid = true;
            
            // Check required fields
            document.querySelectorAll('[required]').forEach(el => {
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Harap isi semua field wajib!');
            }
        });
    </script>
</body>
</html>