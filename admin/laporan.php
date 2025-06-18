<?php
require_once '../config.php';
check_login();
check_admin();

$filter = $_GET['filter'] ?? 'all';
$where = '';

if ($filter === 'lulus') {
    $where = "WHERE pn.status_kelulusan = 'lulus'";
} elseif ($filter === 'tidak_lulus') {
    $where = "WHERE pn.status_kelulusan = 'tidak_lulus'";
}

// Ambil data penilaian
$stmt = $pdo->query("
    SELECT pn.*, u.nama_lengkap, s.nis, s.kelas, s.jurusan
    FROM penilaian pn
    JOIN pendaftaran_osis p ON pn.pendaftaran_id = p.id
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    $where
    ORDER BY pn.nilai_akhir DESC
");
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Seleksi</title>
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
            <h2 class="mb-4">Laporan Hasil Seleksi</h2>

            <!-- Filter -->
            <div class="mb-3">
                <a href="laporan.php?filter=all" class="btn btn-outline-primary btn-sm <?= $filter === 'all' ? 'active' : '' ?>">Semua</a>
                <a href="laporan.php?filter=lulus" class="btn btn-outline-success btn-sm <?= $filter === 'lulus' ? 'active' : '' ?>">Lulus</a>
                <a href="laporan.php?filter=tidak_lulus" class="btn btn-outline-danger btn-sm <?= $filter === 'tidak_lulus' ? 'active' : '' ?>">Tidak Lulus</a>
                <a href="export_pdf.php?filter=<?= $filter ?>" target="_blank" class="btn btn-danger btn-sm float-end">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
            </div>

            <!-- Tabel -->
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th>Nilai Akhir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $i => $d): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($d['nama_lengkap']) ?></td>
                                    <td><?= $d['nis'] ?></td>
                                    <td><?= $d['kelas'] ?> <?= $d['jurusan'] ?></td>
                                    <td><strong><?= $d['nilai_akhir'] ?></strong></td>
                                    <td>
                                        <?= $d['status_kelulusan'] === 'lulus' 
                                            ? '<span class="badge bg-success">Lulus</span>' 
                                            : '<span class="badge bg-danger">Tidak Lulus</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data)): ?>
                                <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
