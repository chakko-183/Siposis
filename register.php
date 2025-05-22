<?php
// Initialize session
session_start();

// Include database connection
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize variables
$name = $nis = $email = $password = $confirm_password = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate name
    if (empty($_POST['name'])) {
        $errors['name'] = 'Nama lengkap wajib diisi';
    } else {
        $name = sanitize_input($_POST['name']);
    }

    // Validate NIS
    if (empty($_POST['nis'])) {
        $errors['nis'] = 'NIS wajib diisi';
    } elseif (!is_numeric($_POST['nis'])) {
        $errors['nis'] = 'NIS harus berupa angka';
    } else {
        $nis = sanitize_input($_POST['nis']);
        
        // Check if NIS already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['nis'] = 'NIS sudah terdaftar';
        }
    }

    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    } else {
        $email = sanitize_input($_POST['email']);
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['email'] = 'Email sudah terdaftar';
        }
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password wajib diisi';
    } elseif (strlen($_POST['password']) < 8) {
        $errors['password'] = 'Password minimal 8 karakter';
    } else {
        $password = $_POST['password'];
    }

    // Validate confirm password
    if (empty($_POST['confirm_password'])) {
        $errors['confirm_password'] = 'Konfirmasi password wajib diisi';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Password tidak cocok';
    } else {
        $confirm_password = $_POST['confirm_password'];
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, nis, email, password, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->bind_param("ssss", $name, $nis, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['success_message'] = 'Pendaftaran berhasil! Silakan login dengan akun yang telah Anda buat.';
            
            // Redirect to login page
            header('Location: login.php');
            exit;
        } else {
            $errors['db_error'] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Pendaftaran OSIS SMKN 2 Sampang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            <h2 class="form-title">Daftar Akun</h2>
            <p class="text-center">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>

            <?php if (!empty($errors['db_error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $errors['db_error']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>" placeholder="Masukkan nama lengkap">
                    <?php if (!empty($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nis" class="form-label">NIS (Nomor Induk Siswa)</label>
                    <input type="text" id="nis" name="nis" class="form-control <?php echo !empty($errors['nis']) ? 'is-invalid' : ''; ?>" value="<?php echo $nis; ?>" placeholder="Masukkan NIS">
                    <?php if (!empty($errors['nis'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['nis']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Masukkan email">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control <?php echo !empty($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="Masukkan password">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>" placeholder="Masukkan ulang password">
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                    <label for="terms" class="form-check-label">Dengan mendaftar, Anda menyetujui Syarat dan Ketentuan serta Kebijakan Privasi kami.</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Daftar</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
