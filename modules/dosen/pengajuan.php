<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('dosen');

$pageTitle = "Review Pengajuan Judul";

// Get current dosen
$dosen = getDosenByUserId($_SESSION['user_id']);
$dosen_id = $dosen['id'];

// Get pengajuan for this dosen
$pengajuan = getPengajuanByDosen($dosen_id);

// Update status pengajuan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $tugas_akhir_id = trim($_POST['tugas_akhir_id']);
    $status = trim($_POST['status']);
    $catatan = trim($_POST['catatan']);
    
    // Validate input
    $errors = [];
    
    if (empty($tugas_akhir_id) || empty($status)) {
        $errors[] = "Status harus dipilih";
    }
    
    if ($status === 'Ditolak' && empty($catatan)) {
        $errors[] = "Komentar harus diisi jika menolak pengajuan";
    }
    
    // If no errors, update data
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE tugas_akhir SET status = ?, catatan_dosen = ? WHERE id = ?");
            $stmt->execute([$status, $catatan, $tugas_akhir_id]);
            
            // Refresh page to show updated data
            header("Location: pengajuan.php");
            exit();
            
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Review Pengajuan Judul</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan dan Constraints:</h3>
            <ul class="list-disc list-inside text-blue-700 mt极">
                <li>Hanya dapat mereview pengajuan judul dari mahasiswa yang dibimbing</li>
                <li>Harus memberikan komentar jika menolak pengajuan</li>
                <li>Hanya dapat mengisi nilai setelah ujian selesai</li>
                <li>Tidak dapat mengubah nilai yang sudah disubmit</li>
            </ul>
        </div>
        
        <!-- Pengajuan Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium极text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pengajuan as $p): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $p['mahasiswa_nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $p['nim']; ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo $p['judul']; ?></div>
                            <?php if (!empty($p['deskripsi'])): ?>
                                <div class="text-sm text-gray-500 mt-1"><?php echo substr($p['deskripsi'], 0, 100) . '...'; ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatTanggal($p['tanggal_pengajuan']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $statusClass = getStatusBadgeClass($p['status']); ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $p['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openModal(<?php echo $p['id']; ?>, '<?php echo $p['judul']; ?>', '<?php echo $p['status']; ?>', `<?php echo htmlspecialchars($p['catatan_dosen']); ?>`)" class="text-indigo-600 hover:text-indigo-900">
                                Review
                            </button>
                            <?php if (!empty($p['proposal_path'])): ?>
                                <a href="<?php echo BASE_URL . '/' . $p['proposal_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 ml-3">
                                    Lihat Proposal
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($pengajuan)): ?>
            <div class="bg-white rounded-lg shadow p-6 text-center mt-6">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-500">Tidak ada pengajuan judul</h3>
                <p class="text-gray-400">Belum ada mahasiswa yang mengajukan judul tugas akhir kepada Anda.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Review Judul Tugas Akhir</h3>
                
                <form method="POST" action="">
                    <input type="hidden" id="tugas_akhir_id" name="tugas_akhir_id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="judul">Judul</label>
                        <div id="judulDisplay" class="px-3 py-2 border border-gray-300 rounded-md bg-gray-100"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status</label>
                        <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Review">Review</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="catatan">Komentar</label>
                        <textarea id="catatan" name="catatan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Berikan komentar untuk mahasiswa..."></textarea>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition mr-2">
                            Batal
                        </button>
                        <button type="submit" name="update_status" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    function openModal(id, judul, status, catatan) {
        document.getElementById('tugas_akhir_id').value = id;
        document.getElementById('judulDisplay').textContent = judul;
        document.getElementById('status').value = status;
        document.getElementById('catatan').value = catatan;
        document.getElementById('reviewModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }
    
    // Close modal if clicked outside
    window.onclick = function(event) {
        const modal = document.getElementById('reviewModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>
