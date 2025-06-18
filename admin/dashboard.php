<?php
require_once '../config.php';
check_login();
check_admin();

// Get statistics
$stats = [];

// Total pendaftar
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pendaftaran_osis");
$stmt->execute();
$stats['total_pendaftar'] = $stmt->fetchColumn();

// Pendaftar yang sudah submit
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pendaftaran_osis WHERE status_pendaftaran IN ('submitted', 'verified')");
$stmt->execute();
$stats['pendaftar_submit'] = $stmt->fetchColumn();

// Pendaftar yang sudah dinilai
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM penilaian WHERE nilai_akhir > 0");
$stmt->execute();
$stats['pendaftar_dinilai'] = $stmt->fetchColumn();

// Pendaftar yang lulus
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM penilaian WHERE status_kelulusan = 'lulus'");
$stmt->execute();
$stats['pendaftar_lulus'] = $stmt->fetchColumn();

// Recent registrations
$stmt = $pdo->prepare("
    SELECT p.*, u.nama_lengkap, s.nis, s.kelas, s.jurusan, u.email
    FROM pendaftaran_osis p 
    JOIN siswa s ON p.siswa_id = s.id 
    JOIN users u ON s.user_id = u.id 
    ORDER BY p.tanggal_daftar DESC 
    LIMIT 10
");

$stmt->execute();
$recent_registrations = $stmt->fetchAll();

// Pending verifications
$stmt = $pdo->prepare("
    SELECT p.*, u.nama_lengkap, s.nis, s.kelas, s.jurusan
    FROM pendaftaran_osis p 
    JOIN siswa s ON p.siswa_id = s.id 
    JOIN users u ON s.user_id = u.id
    WHERE p.status_pendaftaran = 'submitted'
    ORDER BY p.tanggal_daftar ASC
");

$stmt->execute();
$pending_verifications = $stmt->fetchAll();

// Get jadwal seleksi
$stmt = $pdo->prepare("SELECT * FROM jadwal_seleksi WHERE is_active = 1 ORDER BY tanggal_mulai ASC LIMIT 5");
$stmt->execute();
$jadwal = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem OSIS SMKN 2 Sampang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
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
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .stat-card-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .stat-card-warning {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
        .nav-link.active {
            background-color: rgba(255,255,255,0.2) !important;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Dashboard Administrator</h2>
                            <p class="text-muted mb-0">Selamat datang di sistem pengelolaan OSIS SMKN 2 Sampang</p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Tanggal: <?= date('d-m-Y') ?></small>
                            <small class="text-muted d-block">Waktu: <?= date('H:i:s') ?></small>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h3 class="mb-1"><?= $stats['total_pendaftar'] ?></h3>
                                    <h6 class="mb-0">Total Pendaftar</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h3 class="mb-1"><?= $stats['pendaftar_submit'] ?></h3>
                                    <h6 class="mb-0">Sudah Submit</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card-success h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-star fa-3x mb-3"></i>
                                    <h3 class="mb-1"><?= $stats['pendaftar_dinilai'] ?></h3>
                                    <h6 class="mb-0">Sudah Dinilai</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card-warning h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-trophy fa-3x mb-3"></i>
                                    <h3 class="mb-1"><?= $stats['pendaftar_lulus'] ?></h3>
                                    <h6 class="mb-0">Lulus Seleksi</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Recent Registrations -->
                        <div class="col-md-8 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Pendaftaran Terbaru</h5>
                                    <a href="pendaftar.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                                </div>
                                <div class="card-body">
                                    <?php if ($recent_registrations): ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Nama</th>
                                                        <th>NIS</th>
                                                        <th>Kelas</th>
                                                        <th>Status</th>
                                                        <th>Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_registrations as $reg): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($reg['nama_lengkap']) ?></td>
                                                            <td><?= htmlspecialchars($reg['nis']) ?></td>
                                                            <td><?= htmlspecialchars($reg['kelas']) ?> <?= htmlspecialchars($reg['jurusan']) ?></td>
                                                            <td>
                                                                <span class="badge bg-<?= $reg['status_pendaftaran'] == 'submitted' ? 'success' : ($reg['status_pendaftaran'] == 'verified' ? 'primary' : 'warning') ?>">
                                                                    <?= ucfirst($reg['status_pendaftaran']) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= date('d/m/Y', strtotime($reg['tanggal_daftar'])) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">Belum ada pendaftaran.</p>
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
                                        <a href="pendaftar.php" class="btn btn-primary">
                                            <i class="fas fa-users me-2"></i>Kelola Pendaftar
                                        </a>
                                        <a href="penilaian.php" class="btn btn-success">
                                            <i class="fas fa-star me-2"></i>Input Penilaian
                                        </a>
                                        <a href="jadwal.php" class="btn btn-info">
                                            <i class="fas fa-calendar-plus me-2"></i>Tambah Jadwal
                                        </a>
                                        <a href="pengumuman.php" class="btn btn-warning">
                                            <i class="fas fa-bullhorn me-2"></i>Buat Pengumuman
                                        </a>
                                        <a href="laporan.php" class="btn btn-secondary">
                                            <i class="fas fa-file-pdf me-2"></i>Cetak Laporan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Pending Verifications -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Menunggu Verifikasi</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($pending_verifications): ?>
                                        <?php foreach ($pending_verifications as $pending): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($pending['nama_lengkap']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($pending['nis']) ?> - <?= htmlspecialchars($pending['kelas']) ?> <?= htmlspecialchars($pending['jurusan']) ?></small>
                                                </div>
                                                <div>
                                                    <a href="pendaftar.php?id=<?= $pending['id'] ?>" class="btn btn-sm btn-primary">Verifikasi</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="text-center">
                                            <a href="pendaftar.php?filter=submitted" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-3">Tidak ada pendaftaran yang menunggu verifikasi.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Jadwal Seleksi -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Jadwal Seleksi</h5>
                                    <a href="jadwal.php" class="btn btn-sm btn-outline-primary">Kelola</a>
                                </div>
                                <div class="card-body">
                                    <?php if ($jadwal): ?>
                                        <?php foreach ($jadwal as $item): ?>
                                            <div class="mb-3 p-3 border-start border-3 border-primary">
                                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['tahapan']) ?></h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?= format_tanggal($item['tanggal_mulai']) ?>
                                                </p>
                                                <?php if ($item['lokasi']): ?>
                                                    <p class="mb-0 text-muted small">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= htmlspecialchars($item['lokasi']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-3">Belum ada jadwal seleksi.</p>
                                    <?php endif; ?>
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
        // Auto refresh statistics every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
        
        // Update time every second
        setInterval(function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            document.querySelector('.text-end small:last-child').textContent = 'Waktu: ' + timeString;
        }, 1000);
    </script>
</body>
</html>