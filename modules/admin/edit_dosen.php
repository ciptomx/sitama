<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireRole('admin');

$pageTitle = "Edit Data Dosen";

// Get dosen ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dosen.php');
    exit();
}

$dosen_id = $_GET['id'];

// Get dosen data
$stmt = $pdo->prepare("
    SELECT d.*, u.username, u.nama, u.email 
    FROM dosen d 
    JOIN users u ON d.user_id = u.id 
    WHERE d.id = ?
");
$stmt->execute([$dosen_id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if dosen exists
if (!$dosen) {
    header('Location: dosen.php');
    exit();
}

// Update dosen data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_dosen'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nidn = trim($_POST['nidn']);
    $bidang_keahlian = trim($_POST['bidang_keahlian']);
    $peran = trim($_POST['peran']);
    
    // Validate input
    $errors = [];
    
    if (empty($nama) || empty($email) || empty($nidn) || empty($bidang_keahlian)) {
        $errors[] = "Semua field wajib diisi";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!preg_match('/^[0-9]{8,10}$/', $nidn)) {
        $errors[] = "NIDN harus terdiri dari 8-10 digit angka";
    }
    
    // Check if NIDN already exists (excluding current dosen)
    $stmt = $pdo->prepare("SELECT id FROM dosen WHERE nidn = ? AND id != ?");
    $stmt->execute([$nidn, $dosen_id]);
    if ($stmt->fetch()) {
        $errors[] = "NIDN sudah digunakan oleh dosen lain";
    }
    
    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $dosen['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = "Email sudah digunakan oleh pengguna lain";
    }
    
    // If no errors, update data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update users table
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $dosen['user_id']]);
            
            // Update dosen table
            $stmt = $pdo->prepare("UPDATE dosen SET nidn = ?, bidang_keahlian = ?, peran = ? WHERE id = ?");
            $stmt->execute([$nidn, $bidang_keahlian, $peran, $dosen_id]);
            
            $pdo->commit();
            
            $success = "Data dosen berhasil diperbarui";
            
            // Refresh dosen data
            $stmt = $极->prepare("
                SELECT d.*, u.username, u.nama, u.email 
                FROM dosen d 
                JOIN users u ON d.user_id = u.id 
                WHERE d.id = ?
            ");
            $stmt->execute([$dosen_id]);
            $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
            
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
            <a href="modules/admin/dosen.php" class="text-blue-500 hover:text-blue-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Dosen
            </a>
            <h1 class="text-2xl font-bold text-gray-800 ml-4">Edit Data Dosen</h1>
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
                <div class="grid grid-cols-1 md:极-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($dosen['username']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                        <p class="text-gray-500 text-xs mt-1">Username tidak dapat diubah</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb极" for="nidn">NIDN *</label>
                        <input type="text" id="nidn" name="nidn" value="<?php echo htmlspecialchars($dosen['nidn']); ?>" required pattern="[0-9]{8,10}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-gray-500 text-xs mt-1">8-10 digit angka</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($dosen['nama']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($dosen['email']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-500 text-xs mt-1">Format: email@unmuhpnk.ac.id</极>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="bidang_keahlian">Bidang Keahlian *</label>
                        <input type="text" id="bidang_keahlian" name="bidang_keahlian" value="<?php echo htmlspecialchars($dosen['bidang_keahlian']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="peran">Peran *</label>
                        <select id="peran" name="peran" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Pembimbing" <?php echo $dosen['peran'] === 'Pembimbing' ? 'selected' : ''; ?>>Pembimbing</option>
                            <option value="Penguji" <?php echo $dosen['peran'] === 'Penguji' ? 'selected' : ''; ?>>Penguji</option>
                            <option value="Keduanya" <?php echo $dosen['peran'] === 'Keduanya' ? 'selected' : ''; ?>>Keduanya</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="dosen.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition">
                        Batal
                    </a>
                    <button type="submit" name="update_dosen" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Information -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <h3 class="font-bold text-blue-800 mb-2">Informasi Penting:</h3>
            <ul class="list-disc list-inside text-blue-700">
                <li>Pastikan NIDN benar dan unik (tidak duplikat dengan dosen lain)</li>
                <li>Email harus menggunakan format email institusi</li>
                <li>Peran dosen akan menentukan kemampuan sebagai pembimbing/penguji</li>
                <li>Perubahan data akan langsung tersimpan di database</li>
            </ul>
        </div>
        
        <!-- Bimbingan Information -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Bimbingan</h2>
            
            <?php
            // Get bimbingan information
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_bimbingan 
                FROM tugas_akhir 
                WHERE dosen_id = ?
            ");
            $stmt->execute([$dosen_id]);
            $bimbingan_count = $stmt->fetch(PDO::FETCH_ASSOC)['total_bimbingan'];
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $bimbingan_count; ?></div>
                    <div class="text-sm text-blue-500">Total Mahasiswa Bimbingan</div>
                </div>
                
                <?php
                // Get status counts
                $stmt = $pdo->prepare("
                    SELECT status, COUNT(*) as count 
                    FROM tugas_akhir 
                    WHERE dosen_id = ?
                    GROUP BY status
                ");
                $stmt->execute([$dosen_id]);
                $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $status_map = [
                    'Diajukan' => 'yellow',
                    'Review' => 'yellow', 
                    'Disetujui' => 'green',
                    'Ditolak' => 'red',
                    'Selesai' => 'blue'
                ];
                
                foreach ($status_counts as $status): 
                    $color = $status_map[$status['status']] ?? 'gray';
                ?>
                    <div class="bg-<?php echo $color; ?>-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-<?php echo $color; ?>-600"><?php echo $status['count']; ?></div>
                        <div class="text-sm text-<?php echo $color; ?>-500">Status <?php echo $status['status']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- List of Mahasiswa Bimbingan -->
            <?php if ($bimbingan_count > 0): ?>
                <h3 class="font-semibold text-gray-700 mb-3">Daftar Mahasiswa Bimbingan:</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul TA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT ta.*, m.nim, u.nama as mahasiswa_nama
                                FROM tugas_akhir ta
                                JOIN mahasiswa m ON ta.mahasiswa_id = m.id
                                JOIN users u ON m.user_id = u.id
                                WHERE ta.dosen_id = ?
                                ORDER BY ta.tanggal_pengajuan DESC
                            ");
                            $stmt->execute([$dosen_id]);
                            $mahasiswa_bimbingan = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($mahasiswa_bimbingan as $mhs): 
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $mhs['nim']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $mhs['mahasiswa_nama']; ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $mhs['judul']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php $statusClass = getStatusBadgeClass($mhs['status']); ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                            <?php echo $mhs['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-user-graduate text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Dosen ini belum memiliki mahasiswa bimbingan</p>
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
            const nidnInput = document.getElementById('nidn');
            const emailInput = document.getElementById('email');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                let errors = [];
                
                // Validate NIDN
                if (!/^\d{8,10}$/.test(nidnInput.value)) {
                    valid = false;
                    errors.push('NIDN harus terdiri dari 8-10 digit angka');
                    nidnInput.classList.add('border-red-500');
                } else {
                    nidnInput.classList.remove('border-red-500');
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
            nidnInput.addEventListener('input', function() {
                if (/^\d{8,10}$/.test(this.value)) {
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
