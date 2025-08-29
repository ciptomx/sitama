<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('admin');

$pageTitle = "Manajemen Ujian";

// Get all ujian
$stmt = $pdo->query("
    SELECT u.*, ta.judul, m.nim, mhs.nama as mahasiswa_nama
    FROM ujian u
    JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users mhs ON m.user_id = mhs.id
    ORDER BY u.tanggal DESC, u.waktu DESC
");
$ujian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all dosen for penguji
$dosen = getAllDosen();

// Add new ujian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ujian'])) {
    $tugas_akhir_id = trim($_POST['tugas_akhir_id']);
    $tanggal = trim($_POST['tanggal']);
    $waktu = trim($_POST['waktu']);
    $ruangan = trim($_POST['ruangan']);
    $penguji = isset($_POST['penguji']) ? $_POST['penguji'] : [];
    
    // Validate input
    $errors = [];
    
    if (empty($tugas_akhir_id) || empty($tanggal) || empty($waktu) || empty($ruangan)) {
        $errors[] = "Semua field harus diisi";
    }
    
    if (count($penguji) < 2) {
        $errors[] = "Minimal harus ada 2 penguji";
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert into ujian table
            $stmt = $pdo->prepare("INSERT INTO ujian (tugas_akhir_id, tanggal, waktu, ruangan) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tugas_akhir_id, $tanggal, $waktu, $ruangan]);
            $ujian_id = $pdo->lastInsertId();
            
            // Insert penguji
            foreach ($penguji as $dosen_id) {
                $stmt = $pdo->prepare("INSERT INTO penguji (ujian_id, dosen_id) VALUES (?, ?)");
                $stmt->execute([$ujian_id, $dosen_id]);
            }
            
            $pdo->commit();
            
            // Refresh page to show new data
            header("Location: ujian.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get tugas akhir yang sudah disetujui untuk dropdown
$stmt = $pdo->query("
    SELECT ta.id, ta.judul, m.nim, u.nama as mahasiswa_nama
    FROM tugas_akhir ta
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE ta.status = 'Disetujui'
    AND ta.id NOT IN (SELECT tugas_akhir_id FROM ujian)
    ORDER BY m.nim
");
$tugas_akhir = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistem Manajemen Tugas Akhir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0极/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Ujian</h1>
            <button onclick="toggleAddForm()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-2"></i>Jadwalkan Ujian
            </button>
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
        
        <!-- Add Ujian Form -->
        <div id="add-form" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Jadwalkan Ujian Baru</h2>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tugas_akhir_id">Tugas Akhir</label>
                    <select id="tugas_akhir_id" name="tugas_akhir_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Tugas Akhir</option>
                        <?php foreach ($tugas_akhir as $ta): ?>
                            <option value="<?php echo $ta['id']; ?>"><?php echo $ta['nim'] . ' - ' . $ta['mahasiswa_nama'] . ' - ' . $ta['judul']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text极 font-bold mb-2" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="waktu">Waktu</label>
                        <input type="time" id="waktu" name="waktu" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="ruangan">Ruangan</label>
                    <input type="text" id="ruangan" name="ruangan" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Penguji (Minimal 2)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                        <?php foreach ($dosen as $d): ?>
                            <div class="flex items-center">
                                <input type="checkbox" id="penguji_<?php echo $d['id']; ?>" name="penguji[]" value="<?php echo $d['id']; ?>" class="mr-2">
                                <label for="penguji_<?php echo $d['id']; ?>"><?php echo $d['nama'] . ' (' . $d['nidn'] . ')'; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="toggleAddForm()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition mr-2">
                        Batal
                    </button>
                    <button type="submit" name="add_ujian" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan dan Constraints:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Ujian hanya dapat dijadwalkan untuk mahasiswa yang telah menyelesaikan semua bimbingan wajib</li>
                <li>Harus memiliki minimal 2 penguji selain pembimbing</li>
                <li>Nilai akhir merupakan rata-rata dari semua penguji</li>
                <li>Ujian ulang hanya dapat diambil maksimal 2 kali</li>
            </ul>
        </div>
        
        <!-- Ujian Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal/Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $u['ruangan']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $statusClass = getStatusBadgeClass($u['status']); ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $u['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="detail_ujian.php?id=<?php echo $u['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Detail</a>
                            <a href="edit_ujian.php?id=<?php echo $u['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                            <a href="ujian.php?delete=<?php echo $u['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ujian ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
    function toggleAddForm() {
        document.getElementById('add-form').classList.toggle('hidden');
    }
    </script>
</body>
</html>
