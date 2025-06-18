<?php
session_start();
// Simulasi data siswa yang login
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'nama' => 'sarep bin joso',
        'kelas' => '11 IPA 2',
        'nisn' => '1234567890'
    ];
}

$user = $_SESSION['user'];

// Simulasi data status pendaftaran
$registration_status = [
    'status' => 'Lolos Seleksi Administrasi',
    'stage' => 'interview',
    'last_update' => '2025-05-28 10:30:00',
    'next_step' => 'Wawancara',
    'next_date' => '2025-06-02',
    'applied_position' => 'Ketua OSIS'
];

// Timeline proses seleksi
$timeline = [
    [
        'stage' => 'Pendaftaran',
        'date' => '2025-05-15',
        'status' => 'completed',
        'description' => 'Formulir pendaftaran telah disubmit'
    ],
    [
        'stage' => 'Upload Dokumen',
        'date' => '2025-05-20',
        'status' => 'completed',
        'description' => 'Semua dokumen telah diupload'
    ],
    [
        'stage' => 'Seleksi Administrasi',
        'date' => '2025-05-25',
        'status' => 'completed',
        'description' => 'Lolos verifikasi berkas'
    ],
    [
        'stage' => 'Tes Tertulis',
        'date' => '2025-05-30',
        'status' => 'current',
        'description' => 'Tes pengetahuan umum dan kepemimpinan'
    ],
    [
        'stage' => 'Wawancara',
        'date' => '2025-06-02',
        'status' => 'upcoming',
        'description' => 'Wawancara dengan tim penilai'
    ],
    [
        'stage' => 'Pengumuman',
        'date' => '2025-06-05',
        'status' => 'upcoming',
        'description' => 'Pengumuman hasil akhir'
    ]
];

// Simulasi hasil tes (jika ada)
$test_results = [
    [
        'test_name' => 'Tes Pengetahuan Umum',
        'score' => 85,
        'max_score' => 100,
        'status' => 'Lulus',
        'date' => '2025-05-25'
    ],
    [
        'test_name' => 'Tes Kepemimpinan',
        'score' => 78,
        'max_score' => 100,
        'status' => 'Lulus',
        'date' => '2025-05-25'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOSIS - Status & Hasil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .user-info {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .user-title {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #fff;
        }

        .nav-icon {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .main-content {
            margin-left: 280px;
            padding: 30px;
        }

        .status-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .current-status {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }

        .status-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .status-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .detail-item {
            text-align: left;
        }

        .detail-label {
            opacity: 0.8;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .timeline-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .section-icon {
            margin-right: 10px;
            font-size: 1.3rem;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e1e5e9;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-left: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -35px;
            top: 25px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ccc;
        }

        .timeline-item.completed::before {
            background: #28a745;
        }

        .timeline-item.current::before {
            background: #007bff;
            animation: pulse 2s infinite;
        }

        .timeline-item.upcoming::before {
            background: #6c757d;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .timeline-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 10px;
        }

        .timeline-stage {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .timeline-date {
            color: #666;
            font-size: 0.9rem;
        }

        .timeline-description {
            color: #666;
        }

        .results-section {
            margin-bottom: 40px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .result-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #28a745;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .result-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .result-status {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .result-score {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 10px 0;
        }

        .score-bar {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }

        .score-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 1s ease-in-out;
        }

        .next-steps {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .next-steps-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .next-steps-content {
            color: #856404;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .status-details {
                grid-template-columns: 1fr;
            }
            
            .results-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="user-info">
            <div class="user-avatar">👨‍🎓</div>
            <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
            <div class="user-title">Dashboard Siswa</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="pendaftaran.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Pendaftaran
                </a>
            </li>
            <li class="nav-item">
                <a href="upload.php" class="nav-link">
                    <span class="nav-icon">📤</span>
                    Upload Dokumen
                </a>
            </li>
            <li class="nav-item">
                <a href="status.php" class="nav-link active">
                    <span class="nav-icon">ℹ️</span>
                    Status & Hasil
                </a>
            </li>
            <li class="nav-item">
                <a href="profil.php" class="nav-link">
                    <span class="nav-icon">👤</span>
                    Profil
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="status-container">
            <h2 class="page-title">Status Pendaftaran & Hasil Seleksi</h2>
            
            <div class="current-status">
                <div class="status-badge"><?= $registration_status['status'] ?></div>
                <h3>Status Terkini Pendaftaran Anda</h3>
                <div class="status-details">
                    <div class="detail-item">
                        <div class="detail-label">Posisi yang Dilamar</div>
                        <div class="detail-value"><?= $registration_status['applied_position'] ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tahap Saat Ini</div>
                        <div class="detail-value"><?= $registration_status['next_step'] ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Jadwal Selanjutnya</div>
                        <div class="detail-value"><?= date('d F Y', strtotime($registration_status['next_date'])) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Terakhir Diupdate</div>
                        <div class="detail-value"><?= date('d F Y H:i', strtotime($registration_status['last_update'])) ?></div>
                    </div>
                </div>
            </div>

            <div class="timeline-section">
                <h3 class="section-title">
                    <span class="section-icon">📅</span>
                    Timeline Proses Seleksi
                </h3>
                <div class="timeline">
                    <?php foreach ($timeline as $item): ?>
                        <div class="timeline-item <?= $item['status'] ?>">
                            <div class="timeline-header">
                                <div class="timeline-stage"><?= $item['stage'] ?></div>
                                <div class="timeline-date"><?= date('d F Y', strtotime($item['date'])) ?></div>
                            </div>
                            <div class="timeline-description"><?= $item['description'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($test_results)): ?>
                <div class="results-section">
                    <h3 class="section-title">
                        <span class="section-icon">📊</span>
                        Hasil Tes
                    </h3>
                    <div class="results-grid">
                        <?php foreach ($test_results as $result): ?>
                            <div class="result-card">
                                <div class="result-header">
                                    <div class="result-name"><?= $result['test_name'] ?></div>
                                    <div class="result-status"><?= $result['status'] ?></div>
                                </div>
                                <div class="result-score"><?= $result['score'] ?>/<?= $result['max_score'] ?></div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?= ($result['score']/$result['max_score'])*100 ?>%"></div>
                                </div>
                                <div style="text-align: center; color: #666; font-size: 0.9rem;">
                                    Tanggal: <?= date('d F Y', strtotime($result['date'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="next-steps">
                <div class="next-steps-title">
                    <span style="margin-right: 10px;">⚠️</span>
                    Langkah Selanjutnya
                </div>
                <div class="next-steps-content">
                    <strong>Wawancara</strong> akan dilaksanakan pada <strong><?= date('d F Y', strtotime($registration_status['next_date'])) ?></strong>. 
                    Silakan mempersiapkan diri dengan:
                    <br><br>
                    • Mempelajari visi misi sekolah dan OSIS<br>
                    • Mempersiapkan jawaban tentang motivasi dan rencana program kerja<br>
                    • Berpakaian rapi dan sopan<br>
                    • Datang 15 menit sebelum jadwal wawancara<br><br>
                    <strong>Lokasi:</strong> Ruang BK Sekolah<br>
                    <strong>Waktu:</strong> 08.00 - 12.00 WIB
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animation for score bars
        window.addEventListener('load', function() {
            const scoreFills = document.querySelectorAll('.score-fill');
            scoreFills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = width;
                }, 500);
            });
        });
    </script>
</body>
</html>