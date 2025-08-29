<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('mahasiswa');

$pageTitle = "Pengajuan Judul Tugas Akhir";

// Get current mahasiswa
$mahasiswa = getMahasiswaByUserId($_SESSION['user_id']);
$mahasiswa_id = $mahasiswa['id'];

// Get tugas akhir for this mahasiswa
$tugas_akhir = getTugasAkhirByMahasiswa($mahasiswa_id);

// Get all dosen for dropdown
$dosen = getAllDosen();

// Submit new pengajuan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pengajuan'])) {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $dosen_id = trim($_POST['dosen_id']);
    
    // Validate input
    $errors = [];
    
    if (empty($judul) || empty($deskripsi) || empty($dosen_id)) {
        $errors[] = "Semua field harus diisi";
    }
    
    // Check if mahasiswa already has tugas akhir
    if ($tugas_akhir) {
        $errors[] = "Anda sudah mengajukan tugas akhir";
    }
    
    // Check file upload
    if (!isset($_FILES['proposal']) || $_FILES['proposal']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File proposal harus diupload";
    }
    
    // If no errors, process data
    if (empty($errors)) {
        try {
            // Upload file
            $uploadResult = uploadFile($_FILES['proposal']);
            
            if (isset($uploadResult['error'])) {
                $errors[] = $uploadResult['error'];
            } else {
                $proposal_path = $uploadResult['success'];
                
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO tugas_akhir (mahasiswa_id, dosen_id, judul, deskripsi, proposal_path, status) VALUES (?, ?, ?, ?, ?, 'Diajukan')");
                $stmt->execute([$mahasiswa_id, $dosen_id, $judul, $deskripsi, $proposal_path]);
                
                $success = "Pengajuan judul berhasil dikirim";
                
                // Refresh page to show new data
                header("Location: pengajuan.php");
                exit();
            }
            
        } catch (Exception $e) {
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Pengajuan Judul Tugas Akhir</h1>
        
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
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan Pengajuan:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Judul harus unik dan belum pernah diajukan sebelumnya</li>
                <li>Maksimal 3 pengajuan per semester</li>
                <li>Proposal harus dalam format PDF (maks. 2MB)</li>
                <li>Harus memilih pembimbing dari daftar dosen yang tersedia</li>
            </ul>
        </div>
        
        <?php if (!$tugas_akhir): ?>
            <!-- Pengajuan Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Ajukan Judul Baru</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="judul">Judul Proposal</label>
                        <input type="text" id="judul" name="judul" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan judul proposal">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi">Deskripsi Singkat</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan secara singkat tentang proposal TA Anda"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dosen_id">Pilihan Pembimbing</label>
                        <select id="dosen_id" name="dosen_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Dosen Pembimbing --</option>
                            <?php foreach ($dosen as $d): ?>
                                <?php if ($d['peran'] === 'Pembimbing' || $d['peran'] === 'Keduanya'): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['nama'] . ' (' . $d['nidn'] . ') - ' . $d['bidang_keahlian']; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="proposal">Upload Proposal (PDF, maks. 2MB)</label>
                        <input type="file" id="proposal" name="proposal" accept=".pdf" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-gray-500 text-xs mt-1">Hanya file PDF dengan ukuran maksimal 2MB</p>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="submit_pengajuan" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                            Ajukan Judul
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Current Pengajuan Status -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Status Pengajuan Anda</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50"><?php echo $tugas_akhir['judul']; ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pembimbing</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            <?php echo $tugas_akhir['dosen_nama'] ? $tugas_akhir['dosen_nama'] . ' (' . $tugas_akhir['nidn'] . ')' : 'Belum dipilih'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50 min-h-[100px]"><?php echo $tugas_akhir['deskripsi']; ?></p>
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
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50"><?php echo $tugas_akhir['catatan_dosen']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($tugas_akhir['proposal_path']): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Proposal</label>
                        <a href="<?php echo BASE_URL . '/' . $tugas_akhir['proposal_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i> Lihat Proposal
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($tugas_akhir['status'] === 'Ditolak'): ?>
                    <div class="mt-4">
                        <a href="pengajuan.php?edit=<?php echo $tugas_akhir['id']; ?>" class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition inline-flex items-center">
                            <i class="fas fa-edit mr-2"></i> Ajukan Ulang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Riwayat Pengajuan -->
        <?php
        // Get pengajuan history for this mahasiswa
        $stmt = $pdo->prepare("
            SELECT ta.*, d.nidn, u.nama as dosen_nama 
            FROM tugas_akhir ta 
            LEFT JOIN dosen d ON ta.dosen_id = d.id 
            LEFT JOIN users u ON d.user_id = u.id
            WHERE ta.mahasiswa_id = ? 
            ORDER BY ta.tanggal_pengajuan DESC
        ");
        $stmt->execute([$mahasiswa_id]);
        $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (count($riwayat) > 1): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat Pengajuan</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembimbing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($riwayat as $r): ?>
                                <?php if ($r['id'] !== $tugas_akhir['id']): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900"><?php echo $r['judul']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?php echo $r['dosen_nama'] ? $r['dosen_nama'] . ' (' . $r['nidn'] . ')' : 'Belum dipilih'; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatTanggal($r['tanggal_pengajuan']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php $statusClass = getStatusBadgeClass($r['status']); ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                <?php echo $r['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
