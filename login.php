<?php
// Initialize session
session_start();

// Include database connection
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user role
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: student/dashboard.php');
    }
    exit;
}

// Initialize variables
$email = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email wajib diisi';
    } else {
        $email = sanitize_input($_POST['email']);
    }

    // Validate password
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password wajib diisi';
    }

    // If no errors, proceed with login
    if (empty($errors)) {
        $password = $_POST['password'];
        
        // Retrieve user from database
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: student/dashboard.php');
                }
                exit;
            } else {
                $errors['login_failed'] = 'Email atau password salah';
            }
        } else {
            $errors['login_failed'] = 'Email atau password salah';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pendaftaran OSIS SMKN 2 Sampang</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            <h2 class="form-title">Masuk ke Akun Anda</h2>
            <p class="text-center">Belum punya akun? <a href="register.php">Daftar di sini</a></p>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['login_failed'])): ?>
                <div class="alert alert-danger">
                    <?php echo $errors['login_failed']; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
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

                <div class="form-check">
                    <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                    <label for="remember_me" class="form-check-label">Ingat saya</label>
                </div>

                <div class="form-group">
                    <a href="forgot-password.php" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>

            <div class="demo-login">
                <p>Demo login:</p>
                <p>Admin: admin@example.com / admin123</p>
                <p>Siswa: siswa@example.com / siswa123</p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
