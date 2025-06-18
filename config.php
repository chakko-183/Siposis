<?php
// config.php - Konfigurasi Database dan Session
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password kosong
define('DB_NAME', 'osis_registration');

// Base URL Configuration
define('BASE_URL', 'http://localhost/osis-system/'); 

// Upload Configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Database Connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Helper Functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_siswa() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'siswa';
}

function check_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function check_admin() {
    if (!is_admin()) {
        redirect('index.php');
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function format_tanggal($tanggal) {
    return date('d-m-Y H:i', strtotime($tanggal));
}

function generate_unique_filename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Create upload directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');
?>