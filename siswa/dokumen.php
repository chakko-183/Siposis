<?php
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$show_registration_modal = false; // Flag untuk menampilkan modal

// Ambil siswa_id dari user_id
$stmt = $pdo->prepare("SELECT s.id, s.nis, s.kelas, s.jurusan, u.nama_lengkap FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$siswa = $stmt->fetch();
if (!$siswa) {
    // Fallback, seharusnya tidak terjadi jika login berhasil
    die("Data siswa tidak ditemukan.");
}

$siswa_id = $siswa['id'];
$nama_siswa = $siswa['nama_lengkap'];
$nis_siswa = $siswa['nis'];
$kelas_siswa = $siswa['kelas'];
$jurusan_siswa = $siswa['jurusan'];

// Ambil pendaftaran_id dan status pendaftaran dari siswa_id
$stmt = $pdo->prepare("SELECT id, status_pendaftaran FROM pendaftaran_osis WHERE siswa_id = ?");
$stmt->execute([$siswa_id]);
$pendaftaran = $stmt->fetch();

// Cek apakah pendaftaran belum dibuat atau statusnya masih 'draft'
if (!$pendaftaran || $pendaftaran['status_pendaftaran'] === 'draft') {
    $show_registration_modal = true; // Set flag untuk menampilkan modal
    $pendaftaran_id = null; // Pastikan pendaftaran_id null jika modal aktif
    $uploaded_files = []; // Pastikan ini kosong agar tidak ada error di loop bawah
} else {
    $pendaftaran_id = $pendaftaran['id'];

    // Ambil dokumen yang sudah diupload (Hanya jika pendaftaran sudah lengkap)
    $stmt = $pdo->prepare("SELECT jenis_dokumen, nama_file FROM dokumen WHERE pendaftaran_id = ?");
    $stmt->execute([$pendaftaran_id]);
    $uploaded_files = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Array untuk melacak dokumen yang sudah diupload (default kosong jika modal aktif)
$uploaded_docs = [
    'surat_izin' => isset($uploaded_files['surat_izin']),
    'pas_foto' => isset($uploaded_files['pas_foto']),
    'nilai_rapor' => isset($uploaded_files['nilai_rapor']),
    'sertifikat' => isset($uploaded_files['sertifikat']),
    'rekomendasi' => isset($uploaded_files['rekomendasi'])
];

// Definisi folder upload
$upload_dir = '../uploads/dokumen/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Proses upload dokumen hanya jika pendaftaran sudah lengkap dan modal tidak ditampilkan
if (!$show_registration_modal && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jenis_dokumen'])) {
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    // Validasi jenis dokumen
    $valid_types = ['surat_izin', 'pas_foto', 'nilai_rapor', 'sertifikat', 'rekomendasi'];
    if (!in_array($jenis_dokumen, $valid_types)) {
        $error_message = "Jenis dokumen tidak valid.";
    } else if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === 0) {
        $file = $_FILES['dokumen'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validasi ekstensi file
        if (!in_array($file_extension, $allowed_extensions)) {
            $error_message = "Format file tidak didukung. Gunakan: " . implode(', ', $allowed_extensions);
        }
        // Validasi ukuran file
        else if ($file['size'] > $max_file_size) {
            $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
        else {
            // Generate nama file unik
            $new_filename = generate_unique_filename($file['name']);
            $file_path = $upload_dir . $new_filename;
            
            // Pindahkan file
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                try {
                    // Hapus file lama jika ada
                    if ($uploaded_docs[$jenis_dokumen]) {
                        $stmt = $pdo->prepare("SELECT nama_file FROM dokumen WHERE pendaftaran_id = ? AND jenis_dokumen = ?");
                        $stmt->execute([$pendaftaran_id, $jenis_dokumen]);
                        $old_file = $stmt->fetchColumn();
                        if ($old_file && file_exists($upload_dir . $old_file)) {
                            unlink($upload_dir . $old_file);
                        }
                        
                        // Update data existing
                        $stmt = $pdo->prepare("UPDATE dokumen SET nama_file = ?, ukuran_file = ?, path_file = ?, uploaded_at = CURRENT_TIMESTAMP WHERE pendaftaran_id = ? AND jenis_dokumen = ?");
                        $stmt->execute([$new_filename, $file['size'], $file_path, $pendaftaran_id, $jenis_dokumen]);
                    } else {
                        // Insert data baru
                        $stmt = $pdo->prepare("INSERT INTO dokumen (pendaftaran_id, jenis_dokumen, nama_file, ukuran_file, path_file) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$pendaftaran_id, $jenis_dokumen, $new_filename, $file['size'], $file_path]);
                    }
                    
                    $success_message = "Dokumen berhasil diupload!";
                    // Refresh status upload
                    $uploaded_docs[$jenis_dokumen] = true;
                    
                } catch (PDOException $e) {
                    $error_message = "Gagal menyimpan data ke database: " . $e->getMessage();
                    // Hapus file yang sudah diupload jika gagal menyimpan ke database
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            } else {
                $error_message = "Gagal mengupload file.";
            }
        }
    } else {
        $error_message = "Silakan pilih file untuk diupload.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOSIS - Upload Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .page-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }

        .page-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .progress-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .document-card {
            background: white;
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .document-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
            transform: translateY(-2px);
        }

        .document-card.uploaded {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .document-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .document-card.uploaded .document-icon {
            color: #28a745;
        }

        .document-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .document-description {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .uploaded-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .requirements-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .requirements-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
        }

        .requirements-title i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .requirements-list {
            list-style: none;
            padding: 0;
        }

        .requirements-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
            color: #666;
            display: flex;
            align-items: center;
        }

        .requirements-list li:last-child {
            border-bottom: none;
        }

        .requirements-list li i {
            margin-right: 0.75rem;
            color: #28a745;
            width: 16px;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .file-input {
            display: none;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .nav-btn {
            padding: 0.75rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .document-grid {
                grid-template-columns: 1fr;
            }
            
            .navigation-buttons {
                flex-direction: column;
                gap: 1rem;
            }
        }
        /* Tambahan untuk styling modal agar mirip gambar */
        .custom-modal-header {
            background: linear-gradient(135deg, #FF6B6B 0%, #FFA500 100%); /* Warna orange/merah */
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }
        .custom-modal-header i {
            font-size: 3.5rem; /* Ukuran ikon */
            margin-bottom: 0.5rem;
        }
        .custom-modal-content {
            border-radius: 15px;
            overflow: hidden; /* Agar border-radius diterapkan ke header */
        }
        .custom-modal-body {
            padding: 2rem;
            text-align: center;
        }
        .custom-modal-footer {
            border-top: none;
            padding: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        .btn-modal-action {
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            min-width: 150px;
        }
        .btn-modal-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%); /* Hijau */
            color: white;
            border: none;
        }
        .btn-modal-primary:hover {
            opacity: 0.9;
            color: white;
        }
        .btn-modal-secondary {
            background-color: #6c757d; /* Abu-abu */
            color: white;
            border: none;
        }
        .btn-modal-secondary:hover {
            opacity: 0.9;
            color: white;
        }
        /* Sembunyikan konten utama jika modal harus muncul */
        body.modal-open .main-content {
            filter: blur(5px);
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <?php include 'sidebar.php'; ?>
            </div>
            
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    <?php if ($show_registration_modal): ?>
                        <div style="display:none;">
                    <?php endif; ?>

                    <div class="page-header">
                        <h2><i class="fas fa-upload me-3"></i>Upload Dokumen Pendaftaran</h2>
                        <p>Selamat datang, <?= htmlspecialchars($nama_siswa) ?> - NIS: <?= htmlspecialchars($nis_siswa) ?> (<?= htmlspecialchars($kelas_siswa) ?> <?= htmlspecialchars($jurusan_siswa) ?>)</p>
                    </div>

                    <?php
                    // Pastikan variabel ini terdefinisi bahkan jika $pendaftaran_id null
                    $uploaded_count = (isset($uploaded_docs) && is_array($uploaded_docs)) ? array_sum($uploaded_docs) : 0;
                    $total_docs = (isset($uploaded_docs) && is_array($uploaded_docs)) ? count($uploaded_docs) : 5; // Asumsi ada 5 jenis dokumen
                    $progress = $total_docs > 0 ? ($uploaded_count / $total_docs) * 100 : 0;
                    ?>
                    
                    <div class="progress-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0">Progress Upload</h5>
                            <span class="badge bg-primary"><?= $uploaded_count ?>/<?= $total_docs ?> dokumen</span>
                        </div>
                        <div class="progress-bar-custom">
                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block"><?= round($progress) ?>% dokumen telah diupload</small>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <div class="document-grid">
                        <?php
                        $document_types = [
                            'surat_izin' => ['title' => 'Surat Izin Orang Tua', 'desc' => 'Surat persetujuan dari orang tua/wali untuk mengikuti kegiatan OSIS.', 'format' => 'PDF, DOC, DOCX', 'icon' => 'fas fa-file-signature', 'accept' => '.pdf,.doc,.docx'],
                            'pas_foto' => ['title' => 'Pas Foto', 'desc' => 'Foto formal dengan latar belakang merah atau biru, ukuran 3x4 cm.', 'format' => 'JPG, PNG', 'icon' => 'fas fa-camera', 'accept' => 'image/jpeg,image/jpg,image/png'],
                            'nilai_rapor' => ['title' => 'Nilai Rapor', 'desc' => 'Fotokopi rapor semester terakhir yang telah dilegalisir.', 'format' => 'PDF, JPG, PNG', 'icon' => 'fas fa-graduation-cap', 'accept' => '.pdf,image/jpeg,image/jpg,image/png'],
                            'sertifikat' => ['title' => 'Sertifikat Prestasi', 'desc' => 'Sertifikat prestasi akademik atau non-akademik (jika ada).', 'format' => 'PDF, JPG, PNG', 'icon' => 'fas fa-certificate', 'accept' => '.pdf,image/jpeg,image/jpg,image/png'],
                            'rekomendasi' => ['title' => 'Surat Rekomendasi', 'desc' => 'Surat rekomendasi dari guru atau kepala sekolah.', 'format' => 'PDF, DOC, DOCX', 'icon' => 'fas fa-envelope', 'accept' => '.pdf,.doc,.docx'],
                        ];
                        foreach ($document_types as $type => $data):
                        ?>
                        <div class="document-card <?= $uploaded_docs[$type] ? 'uploaded' : '' ?>">
                            <?php if ($uploaded_docs[$type]): ?>
                                <div class="uploaded-badge"><i class="fas fa-check"></i> Uploaded</div>
                            <?php endif; ?>
                            <div class="document-icon">
                                <i class="<?= $data['icon'] ?>"></i>
                            </div>
                            <h4 class="document-title"><?= $data['title'] ?></h4>
                            <p class="document-description">
                                <?= $data['desc'] ?><br>
                                <small><strong>Format:</strong> <?= $data['format'] ?> | <strong>Maks:</strong> 5MB</small>
                            </p>
                            <form method="POST" enctype="multipart/form-data" style="display: inline;">
                                <input type="hidden" name="jenis_dokumen" value="<?= $type ?>">
                                <input type="file" name="dokumen" class="file-input" id="<?= $type ?>" accept="<?= $data['accept'] ?>" onchange="this.form.submit()">
                                <button type="button" class="upload-btn" onclick="document.getElementById('<?= $type ?>').click()" <?= $show_registration_modal ? 'disabled' : '' ?>>
                                    <i class="fas fa-upload me-2"></i>
                                    <?= $uploaded_docs[$type] ? 'Ganti File' : 'Upload File' ?>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="requirements-section">
                        <h4 class="requirements-title">
                            <i class="fas fa-info-circle"></i>
                            Persyaratan Upload Dokumen
                        </h4>
                        <ul class="requirements-list">
                            <li><i class="fas fa-check"></i>Semua dokumen harus dalam format yang jelas dan dapat dibaca</li>
                            <li><i class="fas fa-check"></i>Ukuran file tidak boleh melebihi batas maksimal yang ditentukan</li>
                            <li><i class="fas fa-check"></i>Dokumen harus asli dan tidak hasil manipulasi/edit</li>
                            <li><i class="fas fa-check"></i>Pastikan file tidak rusak atau corrupt</li>
                            <li><i class="fas fa-check"></i>Jika mengalami kesulitan upload, silakan hubungi panitia</li>
                        </ul>
                    </div>

                    <div class="navigation-buttons">
                        <a href="dashboard.php" class="nav-btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                        <?php if ($uploaded_count === $total_docs): ?>
                            <a href="status.php" class="nav-btn btn-primary">
                                Lanjut ke Review <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        <?php else: ?>
                            <span class="nav-btn" style="background: #6c757d; color: white; opacity: 0.6;">
                                Upload semua dokumen terlebih dahulu
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($show_registration_modal): ?>
                        </div> <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registrationRequiredModal" tabindex="-1" aria-labelledby="registrationRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal-content">
                <div class="custom-modal-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h5 class="modal-title fs-4" id="registrationRequiredModalLabel">Pendaftaran Belum Lengkap!</h5>
                </div>
                <div class="modal-body custom-modal-body">
                    <p class="fs-6">Anda harus menyelesaikan formulir pendaftaran terlebih dahulu sebelum dapat mengunggah dokumen.</p>
                </div>
                <div class="custom-modal-footer">
                    <a href="pendaftaran.php" class="btn btn-modal-primary btn-modal-action">
                        <i class="fas fa-file-alt me-2"></i>Lengkapi Pendaftaran
                    </a>
                    <button type="button" class="btn btn-modal-secondary btn-modal-action" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loading state untuk form upload
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('.upload-btn');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
                btn.disabled = true;
            });
        });

        // Validasi file sebelum upload
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        alert('Ukuran file terlalu besar! Maksimal 5MB.');
                        this.value = '';
                        return;
                    }
                }
            });
        });

        // JavaScript untuk menampilkan modal jika flag $show_registration_modal true
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($show_registration_modal): ?>
                var myModal = new bootstrap.Modal(document.getElementById('registrationRequiredModal'), {
                    backdrop: 'static', // Mencegah modal tertutup saat klik di luar
                    keyboard: false      // Mencegah modal tertutup saat tekan Esc
                });
                myModal.show();
                // Tambahkan kelas ke body untuk efek blur pada konten belakang
                document.body.classList.add('modal-open');
            <?php endif; ?>

            // Hapus kelas modal-open dari body ketika modal ditutup
            var registrationModal = document.getElementById('registrationRequiredModal');
            registrationModal.addEventListener('hidden.bs.modal', function () {
                document.body.classList.remove('modal-open');
            });
        });
    </script>
</body>
</html>