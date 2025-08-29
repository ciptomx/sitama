<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireRole('admin');

$pageTitle = "Edit Data Ujian";

// Get ujian ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ujian.php');
    exit();
}

$ujian_id = $_GET['id'];

// Get ujian data
$stmt = $pdo->prepare("
    SELECT u.*, ta.judul, ta.mahasiswa_id, m.nim, u2.nama as mahasiswa_nama
    FROM ujian u
    JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users u2 ON m.user_id = u2.id
    WHERE u.id = ?
");
$stmt->execute([$ujian_id]);
$ujian = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if ujian exists
if (!$ujian) {
    header('Location: ujian.php');
    exit();
}

// Get current penguji
$stmt = $pdo->prepare("
    SELECT p.dosen_id 
    FROM penguji p 
    WHERE p.ujian_id = ?
");
$stmt->execute([$ujian_id]);
$current_penguji = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all dosen for dropdown
$dosen = getAllDosen();

// Update ujian data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ujian'])) {
    $tanggal = trim($_POST['tanggal']);
    $waktu = trim($_POST['waktu']);
    $ruangan = trim($_POST['ruangan']);
    $status = trim($_POST['status']);
    $penguji = isset($_POST['penguji']) ? $_POST['penguji'] : [];
    
    // Validate input
    $errors = [];
    
    if (empty($tanggal) || empty($waktu) || empty($ruangan) || empty($status)) {
        $errors[] = "Semua field wajib diisi";
    }
    
    if (count($penguji) < 2) {
        $errors[] = "Minimal harus ada 2 penguji";
    }
    
    // Check if date is in the future for scheduled exams
    if ($status === 'Terjadwal') {
        $today = new DateTime();
        $examDate = new DateTime($tanggal);
        if ($examDate < $today) {
            $errors[] = "Tanggal ujian yang terjadwal tidak boleh di masa lalu";
        }
    }
    
    // If no errors, update data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update ujian table
            $stmt = $pdo->prepare("UPDATE ujian SET tanggal = ?, waktu = ?, ruangan = ?, status = ? WHERE id = ?");
            $stmt->execute([$tanggal, $waktu, $ruangan, $status, $ujian_id]);
            
            // Remove existing penguji
            $stmt = $pdo->prepare("DELETE FROM penguji WHERE ujian_id = ?");
            $stmt->execute([$ujian_id]);
            
            // Add new penguji
            foreach ($penguji as $dosen_id) {
                $stmt = $pdo->prepare("INSERT INTO penguji (ujian_id, dosen_id) VALUES (?, ?)");
                $stmt->execute([$ujian_id, $dosen_id]);
            }
            
            $pdo->commit();
            
            $success = "Data ujian berhasil diperbarui";
            
            // Refresh ujian data
            $stmt = $pdo->prepare("
                SELECT u.*, ta.judul, ta.mahasiswa_id, m.nim, u2.nama as mahasiswa_nama
                FROM ujian u
                JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
                JOIN mahasiswa m ON ta.mahasiswa_id = m.id
                JOIN users u2 ON m.user_id = u2.id
                WHERE u.id = ?
            ");
            $stmt->execute([$ujian_id]);
            $ujian = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Refresh current penguji
            $stmt = $pdo->prepare("SELECT dosen_id FROM penguji WHERE ujian_id = ?");
            $stmt->execute([$ujian_id]);
            $current_penguji = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
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
            <a href="modules/admin/ujian.php" class="极-blue-500 hover:text-blue-700 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Ujian
            </a>
            <h1 class="text-2xl font-bold text-gray-800 ml-4">Edit Data Ujian</h1>
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
        
        <!-- Ujian Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Ujian</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mahasiswa</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo htmlspecialchars($ujian['mahasiswa_nama']); ?> (<?php echo htmlspecialchars($ujian['nim']); ?>)
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas Akhir</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo htmlspecialchars($ujian['judul']); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($ujian['tanggal']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="waktu">Waktu *</label>
                        <input type="time" id="waktu" name="waktu" value="<?php echo htmlspecialchars(date('H:i', strtotime($ujian['waktu']))); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="ruangan">Ruangan *</label>
                        <input type="text" id="极angan" name="ruangan" value="<?php echo htmlspecialchars($ujian['ruangan']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:out极-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status *</label>
                    <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Terjadwal" <?php echo $ujian['status'] === 'Terjadwal' ? 'selected' : ''; ?>>Terjadwal</option>
                        <option value="Selesai" <?php echo $ujian['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?php echo $ujian['status'] === 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Penguji (Minimal 2) *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 max-h-60 overflow-y-auto p-2 border border-gray-300 rounded-md">
                        <?php foreach ($dosen as $d): ?>
                            <div class="flex items-center">
                                <input type="checkbox" id="penguji_<?php echo $d['id']; ?>" name="penguji[]" value="<?php echo $d['id']; ?>" 
                                    <?php echo in_array($d['id'], $current_penguji) ? 'checked' : ''; ?>
                                    class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="penguji_<?php echo $d['id']; ?>" class="text-sm text-gray-700">
                                    <?php echo htmlspecialchars($d['nama']); ?> (<?php echo htmlspecialchars($d['nidn']); ?>)
                                    <span class="block text-xs text-gray-500"><?php echo htmlspecialchars($d['bidang_keahlian']); ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Pilih minimal 2 dosen penguji</p>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="ujian.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition">
                        Batal
                    </a>
                    <button type="submit" name="update_ujian" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Information -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
            <h3 class="font-bold text-blue-800 mb-2">Aturan dan Constraints:</h3>
            <ul class="list-disc list-inside text-blue-700">
                <li>Ujian harus memiliki minimal 2 penguji</li>
                <li>Tanggal ujian yang terjadwal tidak boleh di masa lalu</li>
                <li>Status ujian akan mempengaruhi kemampuan untuk input nilai</li>
                <li>Perubahan data akan langsung tersimpan di database</li>
            </ul>
        </div>
        
        <!-- Current Penguji Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Daftar Penguji Saat Ini</h2>
            
            <?php if (!empty($current_penguji)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIDN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Penguji</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang Keahlian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $stmt = $pdo->prepare("
                                SELECT d.*, u.nama as dosen_nama 
                                FROM dosen d 
                                JOIN users u ON d.user_id = u.id 
                                WHERE d.id IN (" . implode(',', array_fill(0, count($current_penguji), '?')) . ")
                            ");
                            $stmt->execute($current_penguji);
                            $penguji_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($penguji_data as $p): 
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['nidn']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['dosen_nama']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['bidang_keahlian']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                        if ($p['peran'] === 'Pembimbing') {
                                            echo '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">Pembimbing</span>';
                                        } elseif ($p['peran'] === 'Penguji') {
                                            echo '<span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded">Penguji</span>';
                                        } else {
                                            echo '<span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded">Keduanya</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-user-times text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Belum ada penguji yang ditentukan</p>
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
            const tanggalInput = document.getElementById('tanggal');
            const statusSelect = document.getElementById('status');
            const pengujiCheckboxes = document.querySelectorAll('input[name="penguji[]"]');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                let errors = [];
                
                // Validate date for scheduled exams
                if (statusSelect.value === 'Terjadwal') {
                    const today = new Date();
                    const examDate = new Date(tanggalInput.value);
                    
                    if (examDate < today) {
                        valid = false;
                        errors.push('Tanggal ujian yang terjadwal tidak boleh di masa lalu');
                        tanggalInput.classList.add('border-red-500');
                    } else {
                        tanggalInput.classList.remove('border-red-500');
                    }
                }
                
                // Validate penguji count
                const selectedPenguji = Array.from(pengujiCheckboxes).filter(cb => cb.checked).length;
                if (selectedPenguji < 2) {
                    valid = false;
                    errors.push('Minimal harus memilih 2 penguji');
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
            
            // Real-time validation for date
            tanggalInput.addEventListener('change', function() {
                if (statusSelect.value === 'Terjadwal') {
                    const today = new Date();
                    const examDate = new Date(this.value);
                    
                    if (examDate < today) {
                        this.classList.add('border-red-500');
                    } else {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-green-500');
                    }
                }
            });
            
            // Real-time validation for penguji count
            pengujiCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const selectedCount = Array.from(pengujiCheckboxes).filter(cb => cb.checked).length;
                    const pengujiContainer = this.closest('.grid');
                    
                    if (selectedCount < 2) {
                        pengujiContainer.classList.add('border-red-300');
                        pengujiContainer.classList.remove('border-green-300');
                    } else {
                        pengujiContainer.classList.remove('border-red-300');
                        pengujiContainer.classList.add('border-green-300');
                    }
                });
            });
            
            // Update date validation when status changes
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Terjadwal') {
                    const today = new Date();
                    const examDate = new Date(tanggalInput.value);
                    
                    if (examDate < today) {
                        tanggalInput.classList.add('border-red-500');
                    }
                } else {
                    tanggalInput.classList.remove('border-red-500');
                    tanggalInput.classList.remove('border-green-500');
                }
            });
        });
    </script>
</body>
</html>
