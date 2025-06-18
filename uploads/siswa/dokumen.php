<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'siposis_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Simulasi data siswa yang login
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'nama' => 'sarep bin joso',
        'kelas' => '11 IPA 2',
        'nisn' => '1234567890'
    ];
}

$user = $_SESSION['user'];
$success_message = '';
$error_message = '';

// Create upload directory if not exists
$upload_dir = 'osis-system/siswa/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Get existing uploaded documents from database
$stmt = $pdo->prepare("SELECT * FROM dokumen_siswa WHERE siswa_id = ?");
$stmt->execute([$user['id']]);
$existing_docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to associative array for easier access
$uploaded_docs = [
    'foto' => false,
    'cv' => false,
    'surat_motivasi' => false,
    'transkrip' => false
];

foreach ($existing_docs as $doc) {
    $uploaded_docs[$doc['jenis_dokumen']] = $doc['nama_file'];
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES)) {
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $max_sizes = [
        'foto' => 2 * 1024 * 1024, // 2MB
        'cv' => 5 * 1024 * 1024, // 5MB
        'surat_motivasi' => 3 * 1024 * 1024, // 3MB
        'transkrip' => 3 * 1024 * 1024 // 3MB
    ];
    
    $upload_success = true;
    $uploaded_files = [];
    
    foreach ($_FILES as $key => $file) {
        if ($file['error'] == 0 && $file['size'] > 0) {
            // Check file size
            if ($file['size'] > $max_sizes[$key]) {
                $error_message = "File " . $key . " terlalu besar!";
                $upload_success = false;
                break;
            }
            
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, $allowed_types)) {
                $new_filename = $user['nisn'] . '_' . $key . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $uploaded_files[$key] = $new_filename;
                } else {
                    $error_message = "Gagal mengupload file " . $key;
                    $upload_success = false;
                    break;
                }
            } else {
                $error_message = "Format file " . $key . " tidak didukung!";
                $upload_success = false;
                break;
            }
        }
    }
    
    // Save to database if upload successful
    if ($upload_success && !empty($uploaded_files)) {
        try {
            $pdo->beginTransaction();
            
            foreach ($uploaded_files as $jenis => $filename) {
                // Check if document already exists
                $stmt = $pdo->prepare("SELECT id FROM dokumen_siswa WHERE siswa_id = ? AND jenis_dokumen = ?");
                $stmt->execute([$user['id'], $jenis]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing record
                    $stmt = $pdo->prepare("UPDATE dokumen_siswa SET nama_file = ?, tanggal_upload = NOW() WHERE siswa_id = ? AND jenis_dokumen = ?");
                    $stmt->execute([$filename, $user['id'], $jenis]);
                } else {
                    // Insert new record
                    $stmt = $pdo->prepare("INSERT INTO dokumen_siswa (siswa_id, jenis_dokumen, nama_file, tanggal_upload) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$user['id'], $jenis, $filename]);
                }
                
                $uploaded_docs[$jenis] = $filename;
            }
            
            $pdo->commit();
            $success_message = "File berhasil diupload dan disimpan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Gagal menyimpan data ke database: " . $e->getMessage();
        }
    }
}

