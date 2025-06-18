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
        <i class="fas fa-user-graduate fa-3x mb-2"></i>
        <h5><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h5>
        <small>Siswa</small>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="dashboard.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="pendaftaran.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'pendaftaran.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt me-2"></i>Pendaftaran
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="dokumen.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dokumen.php' ? 'active' : '' ?>">
                <i class="fas fa-upload me-2"></i>Upload Dokumen
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="status.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'status.php' ? 'active' : '' ?>">
                <i class="fas fa-check-circle me-2"></i>Status & Hasil
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="profil.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>">
                <i class="fas fa-user me-2"></i>Profil
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

