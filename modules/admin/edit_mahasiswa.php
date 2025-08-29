<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireRole('admin');

$pageTitle = "Edit Data Mahasiswa";

// Get mahasiswa ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mahasiswa.php');
    exit();
}

$mahasiswa_id = $_GET['id'];

// Get mahasiswa data
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.nama, u.email 
    FROM mahasiswa m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.id = ?
");
$stmt->execute([$mahasiswa_id]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if mahasiswa exists
if (!$mahasiswa) {
    header('Location: mahasiswa.php');
    exit();
}

// Update mahasiswa data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_mahasiswa'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nim = trim($_POST['nim']);
    $prodi = trim($_POST['prodi']);
    $status = trim($_POST['status']);
    
    // Validate input
    $errors = [];
    
    if (empty($nama) || empty($email) || empty($nim) || empty($prodi)) {
        $errors[] = "Semua field wajib diisi";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!preg_match('/^[0-9]{10}$/', $nim)) {
        $errors[] = "NIM harus terdiri dari 10 digit angka";
    }
    
    // Check if NIM already exists (excluding current mahasiswa)
    $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id != ?");
    $stmt->execute([$nim, $mahasiswa_id]);
    if ($stmt->fetch()) {
        $errors[] = "NIM sudah digunakan oleh mahasiswa lain";
    }
    
    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $mahasiswa['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "Email sudah digunakan oleh pengguna lain";
    }
    
    // If no errors, update data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update users table
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $mahasiswa['user_id']]);
            
            // Update mahasiswa table
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nim = ?, prodi = ?, status = ? WHERE id = ?");
            $stmt->execute([$nim, $prodi, $status, $mahasiswa_id]);
            
            $pdo->commit();
            
            $success = "Data mahasiswa berhasil diperbarui";
            
            // Refresh mahasiswa data
            $stmt = $pdo->prepare("
                SELECT m.*, u.username, u.nama, u.email 
                FROM mahasiswa m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?
            ");
            $stmt->execute([$mahasiswa_id]);
            $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistem Manajemen Tugas Akhir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center mb-6">
            <a href="<?php 'BASE_URL'?>/modules/admin/mahasiswa.php" class="text-blue-500 hover:text-blue-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Mahasiswa
            </a>
            <h1 class="text-2xl font-bold text-gray-800 ml-4">Edit Data Mahasiswa</h1>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($mahasiswa['username']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                        <p class="text-gray-500 text-xs mt-1">Username tidak dapat diubah</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nim">NIM *</label>
                        <input type="text" id="nim" name="nim" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" required pattern="[0-9]{10}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-gray-500 text-xs mt-1">10 digit angka</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama极" name="nama" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($mahasiswa['email']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-500 text-xs mt-1">Format: email@unmuhpnk.ac.id</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="prodi">Program Studi *</label>
                        <select id="prodi" name="prodi" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Program Studi</option>
                            <option value="Teknik Informatika" <?php echo $mahasiswa['prodi'] === 'Teknik Informatika' ? 'selected' : ''; ?>>Teknik Informatika</option>
                            <option value="Sistem Informasi" <?php echo $mahasiswa['prodi'] === 'Sistem Informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                            <option value="Teknik Komputer" <?php echo $mahasiswa['prodi'] === 'Teknik Komputer' ? 'selected' : ''; ?>>Teknik Komputer</option>
                            <option value="Teknik Elektro" <?php echo $mahasiswa['prodi'] === 'Teknik Elektro' ? 'selected' : ''; ?>>Teknik Elektro</option>
                            <option value="Manajemen Informatika" <?php echo $mahasiswa['prodi'] === 'Manajemen Informatika' ? 'selected' : ''; ?>>Manajemen Informatika</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status *</label>
                        <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Aktif" <?php echo $mahasiswa['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Cuti" <?php echo $mahasiswa['status'] === 'Cuti' ? 'selected' : ''; ?>>Cuti</option>
                            <option value="Lulus" <?php echo $mahasiswa['status'] === 'Lulus' ? 'selected' : ''; ?>>Lulus</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="<?php 'BASE_URL'?>/modules/admin/mahasiswa.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition">
                        Batal
                    </a>
                    <button type="submit" name="update_mahasiswa" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Information -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <h3 class="font-bold text-blue-800 mb-2">Informasi Penting:</h3>
            <ul class="list-disc list-inside text-blue-700">
                <li>Pastikan NIM benar dan unik (tidak duplikat dengan mahasiswa lain)</li>
                <li>Email harus menggunakan format email institusi</li>
                <li>Status mahasiswa akan mempengaruhi kemampuan untuk mengajukan tugas akhir</li>
                <li>Perubahan data akan langsung tersimpan di database</li>
            </ul>
        </div>
        
        <!-- Tugas Akhir Information -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Tugas Akhir</h2>
            
            <?php
            // Get tugas akhir information
            $stmt = $pdo->prepare("
                SELECT ta.*, d.nidn, u.nama as dosen_nama 
                FROM tugas_akhir ta 
                LEFT JOIN dosen d ON ta.dosen_id = d.id 
                LEFT JOIN users u ON d.user_id = u.id
                WHERE ta.mahasiswa_id = ?
            ");
            $stmt->execute([$mahasiswa_id]);
            $tugas_akhir = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            
            <?php if ($tugas_akhir): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas Akhir</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50"><?php echo htmlspecialchars($tugas_akhir['judul']); ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pembimbing</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            <?php echo $tugas_akhir['dosen_nama'] ? htmlspecialchars($tugas_akhir['dosen_nama']) . ' (' . htmlspecialchars($tugas_akhir['nidn']) . ')' : 'Belum ditentukan'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                        <div class="px-3 py-2">
                            <?php $statusClass = getStatusBadgeClass($tugas_akhir['status']); ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $tugas_akhir['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pengajuan</label>
                        <p class="px-3 py-2"><?php echo formatTanggal($tugas_akhir['tanggal_pengajuan']); ?></p>
                    </div>
                </div>
                
                <?php if ($tugas_akhir['catatan_dosen']): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Komentar Pembimbing</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50"><?php echo htmlspecialchars($tugas_akhir['catatan_dosen']); ?></p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Mahasiswa ini belum mengajukan tugas akhir</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const nimInput = document.getElementById('nim');
            const emailInput = document.getElementById('email');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                let errors = [];
                
                // Validate NIM
                if (!/^\d{10}$/.test(nimInput.value)) {
                    valid = false;
                    errors.push('NIM harus terdiri dari 10 digit angka');
                    nimInput.classList.add('border-red-500');
                } else {
                    nimInput.classList.remove('border-red-500');
                }
                
                // Validate email format
                if (!emailInput.value.endsWith('@unmuhpnk.ac.id')) {
                    valid = false;
                    errors.push('Email harus menggunakan domain @unmuhpnk.ac.id');
                    emailInput.classList.add('border-red-500');
                } else {
                    emailInput.classList.remove('border-red-500');
                }
                
                if (!valid) {
                    e.preventDefault();
                    
                    // Show error message
                    let errorHtml = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
                    errorHtml += '<ul>';
                    errors.forEach(error => {
                        errorHtml += '<li>' + error + '</li>';
                    });
                    errorHtml += '</ul></div>';
                    
                    // Remove existing error messages
                    const existingErrors = document.querySelector('.bg-red-100');
                    if (existingErrors) {
                        existingErrors.remove();
                    }
                    
                    // Insert error message
                    form.insertAdjacentHTML('beforebegin', errorHtml);
                    
                    // Scroll to errors
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
            
            // Real-time validation
            nimInput.addEventListener('input', function() {
                if (/^\d{10}$/.test(this.value)) {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } else {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-red-500');
                }
            });
            
            emailInput.addEventListener('input', function() {
                if (this.value.endsWith('@unmuhpnk.ac.id')) {
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } else {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-red-500');
                }
            });
        });
    </script>
</body>
</html>