// Handle submit all documents
if (isset($_POST['submit_all'])) {
    $all_uploaded = true;
    foreach ($uploaded_docs as $doc => $status) {
        if (!$status) {
            $all_uploaded = false;
            break;
        }
    }
    
    if ($all_uploaded) {
        try {
            // Update submission status
            $stmt = $pdo->prepare("INSERT INTO submissions (siswa_id, tanggal_submit, status) VALUES (?, NOW(), 'submitted') ON DUPLICATE KEY UPDATE tanggal_submit = NOW(), status = 'submitted'");
            $stmt->execute([$user['id']]);
            
            $success_message = "Semua dokumen berhasil disubmit! Terima kasih.";
        } catch (Exception $e) {
            $error_message = "Gagal melakukan submit: " . $e->getMessage();
        }
    } else {
        $error_message = "Harap upload semua dokumen sebelum melakukan submit!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOSIS - Upload Dokumen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
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

        .upload-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .upload-card {
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .upload-card.uploaded {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #667eea;
        }

        .upload-card.uploaded .upload-icon {
            color: #28a745;
        }

        .upload-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .upload-description {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .upload-btn {
            background: blue;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
        }

        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 20px auto;
            display: block;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .uploaded-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .requirements {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }

        .requirements-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .requirements-list {
            list-style: none;
        }

        .requirements-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .requirements-list li:last-child {
            border-bottom: none;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .progress-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .progress-bar {
            background: rgba(255,255,255,0.2);
            height: 10px;
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-fill {
            background: white;
            height: 100%;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .filename-display {
            font-size: 0.85rem;
            color: #28a745;
            margin-top: 10px;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .upload-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="col-md-3 col-lg-2 px-0">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <?php
        $uploaded_count = 0;
        foreach ($uploaded_docs as $status) {
            if ($status) $uploaded_count++;
        }
        $total_docs = count($uploaded_docs);
        $progress = ($uploaded_count / $total_docs) * 100;
        ?>
        
        <div class="progress-section">
            <h3>Progress Upload Dokumen</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
            </div>
            <p><?= $uploaded_count ?> dari <?= $total_docs ?> dokumen telah diupload (<?= round($progress) ?>%)</p>
        </div>

        <div class="upload-container">
            <h2 class="page-title">Upload Dokumen Pendaftaran</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?= $error_message ?></div>
            <?php endif; ?>

            <div class="upload-grid">
                <div class="upload-card <?= $uploaded_docs['foto'] ? 'uploaded' : '' ?>">
                    <?php if ($uploaded_docs['foto']): ?>
                        <div class="uploaded-badge">✓ Uploaded</div>
                    <?php endif; ?>
                    <div class="upload-icon">📷</div>
                    <h3 class="upload-title">Pas Foto</h3>
                    <p class="upload-description">Upload pas foto terbaru dengan latar belakang merah/biru. Format: JPG, PNG. Maks 2MB.</p>
                    <?php if ($uploaded_docs['foto']): ?>
                        <div class="filename-display">File: <?= $uploaded_docs['foto'] ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="file" name="foto" class="file-input" id="foto-input" accept="image/*">
                        <button type="button" class="upload-btn" onclick="document.getElementById('foto-input').click()">
                            <?= $uploaded_docs['foto'] ? 'Ganti File' : 'Pilih File' ?>
                        </button>
                    </form>
                </div>

                <div class="upload-card <?= $uploaded_docs['cv'] ? 'uploaded' : '' ?>">
                    <?php if ($uploaded_docs['cv']): ?>
                        <div class="uploaded-badge">✓ Uploaded</div>
                    <?php endif; ?>
                    <div class="upload-icon">📄</div>
                    <h3 class="upload-title">Curriculum Vitae</h3>
                    <p class="upload-description">Upload CV yang berisi data diri, pengalaman, dan prestasi. Format: PDF, DOC, DOCX. Maks 5MB.</p>
                    <?php if ($uploaded_docs['cv']): ?>
                        <div class="filename-display">File: <?= $uploaded_docs['cv'] ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="file" name="cv" class="file-input" id="cv-input" accept=".pdf,.doc,.docx">
                        <button type="button" class="upload-btn" onclick="document.getElementById('cv-input').click()">
                            <?= $uploaded_docs['cv'] ? 'Ganti File' : 'Pilih File' ?>
                        </button>
                    </form>
                </div>

                <div class="upload-card <?= $uploaded_docs['surat_motivasi'] ? 'uploaded' : '' ?>">
                    <?php if ($uploaded_docs['surat_motivasi']): ?>
                        <div class="uploaded-badge">✓ Uploaded</div>
                    <?php endif; ?>
                    <div class="upload-icon">✍️</div>
                    <h3 class="upload-title">Surat Motivasi</h3>
                    <p class="upload-description">Surat yang menjelaskan motivasi bergabung dengan OSIS. Format: PDF, DOC, DOCX. Maks 3MB.</p>
                    <?php if ($uploaded_docs['surat_motivasi']): ?>
                        <div class="filename-display">File: <?= $uploaded_docs['surat_motivasi'] ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="file" name="surat_motivasi" class="file-input" id="motivasi-input" accept=".pdf,.doc,.docx">
                        <button type="button" class="upload-btn" onclick="document.getElementById('motivasi-input').click()">
                            <?= $uploaded_docs['surat_motivasi'] ? 'Ganti File' : 'Pilih File' ?>
                        </button>
                    </form>
                </div>

                <div class="upload-card <?= $uploaded_docs['transkrip'] ? 'uploaded' : '' ?>">
                    <?php if ($uploaded_docs['transkrip']): ?>
                        <div class="uploaded-badge">✓ Uploaded</div>
                    <?php endif; ?>
                    <div class="upload-icon">📊</div>
                    <h3 class="upload-title">Transkrip Nilai</h3>
                    <p class="upload-description">Transkrip nilai semester terakhir. Format: PDF, JPG, PNG. Maks 3MB.</p>
                    <?php if ($uploaded_docs['transkrip']): ?>
                        <div class="filename-display">File: <?= $uploaded_docs['transkrip'] ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" style="display: inline;">
                        <input type="file" name="transkrip" class="file-input" id="transkrip-input" accept=".pdf,image/*">
                        <button type="button" class="upload-btn" onclick="document.getElementById('transkrip-input').click()">
                            <?= $uploaded_docs['transkrip'] ? 'Ganti File' : 'Pilih File' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Submit Button -->
            <form method="POST">
                <button type="submit" name="submit_all" class="submit-btn" 
                        <?= ($uploaded_count < $total_docs) ? 'disabled title="Upload semua dokumen terlebih dahulu"' : '' ?>>
                    <?= ($uploaded_count < $total_docs) ? 'Upload Semua Dokumen untuk Submit' : 'Submit Semua Dokumen' ?>
                </button>
            </form>

            <div class="requirements">
                <h3 class="requirements-title">Persyaratan Upload Dokumen</h3>
                <ul class="requirements-list">
                    <li>✓ Semua dokumen harus dalam format yang jelas dan dapat dibaca</li>
                    <li>✓ Ukuran file maksimal sesuai dengan ketentuan masing-masing dokumen</li>
                    <li>✓ Pastikan nama file sesuai dengan jenis dokumen</li>
                    <li>✓ Dokumen yang diupload harus asli dan tidak boleh hasil edit/manipulasi</li>
                    <li>✓ Jika terjadi kesalahan upload, silakan hubungi panitia</li>
                    <li>✓ Klik tombol "Submit Semua Dokumen" setelah semua file berhasil diupload</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Auto submit form when file is selected
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.files.length > 0) {
                    // Show loading state
                    const btn = this.parentNode.querySelector('.upload-btn');
                    const originalText = btn.textContent;
                    btn.textContent = 'Mengupload...';
                    btn.disabled = true;
                    
                    // Submit form
                    this.closest('form').submit();
                }
            });
        });
    </script>
</body>
</html>