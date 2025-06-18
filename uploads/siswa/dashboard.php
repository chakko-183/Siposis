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

// Get pendaftaran data
$stmt = $pdo->prepare("SELECT * FROM pendaftaran_osis WHERE siswa_id = ?");
$stmt->execute([$siswa['id']]);
$pendaftaran = $stmt->fetch();

// Get dokumen data
$dokumen = [];
if ($pendaftaran) {
    $stmt = $pdo->prepare("SELECT * FROM dokumen WHERE pendaftaran_id = ?");
    $stmt->execute([$pendaftaran['id']]);
    $dokumen = $stmt->fetchAll();
}

// Get jadwal seleksi
$stmt = $pdo->prepare("SELECT * FROM jadwal_seleksi WHERE is_active = 1 ORDER BY tanggal_mulai ASC");
$stmt->execute();
$jadwal = $stmt->fetchAll();

// Get pengumuman for siswa
$stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE (target_audience = 'all' OR target_audience = 'siswa') AND is_published = 1 ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$pengumuman = $stmt->fetchAll();

// Get penilaian if exists
$penilaian = null;
if ($pendaftaran) {
    $stmt = $pdo->prepare("SELECT * FROM penilaian WHERE pendaftaran_id = ?");
    $stmt->execute([$pendaftaran['id']]);
    $penilaian = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Sistem OSIS SMKN 2 Sampang</title>
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
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        .progress-step {
            text-align: center;
            margin-bottom: 20px;
        }
        .progress-step.active .step-icon {
            background: #28a745;
            color: white;
        }
        .progress-step.pending .step-icon {
            background: #ffc107;
            color: #212529;
        }
        .progress-step.inactive .step-icon {
            background: #6c757d;
            color: white;
        }
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
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
                            <a href="dashboard.php" class="nav-link text-white active">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="pendaftaran.php" class="nav-link text-white">
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
                        <li>
                            <a href="../logout.php" class="btn btn-outline-light w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Dashboard Siswa</h2>
                            <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($siswa['nama_lengkap']) ?>!</p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">NIS: <?= htmlspecialchars($siswa['nis']) ?></small>
                            <small class="text-muted d-block">Kelas: <?= htmlspecialchars($siswa['kelas']) ?> <?= htmlspecialchars($siswa['jurusan']) ?></small>
                        </div>
                    </div>
                    
                    <!-- Status Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                    <h6>Status Pendaftaran</h6>
                                    <?php if ($pendaftaran): ?>
                                        <span class="badge bg-<?= $pendaftaran['status_pendaftaran'] == 'submitted' ? 'success' : ($pendaftaran['status_pendaftaran'] == 'verified' ? 'primary' : 'warning') ?> status-badge">
                                            <?= ucfirst($pendaftaran['status_pendaftaran']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge">Belum Daftar</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-upload fa-2x text-success mb-2"></i>
                                    <h6>Dokumen</h6>
                                    <span class="badge bg-info status-badge"><?= count($dokumen) ?> File</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                    <h6>Nilai Akhir</h6>
                                    <?php if ($penilaian && $penilaian['nilai_akhir'] > 0): ?>
                                        <span class="badge bg-primary status-badge"><?= number_format($penilaian['nilai_akhir'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge">Belum Dinilai</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-trophy fa-2x text-danger mb-2"></i>
                                    <h6>Status Kelulusan</h6>
                                    <?php if ($penilaian): ?>
                                        <span class="badge bg-<?= $penilaian['status_kelulusan'] == 'lulus' ? 'success' : ($penilaian['status_kelulusan'] == 'tidak_lulus' ? 'danger' : 'warning') ?> status-badge">
                                            <?= ucfirst(str_replace('_', ' ', $penilaian['status_kelulusan'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary status-badge">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Progress Timeline -->
                        <div class="col-md-8 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Progress Seleksi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="progress-step <?= $pendaftaran ? 'active' : 'inactive' ?>">
                                                <div class="step-icon">
                                                    <i class="fas fa-user-plus"></i>
                                                </div>
                                                <h6>Pendaftaran</h6>
                                                <small class="text-muted">Upload berkas persyaratan</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="progress-step <?= ($penilaian && $penilaian['status_kelulusan'] != 'pending') ? 'active' : ($pendaftaran && $pendaftaran['status_pendaftaran'] == 'verified' ? 'pending' : 'inactive') ?>">
                                                <div class="step-icon">
                                                    <i class="fas fa-trophy"></i>
                                                </div>
                                                <h6>Hasil Seleksi</h6>
                                                <small class="text-muted">Pengumuman kelulusan</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$pendaftaran): ?>
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Silakan lengkapi pendaftaran terlebih dahulu untuk memulai proses seleksi OSIS.
                                            <a href="pendaftaran.php" class="btn btn-info btn-sm ms-2">Daftar Sekarang</a>
                                        </div>
                                    <?php elseif (count($dokumen) < 3): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Lengkapi upload dokumen untuk melanjutkan proses seleksi.
                                            <a href="dokumen.php" class="btn btn-warning btn-sm ms-2">Upload Dokumen</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="pendaftaran.php" class="btn btn-primary">
                                            <i class="fas fa-file-alt me-2"></i>
                                            <?= $pendaftaran ? 'Edit Pendaftaran' : 'Mulai Pendaftaran' ?>
                                        </a>
                                        <a href="dokumen.php" class="btn btn-success">
                                            <i class="fas fa-upload me-2"></i>Upload Dokumen
                                        </a>
                                        <a href="status.php" class="btn btn-info">
                                            <i class="fas fa-eye me-2"></i>Lihat Status
                                        </a>
                                        <a href="profil.php" class="btn btn-warning">
                                            <i class="fas fa-user me-2"></i>Edit Profil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Jadwal Seleksi -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Jadwal Seleksi</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($jadwal): ?>
                                        <div class="timeline">
                                            <?php foreach ($jadwal as $item): ?>
                                                <div class="mb-3 p-3 border-start border-3 border-primary">
                                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['tahapan']) ?></h6>
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= format_tanggal($item['tanggal_mulai']) ?> - <?= format_tanggal($item['tanggal_selesai']) ?>
                                                    </p>
                                                    <?php if ($item['lokasi']): ?>
                                                        <p class="mb-1 text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?= htmlspecialchars($item['lokasi']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Belum ada jadwal seleksi yang tersedia.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pengumuman -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Pengumuman Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($pengumuman): ?>
                                        <?php foreach ($pengumuman as $item): ?>
                                            <div class="mb-3 p-3 bg-light rounded">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-<?= $item['tipe'] == 'info' ? 'primary' : ($item['tipe'] == 'jadwal' ? 'warning' : 'success') ?>">
                                                        <?= ucfirst($item['tipe']) ?>
                                                    </span>
                                                    <small class="text-muted"><?= format_tanggal($item['created_at']) ?></small>
                                                </div>
                                                <h6 class="fw-bold mb-2"><?= htmlspecialchars($item['judul']) ?></h6>
                                                <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars(substr($item['isi'], 0, 100))) ?>...</p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Belum ada pengumuman terbaru.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nilai Detail (jika sudah ada penilaian) -->
                    <?php if ($penilaian && $penilaian['nilai_akhir'] > 0): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Detail Penilaian</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2 text-center">
                                                <h6>Akademik</h6>
                                                <span class="badge bg-primary fs-6"><?= number_format($penilaian['nilai_akademik'], 1) ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <h6>Kepemimpinan</h6>
                                                <span class="badge bg-success fs-6"><?= number_format($penilaian['nilai_kepemimpinan'], 1) ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <h6>Komunikasi</h6>
                                                <span class="badge bg-info fs-6"><?= number_format($penilaian['nilai_komunikasi'], 1) ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <h6>Organisasi</h6>
                                                <span class="badge bg-warning fs-6"><?= number_format($penilaian['nilai_organisasi'], 1) ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <h6>Wawancara</h6>
                                                <span class="badge bg-secondary fs-6"><?= number_format($penilaian['nilai_wawancara'], 1) ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <h6>Nilai Akhir</h6>
                                                <span class="badge bg-danger fs-6"><?= number_format($penilaian['nilai_akhir'], 2) ?></span>
                                            </div>
                                        </div>
                                        <?php if ($penilaian['catatan']): ?>
                                            <div class="mt-3">
                                                <h6>Catatan Penilai:</h6>
                                                <div class="alert alert-light">
                                                    <?= nl2br(htmlspecialchars($penilaian['catatan'])) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>