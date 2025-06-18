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

// Get all pengumuman for siswa
$stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE (target_audience = 'all' OR target_audience = 'siswa') AND is_published = 1 ORDER BY created_at DESC");
$stmt->execute();
$pengumuman = $stmt->fetchAll();

// Get penilaian if exists
$penilaian = null;
if ($pendaftaran) {
    $stmt = $pdo->prepare("SELECT * FROM penilaian WHERE pendaftaran_id = ?");
    $stmt->execute([$pendaftaran['id']]);
    $penilaian = $stmt->fetch();
}

// Helper function to get status color
function getStatusColor($status) {
    switch ($status) {
        case 'submitted': return 'success';
        case 'verified': return 'primary';
        case 'pending': return 'warning';
        case 'lulus': return 'success';
        case 'tidak_lulus': return 'danger';
        default: return 'secondary';
    }
}

// Helper function to get status text
function getStatusText($status) {
    switch ($status) {
        case 'submitted': return 'Berkas Terkirim';
        case 'verified': return 'Berkas Terverifikasi';
        case 'pending': return 'Menunggu Verifikasi';
        case 'lulus': return 'Lulus Seleksi';
        case 'tidak_lulus': return 'Tidak Lulus';
        default: return ucfirst($status);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status & Informasi - Sistem OSIS SMKN 2 Sampang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .status-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 600;
        }
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
        }
        .timeline-item.completed::before {
            background: #28a745;
        }
        .timeline-item.pending::before {
            background: #ffc107;
        }
        .timeline-item.inactive::before {
            background: #6c757d;
        }
        .info-stat {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }
        .announcement-item {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .announcement-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .schedule-card {
            border-left: 5px solid #28a745;
            background: linear-gradient(to right, #f8f9fa, #ffffff);
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.35s;
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <?php include 'sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card status-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-2"><i class="fas fa-info-circle me-2"></i>Status & Informasi</h3>
                                            <p class="mb-0 opacity-75">Pantau status pendaftaran dan informasi terkini seleksi OSIS</p>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="mb-1"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h5>
                                            <small class="opacity-75">NIS: <?= htmlspecialchars($siswa['nis']) ?> | <?= htmlspecialchars($siswa['kelas']) ?> <?= htmlspecialchars($siswa['jurusan']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Overview Cards -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="info-stat">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <h6>Status Pendaftaran</h6>
                                <?php if ($pendaftaran): ?>
                                    <span class="badge bg-light text-dark status-badge">
                                        <?= getStatusText($pendaftaran['status_pendaftaran']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark status-badge">Belum Mendaftar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="info-stat">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <h6>Dokumen Uploaded</h6>
                                <h3 class="mb-0"><?= count($dokumen) ?> <small>file</small></h3>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="info-stat">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h6>Nilai Akhir</h6>
                                <?php if ($penilaian && $penilaian['nilai_akhir'] > 0): ?>
                                    <h3 class="mb-0"><?= number_format($penilaian['nilai_akhir'], 1) ?></h3>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Belum Dinilai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="info-stat">
                                <i class="fas fa-trophy fa-2x mb-2"></i>
                                <h6>Status Kelulusan</h6>
                                <?php if ($penilaian): ?>
                                    <span class="badge bg-light text-dark status-badge">
                                        <?= getStatusText($penilaian['status_kelulusan']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark status-badge">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status Detail & Timeline -->
                        <div class="col-lg-8 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Status Pendaftaran Detail</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Progress Timeline -->
                                    <div class="timeline">
                                        <div class="timeline-item <?= $pendaftaran ? 'completed' : 'inactive' ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">Pendaftaran</h6>
                                                    <p class="mb-0 text-muted small">Mengisi formulir pendaftaran OSIS</p>
                                                </div>
                                                <div>
                                                    <?php if ($pendaftaran): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Selesai</span>
                                                    <?php else: ?>
                                                        <a href="pendaftaran.php" class="btn btn-primary btn-sm">Mulai Daftar</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="timeline-item <?= count($dokumen) >= 3 ? 'completed' : ($pendaftaran ? 'pending' : 'inactive') ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">Upload Dokumen</h6>
                                                    <p class="mb-0 text-muted small">Upload berkas persyaratan (<?= count($dokumen) ?>/3 file)</p>
                                                </div>
                                                <div>
                                                    <?php if (count($dokumen) >= 3): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Lengkap</span>
                                                    <?php elseif ($pendaftaran): ?>
                                                        <a href="dokumen.php" class="btn btn-warning btn-sm">Upload</a>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Menunggu</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="timeline-item <?= ($pendaftaran && $pendaftaran['status_pendaftaran'] == 'verified') ? 'completed' : 'pending' ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">Verifikasi Berkas</h6>
                                                    <p class="mb-0 text-muted small">Verifikasi kelengkapan dokumen oleh admin</p>
                                                </div>
                                                <div>
                                                    <?php if ($pendaftaran && $pendaftaran['status_pendaftaran'] == 'verified'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check"></i> Terverifikasi</span>
                                                    <?php elseif ($pendaftaran): ?>
                                                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Belum Aktif</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="timeline-item <?= ($penilaian && $penilaian['status_kelulusan'] != 'pending') ? 'completed' : 'pending' ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">Pengumuman Hasil</h6>
                                                    <p class="mb-0 text-muted small">Pengumuman kelulusan seleksi OSIS</p>
                                                </div>
                                                <div>
                                                    <?php if ($penilaian && $penilaian['status_kelulusan'] == 'lulus'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-trophy"></i> Lulus</span>
                                                    <?php elseif ($penilaian && $penilaian['status_kelulusan'] == 'tidak_lulus'): ?>
                                                        <span class="badge bg-danger"><i class="fas fa-times"></i> Tidak Lulus</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Menunggu Pengumuman</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-4">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?php if (!$pendaftaran): ?>
                                                <a href="pendaftaran.php" class="btn btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Mulai Pendaftaran
                                                </a>
                                            <?php else: ?>
                                                <a href="pendaftaran.php" class="btn btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i>Edit Pendaftaran
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="dokumen.php" class="btn btn-outline-success">
                                                <i class="fas fa-upload me-1"></i>Kelola Dokumen
                                            </a>
                                            
                                            <a href="profil.php" class="btn btn-outline-warning">
                                                <i class="fas fa-user me-1"></i>Edit Profil
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jadwal Seleksi -->
                        <div class="col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Jadwal Seleksi</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($jadwal): ?>
                                        <?php foreach ($jadwal as $item): ?>
                                            <div class="schedule-card p-3 mb-3 rounded">
                                                <h6 class="fw-bold text-success mb-2"><?= htmlspecialchars($item['tahapan']) ?></h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?= format_tanggal($item['tanggal_mulai']) ?>
                                                    </small>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        s/d <?= format_tanggal($item['tanggal_selesai']) ?>
                                                    </small>
                                                </div>
                                                <?php if ($item['lokasi']): ?>
                                                    <div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            <?= htmlspecialchars($item['lokasi']) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada jadwal seleksi yang tersedia</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengumuman -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Pengumuman & Informasi</h5>
                                    <span class="badge bg-light text-info"><?= count($pengumuman) ?> pengumuman</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($pengumuman): ?>
                                        <div class="row">
                                            <?php foreach ($pengumuman as $index => $item): ?>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="announcement-item p-3 rounded h-100">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <span class="badge bg-<?= $item['tipe'] == 'info' ? 'primary' : ($item['tipe'] == 'jadwal' ? 'warning' : ($item['tipe'] == 'hasil' ? 'success' : 'secondary')) ?>">
                                                                <i class="fas fa-<?= $item['tipe'] == 'info' ? 'info-circle' : ($item['tipe'] == 'jadwal' ? 'calendar' : ($item['tipe'] == 'hasil' ? 'trophy' : 'bell')) ?> me-1"></i>
                                                                <?= ucfirst($item['tipe']) ?>
                                                            </span>
                                                            <small class="text-muted"><?= format_tanggal($item['created_at']) ?></small>
                                                        </div>
                                                        <h6 class="fw-bold mb-2"><?= htmlspecialchars($item['judul']) ?></h6>
                                                        <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars(substr($item['isi'], 0, 120))) ?><?= strlen($item['isi']) > 120 ? '...' : '' ?></p>
                                                        <?php if (strlen($item['isi']) > 120): ?>
                                                            <button class="btn btn-link btn-sm p-0 mt-2" data-bs-toggle="modal" data-bs-target="#pengumumanModal<?= $index ?>">
                                                                Baca selengkapnya <i class="fas fa-arrow-right"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Modal for full announcement -->
                                                <?php if (strlen($item['isi']) > 120): ?>
                                                    <div class="modal fade" id="pengumumanModal<?= $index ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"><?= htmlspecialchars($item['judul']) ?></h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <span class="badge bg-<?= $item['tipe'] == 'info' ? 'primary' : ($item['tipe'] == 'jadwal' ? 'warning' : ($item['tipe'] == 'hasil' ? 'success' : 'secondary')) ?> me-2">
                                                                            <?= ucfirst($item['tipe']) ?>
                                                                        </span>
                                                                        <small class="text-muted"><?= format_tanggal($item['created_at']) ?></small>
                                                                    </div>
                                                                    <div class="announcement-content">
                                                                        <?= nl2br(htmlspecialchars($item['isi'])) ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum Ada Pengumuman</h5>
                                            <p class="text-muted">Pengumuman terbaru akan ditampilkan di sini</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Penilaian (jika sudah ada) -->
                    <?php if ($penilaian && $penilaian['nilai_akhir'] > 0): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Detail Penilaian Seleksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                                    <h6>Akademik</h6>
                                                    <span class="badge bg-primary fs-6"><?= number_format($penilaian['nilai_akademik'], 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                                                    <h6>Kepemimpinan</h6>
                                                    <span class="badge bg-success fs-6"><?= number_format($penilaian['nilai_kepemimpinan'], 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <i class="fas fa-comments fa-2x text-info mb-2"></i>
                                                    <h6>Komunikasi</h6>
                                                    <span class="badge bg-info fs-6"><?= number_format($penilaian['nilai_komunikasi'], 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <i class="fas fa-sitemap fa-2x text-warning mb-2"></i>
                                                    <h6>Organisasi</h6>
                                                    <span class="badge bg-warning fs-6"><?= number_format($penilaian['nilai_organisasi'], 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <i class="fas fa-microphone fa-2x text-secondary mb-2"></i>
                                                    <h6>Wawancara</h6>
                                                    <span class="badge bg-secondary fs-6"><?= number_format($penilaian['nilai_wawancara'], 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <div class="p-3 bg-primary text-white rounded">
                                                    <i class="fas fa-trophy fa-2x mb-2"></i>
                                                    <h6>Nilai Akhir</h6>
                                                    <span class="badge bg-light text-primary fs-5"><?= number_format($penilaian['nilai_akhir'], 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($penilaian['catatan']): ?>
                                            <div class="mt-4">
                                                <h6><i class="fas fa-sticky-note me-2"></i>Catatan dari Penilai:</h6>
                                                <div class="alert alert-light border">
                                                    <i class="fas fa-quote-left text-muted me-2"></i>
                                                    <?= nl2br(htmlspecialchars($penilaian['catatan'])) ?>
                                                    <i class="fas fa-quote-right text-muted ms-2"></i>
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
    <script>
        // Auto refresh page every 5 minutes to get latest updates
        setTimeout(function(){
            location.reload();
        }, 300000);
        
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>