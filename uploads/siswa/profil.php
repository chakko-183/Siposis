<?php
require_once '../config.php';
check_login();

if (!is_siswa()) {
    redirect('../admin/dashboard.php');
}

// Ambil data siswa
$stmt = $pdo->prepare("SELECT s.*, u.nama_lengkap, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();

$display_back_button = false; // Flag untuk mengontrol tampilan tombol kembali

// Handle form submission
if ($_POST) {
    $display_back_button = true; // Jika form disubmit, tampilkan tombol kembali
    try {
        $pdo->beginTransaction();

        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $no_hp = trim($_POST['no_hp']);
        $alamat = trim($_POST['alamat']);

        // Validation
        if (empty($nama_lengkap) || empty($email)) {
            throw new Exception('Nama lengkap dan email wajib diisi');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }

        // Check if email already exists (except current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            throw new Exception('Email sudah digunakan oleh pengguna lain');
        }

        // Update users table
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama_lengkap, $email, $_SESSION['user_id']]);

        // Update siswa table
        $stmt = $pdo->prepare("UPDATE siswa SET no_hp = ?, alamat = ? WHERE user_id = ?");
        $stmt->execute([$no_hp, $alamat, $_SESSION['user_id']]);

        $pdo->commit();

        // Update session data
        $_SESSION['nama_lengkap'] = $nama_lengkap;

        $success = 'Profil berhasil diperbarui!';

        // Refresh data setelah update
        $stmt = $pdo->prepare("SELECT s.*, u.nama_lengkap, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $siswa = $stmt->fetch();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Profil - Sistem OSIS SMKN 2 Sampang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .form-label {
            font-weight: bold;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <?php include 'sidebar.php'; ?>
            </div>

            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Edit Profil</h2>
                            <p class="text-muted mb-0">Perbarui informasi data pribadi Anda.</p>
                        </div>
                        <div>
                            <?php if ($display_back_button): ?>
                            <a href="profil.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Data Diri</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="editProfileForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nama_lengkap" class="form-label">
                                            Nama Lengkap <span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?= htmlspecialchars($siswa['nama_lengkap']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            Email <span class="required">*</span>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($siswa['email']) ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">NIS</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nis']) ?>" readonly>
                                        <small class="text-muted">NIS tidak dapat diubah</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Kelas</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['kelas']) ?>" readonly>
                                        <small class="text-muted">Kelas tidak dapat diubah</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jurusan</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['jurusan']) ?>" readonly>
                                        <small class="text-muted">Jurusan tidak dapat diubah</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="no_hp" class="form-label">Nomor HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                               value="<?= htmlspecialchars($siswa['no_hp'] ?? '') ?>" 
                                               placeholder="Contoh: 08123456789">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                              placeholder="Masukkan alamat lengkap Anda"><?= htmlspecialchars($siswa['alamat'] ?? '') ?></textarea>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Penting</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>Data yang Tidak Dapat Diubah:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>NIS (Nomor Induk Siswa)</li>
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Kelas</li>
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Jurusan</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-edit text-success me-2"></i>Data yang Dapat Diubah:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Nama Lengkap</li>
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Email</li>
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Nomor HP</li>
                                        <li><i class="fas fa-chevron-right text-primary me-2"></i>Alamat</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            document.getElementById('editProfileForm').addEventListener('submit', function(e) {
                const nama = document.getElementById('nama_lengkap').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (!nama || !email) {
                    e.preventDefault();
                    alert('Nama lengkap dan email wajib diisi!');
                    return false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Format email tidak valid!');
                    return false;
                }
                
                // Phone number validation (optional)
                const noHp = document.getElementById('no_hp').value.trim();
                if (noHp && !/^[0-9+\-\s()]+$/.test(noHp)) {
                    e.preventDefault();
                    alert('Format nomor HP tidak valid!');
                    return false;
                }
            });
        });
    </script>
</body>

</html>