<?php
/**
 * Sanitize user input
 * 
 * @param string $data The input data to sanitize
 * @return string The sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to login page if user is not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Anda harus login terlebih dahulu.';
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Redirect to dashboard if user is not admin
 */
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        $_SESSION['error_message'] = 'Anda tidak memiliki akses ke halaman ini.';
        header('Location: ../student/dashboard.php');
        exit;
    }
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the random string
 * @return string The random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date The date to format
 * @param bool $with_time Whether to include time
 * @return string The formatted date
 */
function format_date($date, $with_time = false) {
    $timestamp = strtotime($date);
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    $formatted_date = "$day $month $year";
    
    if ($with_time) {
        $time = date('H:i', $timestamp);
        $formatted_date .= " $time";
    }
    
    return $formatted_date;
}

/**
 * Upload file
 * 
 * @param array $file The file to upload
 * @param string $directory The directory to upload to
 * @return array|bool The uploaded file info or false on failure
 */
function upload_file($file, $directory = UPLOAD_DIR) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERROR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file extension
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return false;
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Generate unique filename
    $new_filename = generate_random_string() . '.' . $extension;
    $upload_path = $directory . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'filename' => $new_filename,
            'path' => $upload_path,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    return false;
}

/**
 * Get user status label
 * 
 * @param string $status The status code
 * @return string The status label
 */
function get_status_label($status) {
    $labels = [
        'pending' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'interview_scheduled' => 'Terjadwal Wawancara',
        'interview_completed' => 'Wawancara Selesai',
        'selected' => 'Terpilih',
        'not_selected' => 'Tidak Terpilih'
    ];
    
    return isset($labels[$status]) ? $labels[$status] : 'Unknown';
}

/**
 * Get user status badge class
 * 
 * @param string $status The status code
 * @return string The badge class
 */
function get_status_badge_class($status) {
    $classes = [
        'pending' => 'badge-warning',
        'verified' => 'badge-success',
        'interview_scheduled' => 'badge-info',
        'interview_completed' => 'badge-info',
        'selected' => 'badge-success',
        'not_selected' => 'badge-danger'
    ];
    
    return isset($classes[$status]) ? $classes[$status] : 'badge-secondary';
}
?>
