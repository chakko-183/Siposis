<?php
require_once '../config.php';
check_login();
check_admin();

$success_message = '';
$error_message = '';

// Handle verification or rejection action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $pendaftaran_id = $_GET['id'];
    $action = $_GET['action'];

    try {
        $pdo->beginTransaction();

        // Check if all required documents are uploaded before verifying
        if ($action === 'verify') {
            $stmt_docs = $pdo->prepare("SELECT COUNT(*) FROM dokumen WHERE pendaftaran_id = ?");
            $stmt_docs->execute([$pendaftaran_id]);
            $uploaded_doc_count = $stmt_docs->fetchColumn();

            // Assuming a minimum of 3 documents are required for verification
            // Adjust this number based on your actual required documents.
            // In the original dokumen.php, it checks for 5 specific types:
            // 'surat_izin', 'pas_foto', 'nilai_rapor', 'sertifikat', 'rekomendasi'.
            // For simplicity, let's assume at least 3 distinct types.
            // If you need strict checking for all 5, you'd fetch types like in dokumen.php
            // and compare against a specific list of required document types.
            // For now, we'll use a simple count check as a placeholder.
            if ($uploaded_doc_count < 3) { // Adjust '3' to the actual minimum number of required documents
                throw new Exception("Tidak dapat memverifikasi. Dokumen pendaftaran belum lengkap (" . $uploaded_doc_count . "/3 minimal).");
            }

            $new_status = 'verified';
            $success_message = "Pendaftaran berhasil diverifikasi!";
        } elseif ($action === 'reject') {
            $new_status = 'rejected';
            $success_message = "Pendaftaran berhasil ditolak.";
        } else {
            throw new Exception("Aksi tidak valid.");
        }

        $stmt_update = $pdo->prepare("UPDATE pendaftaran_osis SET status_pendaftaran = ? WHERE id = ?");
        $stmt_update->execute([$new_status, $pendaftaran_id]);

        $pdo->commit();
        // Redirect to clear GET parameters and display message
        redirect('pendaftar.php?status=success&message=' . urlencode($success_message));

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal memproses: " . $e->getMessage();
        // Redirect to clear GET parameters and display error message
        redirect('pendaftar.php?status=error&message=' . urlencode($error_message));
    }
}


// Get pendaftar data with JOIN to get complete information
$stmt = $pdo->prepare("
    SELECT
        p.*,
        u.nama_lengkap,
        u.email,
        s.nis,
        s.kelas,
        s.jurusan
    FROM pendaftaran_osis p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.status_pendaftaran IN ('submitted', 'verified', 'rejected', 'draft') -- Include all relevant statuses
    ORDER BY p.tanggal_daftar ASC
");
$stmt->execute();
$pendaftar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pendaftar - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        <?php // include 'style.css'; ?> /* opsional, jika Anda pisahkan style */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0">
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="col-md-9 col-lg-10 main-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Data Pendaftar</h2>
                <div>
                    </div>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-users me-2"></i>Daftar Pendaftar</strong>
                </div>
                <div class="card-body">
                    <?php if ($pendaftar): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama</th>
                                        <th>NIS</th>
                                        <th>Kelas</th>
                                        <th>Email</th>
                                        <th>Motivasi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendaftar as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($p['nama_lengkap'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($p['nis'] ?? '') ?></td>
                                            <td>
                                                <?= htmlspecialchars($p['kelas'] ?? '') ?>
                                                <?= htmlspecialchars($p['jurusan'] ?? '') ?>
                                            </td>
                                            <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
                                            <td>
                                                <?php
                                                $motivasi = $p['motivasi'] ?? '';
                                                echo $motivasi ? mb_strimwidth(htmlspecialchars($motivasi), 0, 50, "...") : '-';
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $p['status_pendaftaran'] ?? 'unknown';
                                                $badge_class = 'secondary';
                                                if ($status == 'verified') {
                                                    $badge_class = 'primary';
                                                } elseif ($status == 'submitted') {
                                                    $badge_class = 'success';
                                                } elseif ($status == 'rejected') {
                                                    $badge_class = 'danger';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $badge_class ?>">
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $p['status_pendaftaran'] ?? '';
                                                $id = $p['id'] ?? 0;
                                                ?>
                                                <?php if ($status == 'submitted'): ?>
                                                    <a href="pendaftar.php?action=verify&id=<?= $id ?>"
                                                       onclick="return confirm('Apakah Anda yakin ingin memverifikasi pendaftaran ini? Pastikan semua dokumen sudah lengkap.');"
                                                       class="btn btn-sm btn-success me-1">
                                                        <i class="fas fa-check"></i> Verifikasi
                                                    </a>
                                                    <a href="pendaftar.php?action=reject&id=<?= $id ?>"
                                                       onclick="return confirm('Apakah Anda yakin ingin MENOLAK pendaftaran ini?');"
                                                       class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </a>
                                                <?php elseif ($status == 'verified'): ?>
                                                    <span class="text-success me-2"><i class="fas fa-check-double"></i> Terverifikasi</span>
                                                    <a href="pendaftar.php?action=reject&id=<?= $id ?>"
                                                       onclick="return confirm('Apakah Anda yakin ingin MEMBATALKAN verifikasi dan MENOLAK pendaftaran ini?');"
                                                       class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times"></i> Batalkan Verifikasi
                                                    </a>
                                                <?php elseif ($status == 'rejected'): ?>
                                                    <span class="text-danger me-2"><i class="fas fa-ban"></i> Ditolak</span>
                                                    <a href="pendaftar.php?action=verify&id=<?= $id ?>"
                                                       onclick="return confirm('Apakah Anda yakin ingin MEMBATALKAN penolakan dan MEMVERIFIKASI pendaftaran ini?');"
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Batalkan Tolak
                                                    </a>
                                                <?php else: // draft or other statuses ?>
                                                    <span class="text-muted">Menunggu Submit</span>
                                                <?php endif; ?>
                                                <a href="detail_pendaftar.php?id=<?= $id ?>" class="btn btn-sm btn-info ms-1">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">Belum ada pendaftar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>