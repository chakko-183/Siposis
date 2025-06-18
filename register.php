<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $nis = sanitize_input($_POST['nis']);
    $kelas = sanitize_input($_POST['kelas']);
    $jurusan = sanitize_input($_POST['jurusan']);
    $no_hp = sanitize_input($_POST['no_hp']);
    $alamat = sanitize_input($_POST['alamat']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap) || empty($nis) || empty($kelas) || empty($jurusan)) {
        $error = 'Semua field wajib harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username atau email sudah digunakan!';
            } else {
                // Check if NIS already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nis = ?");
                $stmt->execute([$nis]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'NIS sudah terdaftar!';
                } else {
                    $pdo->beginTransaction();
                    
                    // Insert into users table
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, nama_lengkap, role) VALUES (?, ?, ?, ?, 'siswa')");
                    $stmt->execute([$username, $email, md5($password), $nama_lengkap]);
                    $user_id = $pdo->lastInsertId();
                    
                    // Insert into siswa table
                    $stmt = $pdo->prepare("INSERT INTO siswa (user_id, nis, kelas, jurusan, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $nis, $kelas, $jurusan, $no_hp, $alamat]);
                    
                    $pdo->commit();
                    $success = 'Registrasi berhasil! Silakan login untuk melanjutkan.';
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Redirect if already logged in
if (is_logged_in()) {
    redirect('siswa/dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem OSIS SMKN 2 Sampang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-card">
                    <div class="register-header text-center py-4">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h4 class="mb-0">Daftar Akun Siswa</h4>
                        <p class="mb-0">SMKN 2 Sampang</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-2"></i>Username *
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Username untuk login" required
                                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Email aktif" required
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password *
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Minimal 6 karakter" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Konfirmasi Password *
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Ulangi password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">
                                    <i class="fas fa-id-card me-2"></i>Nama Lengkap *
                                </label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                       placeholder="Nama lengkap sesuai rapor" required
                                       value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="nis" class="form-label">
                                        <i class="fas fa-hashtag me-2"></i>NIS *
                                    </label>
                                    <input type="text" class="form-control" id="nis" name="nis" 
                                           placeholder="Nomor Induk Siswa" required
                                           value="<?= isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="kelas" class="form-label">
                                        <i class="fas fa-chalkboard me-2"></i>Kelas *
                                    </label>
                                    <select class="form-control" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <option value="X" <?= (isset($_POST['kelas']) && $_POST['kelas'] == 'X') ? 'selected' : '' ?>>X</option>
                                        <option value="XI" <?= (isset($_POST['kelas']) && $_POST['kelas'] == 'XI') ? 'selected' : '' ?>>XI</option>
                                        <option value="XII" <?= (isset($_POST['kelas']) && $_POST['kelas'] == 'XII') ? 'selected' : '' ?>>XII</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="jurusan" class="form-label">
                                        <i class="fas fa-graduation-cap me-2"></i>Jurusan *
                                    </label>
                                    <select class="form-control" id="jurusan" name="jurusan" required>
                                        <option value="">Pilih Jurusan</option>
                                        <option value="TKJ" <?= (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TKJ') ? 'selected' : '' ?>>TKJ</option>
                                        <option value="RPL" <?= (isset($_POST['jurusan']) && $_POST['jurusan'] == 'RPL') ? 'selected' : '' ?>>RPL</option>
                                        <option value="MM" <?= (isset($_POST['jurusan']) && $_POST['jurusan'] == 'MM') ? 'selected' : '' ?>>MM</option>
                                        <option value="TKR" <?= (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TKR') ? 'selected' : '' ?>>TKR</option>
                                        <option value="TSM" <?= (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TSM') ? 'selected' : '' ?>>TSM</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">
                                    <i class="fas fa-phone me-2"></i>No. HP
                                </label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                       placeholder="Nomor HP aktif"
                                       value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Alamat
                                </label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                          placeholder="Alamat lengkap"><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Daftar Akun
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? 
                                <a href="login.php" class="text-decoration-none">Login di sini</a>
                            </p>
                            <hr>
                            <p class="text-center mb-0">
                                <a href="index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });
    </script>
</body>
</html>