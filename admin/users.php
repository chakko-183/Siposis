<?php
require_once '../config.php';
check_login();
check_admin();

// Tambah / Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';

    if ($id) {
        // update tanpa ganti password
        if ($password === '') {
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $role, $id]);
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $role, $hash, $id]);
        }
    } else {
        // tambah user baru
        if ($password === '') {
            $error = "Password tidak boleh kosong!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $hash, $role]);
        }
    }

    header("Location: users.php");
    exit;
}

// Hapus user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: users.php");
    exit;
}

// Ambil semua user
$stmt = $pdo->query("SELECT * FROM users ORDER BY role ASC, nama_lengkap ASC");
$users = $stmt->fetchAll();

// Ambil user jika mode edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Admin</title>
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
            <h2 class="mb-4">Kelola Pengguna</h2>

            <!-- Form -->
            <div class="card mb-4">
                <div class="card-header"><?= $edit_data ? 'Edit User' : 'Tambah User Baru' ?></div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama" class="form-control" required value="<?= $edit_data['nama_lengkap'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email / Username</label>
                            <input type="email" name="email" id="email" class="form-control" required value="<?= $edit_data['email'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><?= $edit_data ? 'Password Baru (opsional)' : 'Password' ?></label>
                            <input type="password" name="password" id="password" class="form-control" <?= $edit_data ? '' : 'required' ?>>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Peran</label>
                            <select name="role" class="form-select" required>
                                <option value="admin" <?= ($edit_data['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="siswa" <?= ($edit_data['role'] ?? '') === 'siswa' ? 'selected' : '' ?>>Siswa</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?= $edit_data ? 'Update' : 'Simpan' ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="users.php" class="btn btn-secondary ms-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabel User -->
            <div class="card">
                <div class="card-header">Daftar User</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $u): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td>
                                        <a href="?edit=<?= $u['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Hapus user ini?')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada pengguna.</td></tr>
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
