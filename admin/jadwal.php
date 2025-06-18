<?php
require_once '../config.php';
check_login();
check_admin();

// Menangani aksi simpan / edit jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $tahapan = $_POST['tahapan'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $lokasi = $_POST['lokasi'];
    $deskripsi = $_POST['deskripsi'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        // Update jadwal
        $stmt = $pdo->prepare("UPDATE jadwal_seleksi SET tahapan=?, tanggal_mulai=?, tanggal_selesai=?, lokasi=?, deskripsi=?, is_active=? WHERE id=?");
        $stmt->execute([$tahapan, $tanggal_mulai, $tanggal_selesai, $lokasi, $deskripsi, $is_active, $id]);
    } else {
        // Tambah jadwal
        $stmt = $pdo->prepare("INSERT INTO jadwal_seleksi (tahapan, tanggal_mulai, tanggal_selesai, lokasi, deskripsi, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tahapan, $tanggal_mulai, $tanggal_selesai, $lokasi, $deskripsi, $is_active]);
    }
    header("Location: jadwal.php");
    exit;
}

// Hapus jadwal
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM jadwal_seleksi WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: jadwal.php");
    exit;
}

// Ambil semua jadwal
$stmt = $pdo->query("SELECT * FROM jadwal_seleksi ORDER BY tanggal_mulai ASC");
$jadwal = $stmt->fetchAll();

// Ambil data untuk edit jika ada
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM jadwal_seleksi WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Seleksi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Jadwal Seleksi</h2>
            </div>

            <!-- Form tambah/edit -->
            <div class="card mb-4">
                <div class="card-header">
                    <?= $edit_data ? 'Edit Jadwal' : 'Tambah Jadwal' ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="tahapan" class="form-label">Tahapan</label>
                            <input type="text" class="form-control" id="tahapan" name="tahapan" required value="<?= $edit_data['tahapan'] ?? '' ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="datetime-local" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required value="<?= isset($edit_data['tanggal_mulai']) ? date('Y-m-d\TH:i', strtotime($edit_data['tanggal_mulai'])) : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required value="<?= isset($edit_data['tanggal_selesai']) ? date('Y-m-d\TH:i', strtotime($edit_data['tanggal_selesai'])) : '' ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= $edit_data['lokasi'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= isset($edit_data['is_active']) && $edit_data['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Aktifkan Jadwal Ini</label>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
                        <?php if ($edit_data): ?>
                            <a href="jadwal.php" class="btn btn-secondary ms-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabel jadwal -->
            <div class="card">
                <div class="card-header">
                    Daftar Jadwal Seleksi
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tahapan</th>
                                <th>Waktu</th>
                                <th>Lokasi</th>
                                <th>Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwal as $i => $item): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($item['tahapan']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($item['tanggal_mulai'])) ?> - <?= date('d/m/Y H:i', strtotime($item['tanggal_selesai'])) ?></td>
                                    <td><?= htmlspecialchars($item['lokasi']) ?></td>
                                    <td><?= $item['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
                                    <td>
                                        <a href="jadwal.php?edit=<?= $item['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="jadwal.php?delete=<?= $item['id'] ?>" onclick="return confirm('Hapus jadwal ini?')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($jadwal)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada jadwal.</td>
                                </tr>
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
