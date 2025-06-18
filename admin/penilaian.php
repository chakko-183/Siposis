<?php
require_once '../config.php';
check_login();
check_admin();

// Ambil data peserta yang belum dinilai
$stmt = $pdo->query("
    SELECT p.id AS pendaftaran_id, u.nama_lengkap, s.nis, s.kelas, s.jurusan,
           p.motivasi, p.pengalaman_organisasi
    FROM pendaftaran_osis p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.status_pendaftaran IN ('submitted', 'verified')
      AND p.id NOT IN (SELECT pendaftaran_id FROM penilaian)
    ORDER BY p.tanggal_daftar ASC
");
$calon = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua data penilaian
$stmt = $pdo->query("
    SELECT pn.*, u.nama_lengkap, s.nis, s.kelas, s.jurusan
    FROM penilaian pn
    JOIN pendaftaran_osis p ON pn.pendaftaran_id = p.id
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    ORDER BY pn.tanggal_penilaian DESC
");
$hasil = $stmt->fetchAll();

// Ambil dokumen rapor untuk semua pendaftaran
$rapor_docs = [];
if (!empty($calon)) {
    $ids = implode(',', array_column($calon, 'pendaftaran_id'));
    $stmt = $pdo->query("SELECT * FROM dokumen WHERE jenis_dokumen = 'nilai_rapor' AND pendaftaran_id IN ($ids)");
    foreach ($stmt->fetchAll() as $doc) {
        $rapor_docs[$doc['pendaftaran_id']] = $doc;
    }
}

// Simpan penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $nilai_akademik = $_POST['nilai_akademik'];
    $nilai_kepemimpinan = $_POST['nilai_kepemimpinan'];
    $nilai_komunikasi = $_POST['nilai_komunikasi'];
    $nilai_organisasi = $_POST['nilai_organisasi'];
    $nilai_wawancara = $_POST['nilai_wawancara'];
    $catatan = $_POST['catatan'];

    $nilai_akhir = round(
        $nilai_akademik * 0.20 +
        $nilai_kepemimpinan * 0.25 +
        $nilai_komunikasi * 0.20 +
        $nilai_organisasi * 0.15 +
        $nilai_wawancara * 0.20, 2
    );

    $status_kelulusan = $nilai_akhir >= 75 ? 'lulus' : 'tidak_lulus';
    $admin_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO penilaian 
        (pendaftaran_id, admin_id, nilai_akademik, nilai_kepemimpinan, nilai_komunikasi, nilai_organisasi, nilai_wawancara, nilai_akhir, catatan, status_kelulusan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $pendaftaran_id, $admin_id,
        $nilai_akademik, $nilai_kepemimpinan, $nilai_komunikasi, $nilai_organisasi, $nilai_wawancara,
        $nilai_akhir, $catatan, $status_kelulusan
    ]);

    header("Location: penilaian.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penilaian Calon OSIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0">
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="col-md-9 col-lg-10 main-content p-4">
            <h2 class="mb-4">Input Penilaian Peserta</h2>

            <?php if ($calon): ?>
                <?php foreach ($calon as $c): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <?= htmlspecialchars($c['nama_lengkap']) ?> (<?= $c['nis'] ?> - <?= $c['kelas'] ?> <?= $c['jurusan'] ?>)
                        </div>
                        <div class="card-body">
                            <p><strong>Motivasi:</strong><br><?= nl2br(htmlspecialchars($c['motivasi'])) ?></p>
                            <p><strong>Pengalaman Organisasi:</strong><br><?= nl2br(htmlspecialchars($c['pengalaman_organisasi'])) ?></p>

                            <?php if (isset($rapor_docs[$c['pendaftaran_id']])): ?>
                                <p><strong>Nilai Rapor:</strong>
                                    <a href="../<?= htmlspecialchars($rapor_docs[$c['pendaftaran_id']]['path_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file-pdf"></i> Lihat Dokumen
                                    </a>
                                </p>
                            <?php else: ?>
                                <p class="text-muted"><i>Tidak ada dokumen nilai rapor yang diunggah.</i></p>
                            <?php endif; ?>

                            <form method="POST">
                                <input type="hidden" name="pendaftaran_id" value="<?= $c['pendaftaran_id'] ?>">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>Nilai Akademik</label>
                                        <input type="number" name="nilai_akademik" class="form-control" required min="0" max="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Nilai Kepemimpinan</label>
                                        <input type="number" name="nilai_kepemimpinan" class="form-control" required min="0" max="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Nilai Komunikasi</label>
                                        <input type="number" name="nilai_komunikasi" class="form-control" required min="0" max="100">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>Nilai Organisasi</label>
                                        <input type="number" name="nilai_organisasi" class="form-control" required min="0" max="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Nilai Wawancara</label>
                                        <input type="number" name="nilai_wawancara" class="form-control" required min="0" max="100">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Catatan Penilai (opsional)</label>
                                    <textarea name="catatan" rows="3" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-2"></i>Simpan Penilaian
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Semua peserta telah dinilai.</p>
            <?php endif; ?>

            <hr class="my-4">

            <h4>Riwayat Penilaian</h4>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Nilai Akhir</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['nama_lengkap']) ?> (<?= $h['nis'] ?>)</td>
                                <td><strong><?= $h['nilai_akhir'] ?></strong></td>
                                <td>
                                    <?= $h['status_kelulusan'] == 'lulus' ? '<span class="badge bg-success">Lulus</span>' : '<span class="badge bg-danger">Tidak Lulus</span>' ?>
                                </td>
                                <td><?= htmlspecialchars($h['catatan']) ?: '-' ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($h['tanggal_penilaian'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($hasil)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada penilaian.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
