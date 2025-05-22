<?php
// Initialize session
session_start();

// Include database connection and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
require_admin();

// Get statistics
// Total applicants
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations");
$stmt->execute();
$total_applicants = $stmt->get_result()->fetch_assoc()['total'];

// Pending verification
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE status = 'pending'");
$stmt->execute();
$pending_verification = $stmt->get_result()->fetch_assoc()['total'];

// Scheduled interviews
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE status = 'interview_scheduled'");
$stmt->execute();
$scheduled_interviews = $stmt->get_result()->fetch_assoc()['total'];

// Completed selections
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE status IN ('selected', 'not_selected')");
$stmt->execute();
$completed_selections = $stmt->get_result()->fetch_assoc()['total'];

// Get recent applicants
$stmt = $conn->prepare("
    SELECT r.*, u.name, u.email 
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute();
$recent_applicants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get upcoming interviews
$stmt = $conn->prepare("
    SELECT i.*, u.name, r.class
    FROM interview_schedules i
    JOIN users u ON i.user_id = u.id
    JOIN registrations r ON i.registration_id = r.id
    JOIN schedules s ON i.schedule_id = s.id
    WHERE s.date >= CURDATE()
    ORDER BY s.date ASC, i.time_slot ASC
    LIMIT 3
");
$stmt->execute();
$upcoming_interviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/admin-placeholder.png" alt="Admin">
                <h3>Admin OSIS</h3>
                <p>SMKN 2 Sampang</p>
            </div>
            
            <div class="sidebar-menu">
                <h3>Menu</h3>
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="applicants.php"><i class="fas fa-users"></i> Pendaftar</a></li>
                    <li><a href="schedules.php"><i class="fas fa-calendar-alt"></i> Jadwal</a></li>
                    <li><a href="interviews.php"><i class="fas fa-clipboard-list"></i> Wawancara</a></li>
                    <li><a href="results.php"><i class="fas fa-file-alt"></i> Hasil Seleksi</a></li>
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
                <h2 class="dashboard-title">Dashboard Admin</h2>
                <div class="dashboard-actions">
                    <a href="#" class="btn btn-icon"><i class="fas fa-bell"></i></a>
                    <div class="user-dropdown">
                        <span>Admin</span>
                        <img src="../assets/img/admin-placeholder.png" alt="Admin">
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_applicants; ?></h3>
                        <p>Total Pendaftar</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_verification; ?></h3>
                        <p>Menunggu Verifikasi</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $scheduled_interviews; ?></h3>
                        <p>Terjadwal Wawancara</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_selections; ?></h3>
                        <p>Seleksi Selesai</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Applicants -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pendaftar Terbaru</h3>
                    <a href="applicants.php" class="btn btn-outline">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_applicants) > 0): ?>
                                    <?php foreach ($recent_applicants as $applicant): ?>
                                    <tr>
                                        <td><?php echo $applicant['name']; ?></td>
                                        <td><?php echo $applicant['class']; ?></td>
                                        <td><?php echo format_date($applicant['created_at']); ?></td>
                                        <td>
                                            <span class="badge <?php echo get_status_badge_class($applicant['status']); ?>">
                                                <?php echo get_status_label($applicant['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="applicant_detail.php?id=<?php echo $applicant['id']; ?>" class="btn btn-icon btn-sm" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($applicant['status'] == 'pending'): ?>
                                                <a href="verify_applicant.php?id=<?php echo $applicant['id']; ?>" class="btn btn-icon btn-sm btn-success" title="Verifikasi">
                                                    <i class="fas fa-check-square"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($applicant['status'] == 'verified'): ?>
                                                <a href="schedule_interview.php?id=<?php echo $applicant['id']; ?>" class="btn btn-icon btn-sm btn-info" title="Jadwalkan Wawancara">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="reject_applicant.php?id=<?php echo $applicant['id']; ?>" class="btn btn-icon btn-sm btn-danger" title="Tolak">
                                                    <i class="fas fa-times-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada pendaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Interviews -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Wawancara Mendatang</h3>
                    <a href="interviews.php" class="btn btn-outline">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (count($upcoming_interviews) > 0): ?>
                        <div class="interview-list">
                            <?php foreach ($upcoming_interviews as $interview): ?>
                            <div class="interview-item">
                                <div class="interview-avatar">
                                    <img src="../assets/img/user-placeholder.png" alt="<?php echo $interview['name']; ?>">
                                </div>
                                <div class="interview-info">
                                    <h4><?php echo $interview['name']; ?></h4>
                                    <p><?php echo $interview['class']; ?></p>
                                </div>
                                <div class="interview-schedule">
                                    <p class="interview-date"><?php echo format_date($interview['date']); ?></p>
                                    <p class="interview-time"><?php echo $interview['time_slot']; ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada jadwal wawancara.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
