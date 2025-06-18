<?php
require_once '../config.php';
check_login();

// Pastikan ID pengumuman ada
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'];

// Ambil data pengumuman
$stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ? AND tipe = 'hasil' AND is_published = 1");
$stmt->execute([$id]);
$pengumuman = $stmt->fetch();

if (!$pengumuman) {
    header("Location: ../index.php");
    exit;
}

// Jika user adalah siswa, cek apakah target audience cocok
$user_role = $_SESSION['role'];
if ($user_role == 'siswa') {
    if ($pengumuman['target_audience'] != 'all' && $pengumuman['target_audience'] != 'siswa') {
        header("Location: ../index.php");
        exit;
    }
}

// Ambil informasi sekolah/institusi (opsional, sesuaikan dengan database Anda)
$info_sekolah = [
    'nama' => 'SMA Negeri 1 Contoh',
    'alamat' => 'Jl. Pendidikan No. 123, Jakarta',
    'telepon' => '(021) 123-4567',
    'website' => 'www.sman1contoh.sch.id'
];

// Set header untuk PDF
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak - <?= htmlspecialchars($pengumuman['judul']) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .pengumuman-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .pengumuman-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        
        .content {
            text-align: justify;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        
        .content h3 {
            font-size: 15px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        
        .footer-info {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .tanggal-cetak {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        /* Styles for better PDF appearance */
        @page {
            margin: 2cm;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Tombol Print (tidak akan tercetak) -->
    <div class="print-btn no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Cetak PDF
        </button>
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Header Sekolah -->
    <div class="header">
        <h1><?= $info_sekolah['nama'] ?></h1>
        <p><?= $info_sekolah['alamat'] ?></p>
        <p>Telp: <?= $info_sekolah['telepon'] ?> | Website: <?= $info_sekolah['website'] ?></p>
    </div>

    <!-- Header Pengumuman -->
    <div class="pengumuman-header">
        <h2>Pengumuman Hasil</h2>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 150px; font-weight: bold;">Judul</td>
                <td>: <?= htmlspecialchars($pengumuman['judul']) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Publikasi</td>
                <td>: <?= date('d F Y', strtotime($pengumuman['created_at'])) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tipe</td>
                <td>: <?= ucfirst($pengumuman['tipe']) ?></td>
            </tr>
        </table>
    </div>

    <!-- Konten Pengumuman -->
    <div class="content">
        <?= nl2br(htmlspecialchars($pengumuman['isi'])) ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <?= $info_sekolah['nama'] ?><br>
            <?= date('d F Y') ?>
        </p>
        <br><br>
        <p>
            <strong>Kepala Sekolah</strong><br><br><br><br>
            <strong><u>_________________________</u></strong><br>
            <strong>NIP. ___________________</strong>
        </p>
    </div>

    <!-- Tanggal Cetak -->
    <div class="tanggal-cetak">
        Dicetak pada: <?= date('d F Y, H:i:s') ?> WIB
    </div>

    <!-- Footer Info -->
    <div class="footer-info">
        Dokumen ini dicetak secara otomatis dari sistem informasi sekolah.<br>
        Untuk verifikasi keabsahan dokumen, silakan hubungi pihak sekolah.
    </div>

    <script>
        // Auto print jika parameter print=1
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>
</html>