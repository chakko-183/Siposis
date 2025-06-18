<?php
require_once '../config.php';
check_login();

if (!is_siswa()) {
    redirect('../admin/dashboard.php');
}

// Get siswa data
$stmt = $pdo->prepare("SELECT s.*, u.nama_lengkap, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();

// Check if already registered
$stmt = $pdo->prepare("SELECT * FROM pendaftaran_osis WHERE siswa_id = ?");
$stmt->execute([$siswa['id']]);
$pendaftaran = $stmt->fetch();

// Handle form submission
if ($_POST) {
    try {
        $pdo->beginTransaction();
        
        $motivasi = trim($_POST['motivasi']);
        $visi_misi = trim($_POST['visi_misi']);
        $program_kerja = trim($_POST['program_kerja']);
        $pengalaman_organisasi = trim($_POST['pengalaman_organisasi']);
        $prestasi = trim($_POST['prestasi']);
        $posisi_diinginkan = trim($_POST['posisi_diinginkan']);
        
        // Validation
        if (empty($motivasi) || empty($visi_misi) || empty($program_kerja) || empty($posisi_diinginkan)) {
            throw new Exception('Semua field wajib diisi kecuali pengalaman organisasi dan prestasi');
        }
        
        if ($pendaftaran) {
            // Update existing registration
            $stmt = $pdo->prepare("UPDATE pendaftaran_osis SET 
                motivasi = ?, visi_misi = ?, program_kerja = ?, 
                pengalaman_organisasi = ?, prestasi = ?, posisi_diinginkan = ?,
                status_pendaftaran = 'draft'
                WHERE siswa_id = ?");
            $stmt->execute([$motivasi, $visi_misi, $program_kerja, 
                          $pengalaman_organisasi, $prestasi, $posisi_diinginkan, $siswa['id']]);
            $pendaftaran_id = $pendaftaran['id'];
        } else {
            // Create new registration
            $stmt = $pdo->prepare("INSERT INTO pendaftaran_osis 
                (siswa_id, motivasi, visi_misi, program_kerja, pengalaman_organisasi, 
                prestasi, posisi_diinginkan, status_pendaftaran, tanggal_daftar) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', NOW())");
            $stmt->execute([$siswa['id'], $motivasi, $visi_misi, $program_kerja, 
                          $pengalaman_organisasi, $prestasi, $posisi_diinginkan]);
            $pendaftaran_id = $pdo->lastInsertId();
        }
        
        // If submit button clicked, change status to submitted
        if (isset($_POST['submit_final'])) {
            $stmt = $pdo->prepare("UPDATE pendaftaran_osis SET status_pendaftaran = 'submitted' WHERE id = ?");
            $stmt->execute([$pendaftaran_id]);
        }
        
        $pdo->commit();
        $success = isset($_POST['submit_final']) ? 'Pendaftaran berhasil disubmit!' : 'Data berhasil disimpan sebagai draft!';
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM pendaftaran_osis WHERE siswa_id = ?");
        $stmt->execute([$siswa['id']]);
        $pendaftaran = $stmt->fetch();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get available positions
$posisi_tersedia = [
    'Ketua OSIS',
    'Wakil Ketua OSIS', 
    'Sekretaris',
    'Bendahara',
    'Koordinator Bidang Keimanan dan Ketakwaan',
    'Koordinator Bidang Budi Pekerti dan Kepribadian',
    'Koordinator Bidang Pembinaan Demokrasi',
    'Koordinator Bidang Wawasan Kebangsaan',
    'Koordinator Bidang Pendidikan Pendahuluan Bela Negara',
    'Koordinator Bidang Kepemimpinan dan Kepeloporan',
    'Koordinator Bidang Keorganisasian, Politik dan Kepemimpinan',
    'Koordinator Bidang Keterampilan dan Kewirausahaan',
    'Koordinator Bidang Kesegaran Jasmani dan Daya Kreasi',
    'Koordinator Bidang Persepsi, Apresiasi dan Kreasi Seni'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran OSIS - Sistem OSIS SMKN 2 Sampang</title>
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
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .character-count {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar d-flex flex-column p-3">
                    <div class="text-center text-white mb-4">
                        <i class="fas fa-user-graduate fa-3x mb-2"></i>
                        <h5><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h5>
                        <small>Dashboard Siswa</small>
                    </div>
                    
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item mb-2">
                            <a href="dashboard.php" class="nav-link text-white">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="pendaftaran.php" class="nav-link text-white active">
                                <i class="fas fa-file-alt me-2"></i>Pendaftaran
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="dokumen.php" class="nav-link text-white">
                                <i class="fas fa-upload me-2"></i>Upload Dokumen
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="status.php" class="nav-link text-white">
                                <i class="fas fa-info-circle me-2"></i>Status & Hasil
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="profil.php" class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Profil
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-auto">
                        <a href="../logout.php" class="btn btn-outline-light w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Formulir Pendaftaran OSIS</h2>
                            <p class="text-muted mb-0">Lengkapi formulir pendaftaran untuk mengikuti seleksi OSIS</p>
                        </div>
                        <div class="text-end">
                            <?php if ($pendaftaran): ?>
                                <span class="badge bg-<?= $pendaftaran['status_pendaftaran'] == 'submitted' ? 'success' : ($pendaftaran['status_pendaftaran'] == 'verified' ? 'primary' : 'warning') ?> status-badge">
                                    Status: <?= ucfirst($pendaftaran['status_pendaftaran']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary status-badge">Belum Mendaftar</span>
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
                    
                    <?php if ($pendaftaran && $pendaftaran['status_pendaftaran'] == 'submitted'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Pendaftaran Anda sudah disubmit dan sedang dalam proses verifikasi. Anda masih bisa mengupdate data sampai status berubah menjadi "Verified".
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Data Pribadi -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nama_lengkap']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">NIS</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['nis']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Kelas</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($siswa['kelas']) ?> <?= htmlspecialchars($siswa['jurusan']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?= htmlspecialchars($siswa['email']) ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Pendaftaran -->
                        <div class="col-12">
                            <form method="POST" id="registrationForm">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Form Pendaftaran OSIS</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Posisi yang Diinginkan -->
                                            <div class="col-12 mb-4">
                                                <label for="posisi_diinginkan" class="form-label">
                                                    Posisi yang Diinginkan <span class="required">*</span>
                                                </label>
                                                <select class="form-select" id="posisi_diinginkan" name="posisi_diinginkan" required>
                                                    <option value="">Pilih posisi yang diinginkan</option>
                                                    <?php foreach ($posisi_tersedia as $posisi): ?>
                                                        <option value="<?= $posisi ?>" <?= ($pendaftaran && $pendaftaran['posisi_diinginkan'] == $posisi) ? 'selected' : '' ?>>
                                                            <?= $posisi ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <!-- Motivasi -->
                                            <div class="col-12 mb-4">
                                                <label for="motivasi" class="form-label">
                                                    Motivasi Mengikuti OSIS <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" id="motivasi" name="motivasi" rows="4" 
                                                          placeholder="Jelaskan motivasi Anda untuk bergabung dengan OSIS..." 
                                                          maxlength="1000" required><?= $pendaftaran ? htmlspecialchars($pendaftaran['motivasi']) : '' ?></textarea>
                                                <div class="form-text character-count">
                                                    <span id="motivasi-count">0</span>/1000 karakter
                                                </div>
                                            </div>
                                            
                                            <!-- Visi Misi -->
                                            <div class="col-12 mb-4">
                                                <label for="visi_misi" class="form-label">
                                                    Visi dan Misi <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" id="visi_misi" name="visi_misi" rows="5" 
                                                          placeholder="Tuliskan visi dan misi Anda jika terpilih..." 
                                                          maxlength="1500" required><?= $pendaftaran ? htmlspecialchars($pendaftaran['visi_misi']) : '' ?></textarea>
                                                <div class="form-text character-count">
                                                    <span id="visi_misi-count">0</span>/1500 karakter
                                                </div>
                                            </div>
                                            
                                            <!-- Program Kerja -->
                                            <div class="col-12 mb-4">
                                                <label for="program_kerja" class="form-label">
                                                    Program Kerja <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" id="program_kerja" name="program_kerja" rows="6" 
                                                          placeholder="Jelaskan program kerja yang akan Anda jalankan..." 
                                                          maxlength="2000" required><?= $pendaftaran ? htmlspecialchars($pendaftaran['program_kerja']) : '' ?></textarea>
                                                <div class="form-text character-count">
                                                    <span id="program_kerja-count">0</span>/2000 karakter
                                                </div>
                                            </div>
                                            
                                            <!-- Pengalaman Organisasi -->
                                            <div class="col-12 mb-4">
                                                <label for="pengalaman_organisasi" class="form-label">
                                                    Pengalaman Organisasi
                                                </label>
                                                <textarea class="form-control" id="pengalaman_organisasi" name="pengalaman_organisasi" rows="4" 
                                                          placeholder="Jelaskan pengalaman organisasi yang pernah Anda ikuti (opsional)..." 
                                                          maxlength="1000"><?= $pendaftaran ? htmlspecialchars($pendaftaran['pengalaman_organisasi']) : '' ?></textarea>
                                                <div class="form-text character-count">
                                                    <span id="pengalaman_organisasi-count">0</span>/1000 karakter
                                                </div>
                                            </div>
                                            
                                            <!-- Prestasi -->
                                            <div class="col-12 mb-4">
                                                <label for="prestasi" class="form-label">
                                                    Prestasi yang Pernah Diraih
                                                </label>
                                                <textarea class="form-control" id="prestasi" name="prestasi" rows="4" 
                                                          placeholder="Jelaskan prestasi akademik maupun non-akademik yang pernah Anda raih (opsional)..." 
                                                          maxlength="1000"><?= $pendaftaran ? htmlspecialchars($pendaftaran['prestasi']) : '' ?></textarea>
                                                <div class="form-text character-count">
                                                    <span id="prestasi-count">0</span>/1000 karakter
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer bg-light">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Field yang bertanda <span class="required">*</span> wajib diisi
                                                </small>
                                            </div>
                                            <div>
                                                <button type="submit" name="save_draft" class="btn btn-warning me-2">
                                                    <i class="fas fa-save me-2"></i>Simpan Draft
                                                </button>
                                                <button type="submit" name="submit_final" class="btn btn-success" 
                                                        onclick="return confirm('Apakah Anda yakin ingin mengirim pendaftaran? Data tidak dapat diubah setelah disubmit.')">
                                                    <i class="fas fa-paper-plane me-2"></i>Submit Pendaftaran
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips Mengisi Formulir</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-check-circle text-success me-2"></i>Yang Harus Dilakukan:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Isi semua field dengan jujur dan detail</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Gunakan bahasa yang baik dan benar</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Jelaskan visi misi dengan realistis</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Program kerja harus konkret dan dapat dilaksanakan</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-times-circle text-danger me-2"></i>Yang Harus Dihindari:</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Mengisi dengan asal-asalan</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Menyalin dari internet tanpa modifikasi</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Menggunakan bahasa yang tidak sopan</li>
                                                <li><i class="fas fa-chevron-right text-primary me-2"></i>Membuat program kerja yang tidak realistis</li>
                                            </ul>
                                        </div>
                                    </div>
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
        // Character counter for textareas
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = ['motivasi', 'visi_misi', 'program_kerja', 'pengalaman_organisasi', 'prestasi'];
            
            textareas.forEach(function(id) {
                const textarea = document.getElementById(id);
                const counter = document.getElementById(id + '-count');
                
                if (textarea && counter) {
                    // Initial count
                    counter.textContent = textarea.value.length;
                    
                    // Update count on input
                    textarea.addEventListener('input', function() {
                        counter.textContent = this.value.length;
                    });
                }
            });
            
            // Form validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                const requiredFields = ['posisi_diinginkan', 'motivasi', 'visi_misi', 'program_kerja'];
                let isValid = true;
                
                requiredFields.forEach(function(fieldId) {
                    const field = document.getElementById(fieldId);
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi!');
                }
            });
        });
    </script>
</body>
</html>