<?php
require_once '../config.php';
check_login();
check_admin();

// Simpan atau update pengumuman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $judul = $_POST['judul'];
    $isi = $_POST['isi'];
    $tipe = $_POST['tipe'];
    $target = $_POST['target_audience'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];

    if ($id) {
        $stmt = $pdo->prepare("UPDATE pengumuman SET judul=?, isi=?, tipe=?, target_audience=?, is_published=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$judul, $isi, $tipe, $target, $is_published, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pengumuman (judul, isi, tipe, target_audience, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $isi, $tipe, $target, $is_published, $created_by]);
    }
    header("Location: pengumuman.php");
    exit;
}

// Hapus pengumuman
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: pengumuman.php");
    exit;
}

// Ambil semua pengumuman
$stmt = $pdo->query("SELECT * FROM pengumuman ORDER BY created_at DESC");
$pengumuman = $stmt->fetchAll();

// Ambil untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengumuman - Admin</title>
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
                <h2>Kelola Pengumuman</h2>
            </div>

            <!-- Form -->
            <div class="card mb-4">
                <div class="card-header"><?= $edit_data ? 'Edit Pengumuman' : 'Tambah Pengumuman' ?></div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" name="judul" id="judul" class="form-control" required value="<?= $edit_data['judul'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="isi" class="form-label">Isi</label>
                            <textarea name="isi" id="isi" rows="4" class="form-control" required><?= $edit_data['isi'] ?? '' ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <select name="tipe" id="tipe" class="form-select" required>
                                    <option value="info" <?= ($edit_data['tipe'] ?? '') == 'info' ? 'selected' : '' ?>>Info</option>
                                    <option value="jadwal" <?= ($edit_data['tipe'] ?? '') == 'jadwal' ? 'selected' : '' ?>>Jadwal</option>
                                    <option value="hasil" <?= ($edit_data['tipe'] ?? '') == 'hasil' ? 'selected' : '' ?>>Hasil</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="target_audience" class="form-label">Target</label>
                                <select name="target_audience" id="target_audience" class="form-select" required>
                                    <option value="all" <?= ($edit_data['target_audience'] ?? '') == 'all' ? 'selected' : '' ?>>Semua</option>
                                    <option value="siswa" <?= ($edit_data['target_audience'] ?? '') == 'siswa' ? 'selected' : '' ?>>Siswa</option>
                                    <option value="admin" <?= ($edit_data['target_audience'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" <?= isset($edit_data['is_published']) && $edit_data['is_published'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">Tampilkan Pengumuman</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
                        <?php if ($edit_data): ?>
                            <a href="pengumuman.php" class="btn btn-secondary ms-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="card">
                <div class="card-header">Daftar Pengumuman</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Tipe</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengumuman as $i => $p): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($p['judul']) ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($p['tipe']) ?></span></td>
                                    <td><?= ucfirst($p['target_audience']) ?></td>
                                    <td>
                                        <?= $p['is_published'] ? '<span class="badge bg-success">Dipublikasi</span>' : '<span class="badge bg-secondary">Disembunyikan</span>' ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                    <td>
                                        <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengumuman ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pengumuman)): ?>
                                <tr><td colspan="7" class="text-center text-muted">Belum ada pengumuman.</td></tr>
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
