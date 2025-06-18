<?php
require_once '../config.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

$filter = $_GET['filter'] ?? 'all';
$where = '';

if ($filter === 'lulus') {
    $where = "WHERE pn.status_kelulusan = 'lulus'";
} elseif ($filter === 'tidak_lulus') {
    $where = "WHERE pn.status_kelulusan = 'tidak_lulus'";
}

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

// HTML template
ob_start();
?>
<h2 style="text-align:center;">Laporan Hasil Seleksi OSIS</h2>
<table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;">
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
            <td><?= $d['nilai_akhir'] ?></td>
            <td><?= ucfirst($d['status_kelulusan']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$html = ob_get_clean();

// PDF render
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan-seleksi-osis.pdf", ["Attachment" => false]);
exit;?>
