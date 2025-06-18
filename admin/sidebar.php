<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
                .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
        }
    </style>
</head>
<body>
<div class="sidebar d-flex flex-column p-3">
    <div class="text-center text-white mb-4">
        <i class="fas fa-user-shield fa-3x mb-2"></i>
        <h5><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h5>
        <small>Administrator</small>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="dashboard.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="pendaftar.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'pendaftar.php' ? 'active' : '' ?>">
                <i class="fas fa-users me-2"></i>Data Pendaftar
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="penilaian.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'penilaian.php' ? 'active' : '' ?>">
                <i class="fas fa-star me-2"></i>Penilaian
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="jadwal.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'jadwal.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt me-2"></i>Jadwal Seleksi
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="pengumuman.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'pengumuman.php' ? 'active' : '' ?>">
                <i class="fas fa-bullhorn me-2"></i>Pengumuman
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="laporan.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar me-2"></i>Laporan
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="users.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-user-cog me-2"></i>Kelola User
            </a>
        </li>
        <li class="mt-auto">
            <a href="../logout.php" class="btn btn-outline-light w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </li>
    </ul>
    
</div>
</body>
</html>

