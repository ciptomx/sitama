<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('dosen');

$pageTitle = "Penilaian Ujian";

// Get current dosen
$dosen = getDosenByUserId($_SESSION['user_id']);
$dosen_id = $dosen['id'];

// Get ujian for this dosen as penguji
$stmt = $pdo->prepare("
    SELECT u.*, ta.judul, m.nim, mhs.nama as mahasiswa_nama, p.nilai_presentasi, p.nilai_makalah, p.nilai_responsi, p.nilai_total, p.catatan
    FROM ujian u
    JOIN penguji pj ON u.id = pj.ujian_id
    JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users mhs ON m.user_id = mhs.id
    LEFT JOIN penilaian p ON u.id = p.ujian_id AND p.dosen_id = ?
    WHERE pj.dosen_id = ?
    ORDER BY u.tanggal DESC, u.waktu DESC
");
$stmt->execute([$dosen_id, $dosen_id]);
$ujian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Submit penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nilai'])) {
    $ujian_id = trim($_POST['ujian_id']);
    $nilai_presentasi = trim($_POST['nilai_presentasi']);
    $nilai_makalah = trim($_POST['nilai_makalah']);
    $nilai_responsi = trim($_POST['nilai_responsi']);
    $catatan = trim($_POST['catatan']);
    
    // Validate input
    $errors = [];
    
    if (empty($nilai_presentasi) || empty($nilai_makalah) || empty($nilai_responsi)) {
        $errors[] = "Semua nilai harus diisi";
    }
    
    if (!is_numeric($nilai_presentasi) || $nilai_presentasi < 0 || $nilai_presentasi > 100) {
        $errors[] = "Nilai presentasi harus antara 0-100";
    }
    
    if (!is_numeric($nilai_makalah) || $nilai_makalah < 0 || $nilai_makalah > 100) {
        $errors[] = "Nilai makalah harus antara 0-100";
    }
    
    if (!is_numeric($nilai_responsi) || $nilai_responsi < 0 || $nilai_responsi > 100) {
        $errors[] = "Nilai responsi harus antara 0-100";
    }
    
    // Calculate total score
    $nilai_total = ($nilai_presentasi + $nilai_makalah + $nilai_responsi) / 3;
    
    // If no errors, insert/update data
    if (empty($errors)) {
        try {
            // Check if penilaian already exists
            $stmt = $pdo->prepare("SELECT id FROM penilaian WHERE ujian_id = ? AND dosen_id = ?");
            $stmt->execute([$ujian_id, $dosen_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE penilaian SET nilai_presentasi = ?, nilai_makalah = ?, nilai_responsi = ?, nilai_total = ?, catatan = ? WHERE ujian_id = ? AND dosen_id = ?");
                $stmt->execute([$nilai_presentasi, $nilai_makalah, $nilai_responsi, $nilai_total, $catatan, $ujian_id, $dosen_id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO penilaian (ujian_id, dosen_id, nilai_presentasi, nilai_makalah, nilai_responsi, nilai_total, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$ujian_id, $dosen_id, $nilai_presentasi, $nilai_makalah, $nilai_responsi, $nilai_total, $catatan]);
            }
            
            $success = "Penilaian berhasil disimpan";
            
            // Refresh page to show updated data
            header("Location: penilaian.php");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $极->getMessage();
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Penilaian Ujian</h1>
        
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
            <h3 class="font-bold text-blue-800">Aturan Penilaian:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Hanya dapat mengisi nilai setelah ujian selesai</li>
                <li>Tidak dapat mengubah nilai yang sudah disubmit</li>
                <li>Nilai harus dalam skala 0-100</li>
                <li>Nilai akhir merupakan rata-rata dari presentasi, makalah, dan responsi</li>
            </ul>
        </div>
        
        <!-- Ujian Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul TA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Ujian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Penilaian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($ujian as $u): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $u['mahasiswa_nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $u['nim']; ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo $u['judul']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatTanggal($u['tanggal']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($u['waktu'])); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($u['nilai_total'] !== null): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Sudah Dinilai (<?php echo number_format($u['nilai_total'], 2); ?>)
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Belum Dinilai
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openModal(
                                <?php echo $u['id']; ?>, 
                                '<?php echo $u['mahasiswa_nama']; ?>',
                                '<?php echo $u['judul']; ?>',
                                <?php echo $u['nilai_presentasi'] ?? ''; ?>,
                                <?php echo $u['nilai_makalah'] ?? ''; ?>,
                                <?php echo $u['nilai_responsi'] ?? ''; ?>,
                                `<?php echo htmlspecialchars($u['catatan'] ?? ''); ?>`
                            )" class="text-indigo-600 hover:text-indigo-900">
                                <?php echo $u['nilai_total'] !== null ? 'Edit Nilai' : 'Beri Nilai'; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($ujian)): ?>
            <div class="bg-white rounded-lg shadow p-6 text-center mt-6">
                <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-500">Tidak ada ujian untuk dinilai</h3>
                <p class="text-gray-400">Anda belum ditugaskan sebagai penguji untuk ujian apapun.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Penilaian Modal -->
    <div id="penilaianModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Penilaian Ujian</h3>
                
                <form method="POST" action="">
                    <input type="hidden" id="ujian_id" name="ujian_id">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mahasiswa</label>
                        <div id="mahasiswaDisplay" class="px-3 py-2 border border-gray-300 rounded-md bg-gray-100"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas Akhir</label>
                        <div id="judulDisplay" class="px-3 py-2 border border-gray-300 rounded-md bg-gray-100"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nilai_presentasi">Nilai Presentasi</label>
                            <input type="number" id="nilai_presentasi" name="nilai_presentasi" min="0" max="100" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nilai_makalah">Nilai Makalah</label>
                            <input type="number" id="nilai_makalah" name="nilai_makalah" min="0" max="100" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nilai_responsi">Nilai Responsi</label>
                            <input type="number" id="nilai_responsi" name="nilai_responsi" min="0" max="100" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="catatan">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Berikan catatan untuk mahasiswa..."></textarea>
                    </div>
                    
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition mr-2">
                            Batal
                        </button>
                        <button type="submit" name="submit_nilai" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:极-blue-600 transition">
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    function openModal(ujian_id, mahasiswa, judul, presentasi, makalah, responsi, catatan) {
        document.getElementById('ujian_id').value = ujian_id;
        document.getElementById('mahasiswaDisplay').textContent = mahasiswa;
        document.getElementById('judulDisplay').textContent = judul;
        document.getElementById('nilai_presentasi').value = presentasi;
        document.getElementById('nilai_makalah').value = makalah;
        document.getElementById('nilai_responsi').value = responsi;
        document.getElementById('catatan').value = catatan;
        document.getElementById('penilaianModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('penilaianModal').classList.add('hidden');
    }
    
    // Close modal if clicked outside
    window.onclick = function(event) {
        const modal = document.getElementById('penilaianModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>
