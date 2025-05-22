<?php
// Initialize session
session_start();

// Include database connection and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
require_login();

if (is_admin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if user has already registered
$stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existing_registration = $stmt->get_result()->fetch_assoc();

if ($existing_registration) {
    $_SESSION['error_message'] = 'Anda sudah mendaftar sebagai calon pengurus OSIS.';
    header('Location: dashboard.php');
    exit;
}

// Initialize variables
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$max_step = 4;
$form_submitted = false;
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission based on current step
    switch ($current_step) {
        case 1: // Personal Information
            // Validate and process personal information
            // ...
            // If valid, move to next step
            header('Location: registration_form.php?step=2');
            exit;
            break;
            
        case 2: // Academic Information
            // Validate and process academic information
            // ...
            // If valid, move to next step
            header('Location: registration_form.php?step=3');
            exit;
            break;
            
        case 3: // Motivation
            // Validate and process motivation
            // ...
            // If valid, move to next step
            header('Location: registration_form.php?step=4');
            exit;
            break;
            
        case 4: // Documents
            // Validate and process documents
            // ...
            // If valid, complete registration
            $form_submitted = true;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-container">
        <div class="container">
            <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
            
            <div class="form-header text-center">
                <h2>Formulir Pendaftaran OSIS</h2>
                <p>Lengkapi formulir berikut untuk mendaftar sebagai calon pengurus OSIS</p>
            </div>
            
            <?php if (!$form_submitted): ?>
                <!-- Progress Steps -->
                <div class="form-steps">
                    <div class="step <?php echo $current_step >= 1 ? 'active' : ''; ?> <?php echo $current_step > 1 ? 'completed' : ''; ?>">
                        <div class="step-number">1</div>
                        <div class="step-label">Data Diri</div>
                    </div>
                    <div class="step <?php echo $current_step >= 2 ? 'active' : ''; ?> <?php echo $current_step > 2 ? 'completed' : ''; ?>">
                        <div class="step-number">2</div>
                        <div class="step-label">Akademik</div>
                    </div>
                    <div class="step <?php echo $current_step >= 3 ? 'active' : ''; ?> <?php echo $current_step > 3 ? 'completed' : ''; ?>">
                        <div class="step-number">3</div>
                        <div class="step-label">Motivasi</div>
                    </div>
                    <div class="step <?php echo $current_step >= 4 ? 'active' : ''; ?>">
                        <div class="step-number">4</div>
                        <div class="step-label">Dokumen</div>
                    </div>
                </div>
                
                <div class="card">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?step=' . $current_step); ?>" method="POST" enctype="multipart/form-data">
                        <!-- Step 1: Personal Information -->
                        <?php if ($current_step == 1): ?>
                            <div class="card-header">
                                <h3 class="card-title">Data Diri</h3>
                                <p class="card-subtitle">Lengkapi informasi data diri Anda</p>
                            </div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="fullName" class="form-label">Nama Lengkap <span class="required">*</span></label>
                                        <input type="text" id="fullName" name="fullName" class="form-control" placeholder="Masukkan nama lengkap" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="nis" class="form-label">NIS <span class="required">*</span></label>
                                        <input type="text" id="nis" name="nis" class="form-control" placeholder="Masukkan NIS" required>
                                    </div>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="class" class="form-label">Kelas <span class="required">*</span></label>
                                        <select id="class" name="class" class="form-control" required>
                                            <option value="">Pilih kelas</option>
                                            <option value="X RPL 1">X RPL 1</option>
                                            <option value="X RPL 2">X RPL 2</option>
                                            <option value="X TKJ 1">X TKJ 1</option>
                                            <option value="X TKJ 2">X TKJ 2</option>
                                            <option value="X MM 1">X MM 1</option>
                                            <option value="X MM 2">X MM 2</option>
                                            <option value="XI RPL 1">XI RPL 1</option>
                                            <option value="XI RPL 2">XI RPL 2</option>
                                            <option value="XI TKJ 1">XI TKJ 1</option>
                                            <option value="XI TKJ 2">XI TKJ 2</option>
                                            <option value="XI MM 1">XI MM 1</option>
                                            <option value="XI MM 2">XI MM 2</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                                        <div class="radio-group">
                                            <div class="radio-item">
                                                <input type="radio" id="male" name="gender" value="male" required>
                                                <label for="male">Laki-laki</label>
                                            </div>
                                            <div class="radio-item">
                                                <input type="radio" id="female" name="gender" value="female">
                                                <label for="female">Perempuan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="birthPlace" class="form-label">Tempat Lahir <span class="required">*</span></label>
                                        <input type="text" id="birthPlace" name="birthPlace" class="form-control" placeholder="Masukkan tempat lahir" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="birthDate" class="form-label">Tanggal Lahir <span class="required">*</span></label>
                                        <input type="date" id="birthDate" name="birthDate" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address" class="form-label">Alamat <span class="required">*</span></label>
                                    <textarea id="address" name="address" class="form-control" placeholder="Masukkan alamat lengkap" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">Nomor Telepon <span class="required">*</span></label>
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Masukkan nomor telepon" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-navigation">
                                    <div></div> <!-- Empty div for spacing -->
                                    <button type="submit" class="btn btn-primary">Selanjutnya</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 2: Academic Information -->
                        <?php if ($current_step == 2): ?>
                            <div class="card-header">
                                <h3 class="card-title">Informasi Akademik</h3>
                                <p class="card-subtitle">Lengkapi informasi akademik dan pengalaman organisasi</p>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="lastSemesterGrade" class="form-label">Nilai Rata-rata Semester Terakhir <span class="required">*</span></label>
                                    <input type="text" id="lastSemesterGrade" name="lastSemesterGrade" class="form-control" placeholder="Contoh: 85.5" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="achievements" class="form-label">Prestasi Akademik/Non-Akademik</label>
                                    <textarea id="achievements" name="achievements" class="form-control" placeholder="Tuliskan prestasi yang pernah diraih (jika ada)"></textarea>
                                </div>
                                
                                <hr class="form-divider">
                                
                                <div class="form-check">
                                    <input type="checkbox" id="hasOrganizationExperience" name="hasOrganizationExperience" class="form-check-input">
                                    <label for="hasOrganizationExperience" class="form-check-label">Pernah mengikuti organisasi sebelumnya</label>
                                </div>
                                
                                <div id="organizationFields" class="organization-fields">
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="organizationName" class="form-label">Nama Organisasi</label>
                                            <input type="text" id="organizationName" name="organizationName" class="form-control" placeholder="Contoh: Pramuka, PMR, dll">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="organizationPosition" class="form-label">Jabatan</label>
                                            <input type="text" id="organizationPosition" name="organizationPosition" class="form-control" placeholder="Contoh: Ketua, Sekretaris, Anggota">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="organizationYear" class="form-label">Tahun</label>
                                        <input type="text" id="organizationYear" name="organizationYear" class="form-control" placeholder="Contoh: 2022-2023">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="organizationDescription" class="form-label">Deskripsi Kegiatan</label>
                                        <textarea id="organizationDescription" name="organizationDescription" class="form-control" placeholder="Jelaskan secara singkat kegiatan yang dilakukan dalam organisasi tersebut"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-navigation">
                                    <a href="registration_form.php?step=1" class="btn btn-outline">Sebelumnya</a>
                                    <button type="submit" class="btn btn-primary">Selanjutnya</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 3: Motivation -->
                        <?php if ($current_step == 3): ?>
                            <div class="card-header">
                                <h3 class="card-title">Motivasi dan Visi Misi</h3>
                                <p class="card-subtitle">Jelaskan motivasi dan visi misi Anda sebagai calon pengurus OSIS</p>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="reason" class="form-label">Alasan Ingin Menjadi Pengurus OSIS <span class="required">*</span></label>
                                    <textarea id="reason" name="reason" class="form-control" placeholder="Jelaskan alasan Anda ingin menjadi pengurus OSIS" rows="4" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="vision" class="form-label">Visi <span class="required">*</span></label>
                                    <textarea id="vision" name="vision" class="form-control" placeholder="Tuliskan visi Anda sebagai calon pengurus OSIS" rows="4" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="mission" class="form-label">Misi <span class="required">*</span></label>
                                    <textarea id="mission" name="mission" class="form-control" placeholder="Tuliskan misi Anda sebagai calon pengurus OSIS" rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-navigation">
                                    <a href="registration_form.php?step=2" class="btn btn-outline">Sebelumnya</a>
                                    <button type="submit" class="btn btn-primary">Selanjutnya</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Step 4: Documents -->
                        <?php if ($current_step == 4): ?>
                            <div class="card-header">
                                <h3 class="card-title">Unggah Dokumen</h3>
                                <p class="card-subtitle">Unggah dokumen yang diperlukan untuk pendaftaran</p>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Dokumen yang diunggah harus dalam format PDF, JPG, atau PNG dengan ukuran maksimal 2MB.</p>
                                </div>
                                
                                <div class="document-upload-container">
                                    <div class="document-upload-item">
                                        <label class="form-label">Surat Izin Orang Tua <span class="required">*</span></label>
                                        <div class="file-upload" id="parentPermissionUpload">
                                            <input type="file" id="parentPermission" name="parentPermission" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <i class="fas fa-upload"></i>
                                            <p>Klik untuk mengunggah atau seret file ke sini</p>
                                        </div>
                                        <p class="form-text">Surat pernyataan izin dari orang tua/wali untuk mengikuti kegiatan OSIS</p>
                                    </div>
                                    
                                    <div class="document-upload-item">
                                        <label class="form-label">Pas Foto <span class="required">*</span></label>
                                        <div class="file-upload" id="photoUpload">
                                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png" required>
                                            <i class="fas fa-upload"></i>
                                            <p>Klik untuk mengunggah atau seret file ke sini</p>
                                        </div>
                                        <p class="form-text">Pas foto berwarna ukuran 3x4 dengan latar belakang merah</p>
                                    </div>
                                    
                                    <div class="document-upload-item">
                                        <label class="form-label">Fotokopi Rapor <span class="required">*</span></label>
                                        <div class="file-upload" id="reportCardUpload">
                                            <input type="file" id="reportCard" name="reportCard" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <i class="fas fa-upload"></i>
                                            <p>Klik untuk mengunggah atau seret file ke sini</p>
                                        </div>
                                        <p class="form-text">Fotokopi rapor semester terakhir</p>
                                    </div>
                                    
                                    <div class="document-upload-item">
                                        <label class="form-label">Sertifikat Prestasi (Opsional)</label>
                                        <div class="file-upload" id="certificatesUpload">
                                            <input type="file" id="certificates" name="certificates" accept=".pdf,.jpg,.jpeg,.png">
                                            <i class="fas fa-upload"></i>
                                            <p>Klik untuk mengunggah atau seret file ke sini</p>
                                        </div>
                                        <p class="form-text">Sertifikat prestasi akademik atau non-akademik (jika ada)</p>
                                    </div>
                                </div>
                                
                                <div class="form-check agreement-check">
                                    <input type="checkbox" id="agreement" name="agreement" class="form-check-input" required>
                                    <label for="agreement" class="form-check-label">
                                        Saya menyatakan bahwa data yang saya isi adalah benar dan saya bersedia mengikuti seluruh rangkaian kegiatan seleksi pengurus OSIS dengan penuh tanggung jawab.
                                    </label>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-navigation">
                                    <a href="registration_form.php?step=3" class="btn btn-outline">Sebelumnya</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Kirim Pendaftaran</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            <?php else: ?>
                <!-- Success Message -->
                <div class="card text-center">
                    <div class="card-body success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Pendaftaran Berhasil!</h2>
                        <p>Terima kasih telah mendaftar sebagai calon pengurus OSIS SMKN 2 Sampang. Data Anda telah kami terima dan akan segera diproses.</p>
                        <p>Anda akan diarahkan ke dashboard dalam beberapa detik...</p>
                        <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
                    </div>
                </div>
                
                <script>
                    // Redirect to dashboard after 3 seconds
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 3000);
                </script>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Toggle organization fields visibility
        document.addEventListener('DOMContentLoaded', function() {
            const organizationCheckbox = document.getElementById('hasOrganizationExperience');
            const organizationFields = document.getElementById('organizationFields');
            
            if (organizationCheckbox && organizationFields) {
                // Initially hide organization fields
                organizationFields.style.display = 'none';
                
                // Toggle visibility on checkbox change
                organizationCheckbox.addEventListener('change', function() {
                    organizationFields.style.display = this.checked ? 'block' : 'none';
                });
            }
            
            // Enable submit button when agreement is checked
            const agreementCheckbox = document.getElementById('agreement');
            const submitBtn = document.getElementById('submitBtn');
            
            if (agreementCheckbox && submitBtn) {
                agreementCheckbox.addEventListener('change', function() {
                    submitBtn.disabled = !this.checked;
                });
            }
            
            // File upload preview
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const fileName = e.target.files[0]?.name || 'No file selected';
                    const uploadContainer = this.closest('.file-upload');
                    const textElement = uploadContainer.querySelector('p');
                    
                    if (e.target.files[0]) {
                        textElement.textContent = fileName;
                        uploadContainer.classList.add('has-file');
                    } else {
                        textElement.textContent = 'Klik untuk mengunggah atau seret file ke sini';
                        uploadContainer.classList.remove('has-file');
                    }
                });
            });
        });
    </script>
</body>
</html>
