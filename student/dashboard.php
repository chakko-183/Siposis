<?php
// Initialize session
session_start();

// Include database connection and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
require_login();

if (is_admin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get registration data if exists
$stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registration = $stmt->get_result()->fetch_assoc();

// Calculate progress
$progress = 0;
$registration_status = 'Belum Mendaftar';

if ($registration) {
    switch ($registration['status']) {
        case 'pending':
            $progress = 25;
            $registration_status = 'Menunggu Verifikasi';
            break;
        case 'verified':
            $progress = 50;
            $registration_status = 'Terverifikasi';
            break;
        case 'interview_scheduled':
            $progress = 75;
            $registration_status = 'Terjadwal Wawancara';
            break;
        case 'interview_completed':
            $progress = 90;
            $registration_status = 'Wawancara Selesai';
            break;
        case 'selected':
            $progress = 100;
            $registration_status = 'Terpilih';
            break;
        case 'not_selected':
            $progress = 100;
            $registration_status = 'Tidak Terpilih';
            break;
    }
}

// Get notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get schedules
$stmt = $conn->prepare("
    SELECT s.* FROM schedules s
    LEFT JOIN interview_schedules i ON s.id = i.schedule_id
    WHERE i.user_id = ? OR s.type = 'general'
    ORDER BY s.date ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get documents
$documents = [];
if ($registration) {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE registration_id = ?");
    $stmt->bind_param("i", $registration['id']);
    $stmt->execute();
    $documents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/user-placeholder.png" alt="<?php echo $user['name']; ?>">
                <h3><?php echo $user['name']; ?></h3>
                <p><?php echo $registration ? $registration['class'] : 'Belum Mendaftar'; ?></p>
                <span class="badge <?php echo $registration ? get_status_badge_class($registration['status']) : 'badge-warning'; ?>">
                    <?php echo $registration_status; ?>
                </span>
            </div>
            
            <div class="sidebar-menu">
                <h3>Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profil</a></li>
                    <li><a href="documents.php"><i class="fas fa-file-alt"></i> Dokumen</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a></li>
                    <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifikasi</a></li>
                </ul>
            </div>
            
            <div class="sidebar-menu">
                <h3>Lainnya</h3>
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
                    <li><a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Dashboard Siswa</h2>
                <div class="dashboard-actions">
                    <a href="#" class="btn btn-icon"><i class="fas fa-bell"></i></a>
                    <div class="user-dropdown">
                        <span><?php echo $user['name']; ?></span>
                        <img src="../assets/img/user-placeholder.png" alt="<?php echo $user['name']; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Status Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Pendaftaran</h3>
                </div>
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Progres Keseluruhan</span>
                            <span><?php echo $progress; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="status-grid">
                        <div class="status-item <?php echo $registration ? 'status-complete' : 'status-incomplete'; ?>">
                            <i class="fas fa-user-check"></i>
                            <div>
                                <h4>Profil</h4>
                                <p><?php echo $registration ? 'Lengkap' : 'Belum Lengkap'; ?></p>
                            </div>
                        </div>
                        
                        <div class="status-item <?php echo ($registration && $registration['status'] != 'pending') ? 'status-complete' : 'status-incomplete'; ?>">
                            <i class="fas fa-file-check"></i>
                            <div>
                                <h4>Dokumen</h4>
                                <p><?php echo ($registration && $registration['status'] != 'pending') ? 'Terverifikasi' : 'Belum Terverifikasi'; ?></p>
                            </div>
                        </div>
                        
                        <div class="status-item <?php echo ($registration && ($registration['status'] == 'interview_scheduled' ||
                         $registration['status'] == 'interview_completed' ||
                          $registration['status'] == 'selected' ||
                           $registration['status'] == 'not_selected')) ? 'status-complete' : 'status-incomplete'; ?>">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <h4>Seleksi</h4>
                                <p>
                                    <?php 
                                    if (!$registration || $registration['status'] == 'pending' || $registration['status'] == 'verified') {
                                        echo 'Menunggu Jadwal';
                                    } elseif ($registration['status'] == 'interview_completed' ||
                                        $registration['status'] == 'selected' ||
                                        $registration['status'] == 'interview_completed' ||
                                        $registration['status'] == 'selected' ||
                                        $registration['status'] == 'not_selected') {
                                    echo 'Selesai';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$registration): ?>
                    <div class="registration-cta">
                        <p>Anda belum mendaftar sebagai calon pengurus OSIS. Silakan lengkapi formulir pendaftaran untuk memulai proses seleksi.</p>
                        <a href="registration_form.php" class="btn btn-primary">Isi Formulir Pendaftaran</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <!-- Upcoming Schedules -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Jadwal Terdekat</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($schedules) > 0): ?>
                            <div class="schedule-list">
                                <?php foreach (array_slice($schedules, 0, 2) as $schedule): ?>
                                <div class="schedule-item">
                                    <div class="schedule-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="schedule-info">
                                        <h4><?php echo $schedule['title']; ?></h4>
                                        <p><?php echo format_date($schedule['date']); ?>, <?php echo $schedule['time']; ?></p>
                                        <p><?php echo $schedule['location']; ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Tidak ada jadwal terdekat.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="schedule.php" class="btn btn-outline btn-block">Lihat Semua Jadwal</a>
                    </div>
                </div>
                
                <!-- Recent Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Notifikasi Terbaru</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($notifications) > 0): ?>
                            <div class="notification-list">
                                <?php foreach (array_slice($notifications, 0, 2) as $notification): ?>
                                <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                    <div class="notification-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="notification-info">
                                        <h4><?php echo $notification['title']; ?></h4>
                                        <p><?php echo $notification['message']; ?></p>
                                        <span class="notification-time"><?php echo format_date($notification['created_at'], true); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">Tidak ada notifikasi terbaru.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="notifications.php" class="btn btn-outline btn-block">Lihat Semua Notifikasi</a>
                    </div>
                </div>
            </div>
            
            <!-- Important Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Penting</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <p>Seluruh peserta diminta untuk mempersiapkan presentasi singkat tentang visi dan misi sebagai pengurus OSIS. Presentasi akan dilaksanakan pada tanggal 26 Mei 2024.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
