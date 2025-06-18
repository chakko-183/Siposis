<?php
require_once 'config.php';

// Get latest announcements
$stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE is_published = 1 ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$pengumuman = $stmt->fetchAll();

// Get jadwal seleksi
$stmt = $pdo->prepare("SELECT * FROM jadwal_seleksi WHERE is_active = 1 ORDER BY tanggal_mulai ASC");
$stmt->execute();
$jadwal = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pendaftaran OSIS - SMKN 2 Sampang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .timeline {
            position: relative;
            padding: 0;
        }
        .timeline-item {
            margin-bottom: 20px;
            position: relative;
            padding-left: 40px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 2px;
            background: #667eea;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 10px;
            top: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-school"></i> OSIS SMKN 2 Sampang
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#jadwal">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pengumuman">Pengumuman</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Dashboard Admin</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="siswa/dashboard.php">Dashboard Siswa</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Selamat Datang</h1>
            <p class="lead mb-4">Sistem Informasi Pendaftaran dan Seleksi Calon Pengurus OSIS<br>SMKN 2 Sampang</p>
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </a>
                <a href="login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php else: ?>
                <?php if (is_siswa()): ?>
                    <a href="siswa/dashboard.php" class="btn btn-light btn-lg">
                        <i class="fas fa-tachometer-alt"></i> Dashboard Siswa
                    </a>
                <?php else: ?>
                    <a href="admin/dashboard.php" class="btn btn-light btn-lg">
                        <i class="fas fa-cogs"></i> Dashboard Admin
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2 class="display-5 fw-bold">Fitur Unggulan</h2>
                    <p class="lead text-muted">Sistem modern untuk pendaftaran OSIS yang efisien dan transparan</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-laptop fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Pendaftaran Online</h5>
                            <p class="card-text">Daftar menjadi calon pengurus OSIS secara online kapan saja dan dimana saja</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Upload Dokumen</h5>
                            <p class="card-text">Upload dokumen persyaratan dengan mudah dan aman dalam format digital</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Monitoring Real-time</h5>
                            <p class="card-text">Pantau status pendaftaran dan hasil seleksi secara real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Jadwal Section -->
    <section id="jadwal" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 class="fw-bold mb-4">
                        <i class="fas fa-calendar-alt text-primary"></i> Jadwal Seleksi
                    </h2>
                    <div class="timeline">
                        <?php foreach ($jadwal as $item): ?>
                            <div class="timeline-item">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold"><?= htmlspecialchars($item['tahapan']) ?></h6>
                                        <p class="card-text mb-1">
                                            <i class="fas fa-clock text-primary"></i>
                                            <?= format_tanggal($item['tanggal_mulai']) ?> - <?= format_tanggal($item['tanggal_selesai']) ?>
                                        </p>
                                        <?php if ($item['lokasi']): ?>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                <?= htmlspecialchars($item['lokasi']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($item['deskripsi']): ?>
                                            <p class="card-text text-muted small"><?= htmlspecialchars($item['deskripsi']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">
                        <i class="fas fa-info-circle text-info"></i> Persyaratan Pendaftaran
                    </h2>
                    <div class="card">
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Siswa aktif SMKN 2 Sampang
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Surat izin orang tua
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Pas foto formal 3x4
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Fotokopi nilai rapor semester terakhir
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Surat rekomendasi dari wali kelas
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Sertifikat prestasi (jika ada)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pengumuman Section -->
    <section id="pengumuman" class="py-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-5">
                <i class="fas fa-bullhorn text-warning"></i> Pengumuman Terbaru
            </h2>
            <div class="row">
                <?php foreach ($pengumuman as $item): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?= $item['tipe'] == 'info' ? 'primary' : ($item['tipe'] == 'jadwal' ? 'warning' : 'success') ?>">
                                    <?= ucfirst($item['tipe']) ?>
                                </span>
                                <small class="text-muted"><?= format_tanggal($item['created_at']) ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['judul']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($item['isi'])) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>SMKN 2 Sampang</h5>
                    <p class="mb-0">Sistem Informasi Pendaftaran dan Seleksi Calon OSIS</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 SMKN 2 Sampang. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>