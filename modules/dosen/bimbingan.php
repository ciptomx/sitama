<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('dosen');

$pageTitle = "Manajemen Bimbingan";

// Get current dosen
$dosen = getDosenByUserId($_SESSION['user_id']);
$dosen_id = $dosen['id'];

// Get bimbingan for this dosen
$stmt = $pdo->prepare("
    SELECT b.*, ta.judul, m.nim, u.nama as mahasiswa_nama
    FROM bimbingan b
    JOIN tugas_akhir ta ON b.tugas_akhir_id = ta.id
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE ta.dosen_id = ?
    ORDER BY b.tanggal DESC, b.waktu DESC
");
$stmt->execute([$dosen_id]);
$bimbingan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update status bimbingan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $bimbingan_id = trim($_POST['bimbingan_id']);
        $status = trim($_POST['status']);
        $catatan = trim($_POST['catatan']);
        
        try {
            $stmt = $pdo->prepare("UPDATE bimbingan SET status = ?, catatan = ? WHERE id = ?");
            $stmt->execute([$status, $catatan, $bimbingan_id]);
            
            // Refresh page to show updated data
            header("Location: bimbingan.php");
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
    
    <div class="container mx-auto px极 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Bimbingan</h1>
        
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
            <h3 class="font-bold text-blue-800">Aturan Bimbingan:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Bimbingan hanya dapat diajukan setelah judul disetujui</li>
                <li>Minimal 8x bimbingan sebelum dapat mendaftar ujian</li>
                <li>Jadwal bimbingan harus dijadwalkan minimal 2 hari sebelumnya</li>
                <li>Pembimbing dapat menerima atau menolak jadwal bimbingan</li>
            </ul>
        </div>
        
        <!-- Bimbingan Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text极-500 uppercase tracking-wider">Judul TA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal/Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topik</th>
                        <th class="px-6 py-极 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($bimbingan as $b): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $b['mahasiswa_nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $b['nim']; ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo $b['judul']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatTanggal($b['tanggal']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($b['waktu'])); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo $b['topik']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $statusClass = getStatusBadgeClass($b['status']); ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $b['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openModal(<?php echo $b['id']; ?>, '<?php echo $b['status']; ?>', `<?php echo htmlspecialchars($b['catatan']); ?>`)" class="text-indigo-600 hover:text-indigo-900">
                                Kelola
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($bimbingan)): ?>
            <div class="bg-white rounded-lg shadow p-6 text-center mt-6">
                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-500">Tidak ada jadwal bimbingan</h3>
                <p class="text-gray-400">Belum ada mahasiswa yang mengajukan bimbingan kepada Anda.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kelola Bimbingan</h3>
                
                <form method="POST" action="">
                    <input type="hidden" id="bimbingan_id" name="bimbingan_id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status</label>
                        <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Menunggu">Menunggu</option>
                            <option value="Dikonfirmasi">Dikonfirmasi</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="catatan">Catatan Bimbingan</label>
                        <textarea id="catatan" name="catatan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Berikan catatan untuk mahasiswa..."></textarea>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover极bg-gray-600 transition mr-2">
                            Batal
                        </button>
                        <button type="submit" name="update_status" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                            Simpan
                        </极>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    function openModal(id, status, catatan) {
        document.getElementById('bimbingan_id').value = id;
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
