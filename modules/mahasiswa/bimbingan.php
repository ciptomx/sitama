<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('mahasiswa');

$pageTitle = "Manajemen Bimbingan";

// Get current mahasiswa
$mahasiswa = getMahasiswaByUserId($_SESSION['user_id']);
$mahasiswa_id = $mahasiswa['id'];

// Get tugas akhir for this mahasiswa
$tugas_akhir = getTugasAkhirByMahasiswa($mahasiswa_id);

// Get bimbingan for this mahasiswa
$bimbingan = [];
if ($tugas_akhir) {
    $bimbingan = getBimbinganByTugasAkhir($tugas_akhir['id']);
}

// Submit new bimbingan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_bimbingan'])) {
    $tanggal = trim($_POST['tanggal']);
    $waktu = trim($_POST['waktu']);
    $topik = trim($_POST['topik']);
    
    // Validate input
    $errors = [];
    
    if (empty($tanggal) || empty($waktu) || empty($topik)) {
        $errors[] = "Semua field harus diisi";
    }
    
    if (!$tugas_akhir || $tugas_akhir['status'] !== 'Disetujui') {
        $errors[] = "Judul TA harus disetujui terlebih dahulu sebelum mengajukan bimbingan";
    }
    
    // Check if date is at least 2 days from now
    $today = new DateTime();
    $selectedDate = new DateTime($tanggal);
    $interval = $today->diff($selectedDate);
    
    if ($interval->days < 2) {
        $errors[] = "Bimbingan harus dijadwalkan minimal 2 hari dari sekarang";
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bimbingan (tugas_akhir_id, tanggal, waktu, topik, status) VALUES (?, ?, ?, ?, 'Menunggu')");
            $stmt->execute([$tugas_akhir['id'], $tanggal, $waktu, $topik]);
            
            $success = "Jadwal bimbingan berhasil diajukan";
            
            // Refresh page to show new data
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
    
    <div class="container mx-auto px-4 py-6">
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
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan Bimbingan:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Hanya dapat mengajukan bimbingan setelah judul disetujui</li>
                <li>Minimal 8x bimbingan sebelum dapat mendaftar ujian</li>
                <li>Jadwal bimbingan harus dijadwalkan minimal 2 hari sebelumnya</li>
                <li>Pembimbing dapat menerima atau menolak jadwal bimbingan</li>
            </ul>
        </div>
        
        <?php if ($tugas_akhir && $tugas_akhir['status'] === 'Disetujui'): ?>
            <!-- Ajukan Bimbingan Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Ajukan Jadwal Bimbingan</h2>
                <form method="POST" action="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="tanggal">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" required min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="waktu">Waktu</label>
                            <input type="time" id="waktu" name="waktu" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:out极-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text极-700 text-sm font-bold mb-2" for="topik">Topik Bahasan</label>
                        <textarea id="topik" name="topik" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan topik yang akan dibahas..."></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="submit_bimbingan" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                            Ajukan Bimbingan
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($tugas_akhir && $tugas_akhir['status'] !== 'Disetujui'): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                <p>Anda hanya dapat mengajukan bimbingan setelah judul TA disetujui. Status pengajuan Anda saat ini: <strong><?php echo $tugas_akhir['status']; ?></strong></p>
            </div>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                <p>Anda belum mengajukan judul TA. Silakan ajukan judul TA terlebih dahulu sebelum mengajukan bimbingan.</p>
            </div>
        <?php endif; ?>
        
        <!-- Daftar Bimbingan -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Daftar Bimbingan</h2>
            
            <?php if (!empty($bimbingan)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal/Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide极 divide-gray-200">
                            <?php foreach ($bimbingan as $b): ?>
                            <tr>
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
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500"><?php echo $b['catatan'] ? $b['catatan'] : '-'; ?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Bimbingan Stats -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php
                    $total_bimbingan = count($bimbingan);
                    $selesai_bimbingan = count(array_filter($bimbingan, function($b) {
                        return $b['status'] === 'Selesai';
                    }));
                    $menunggu_bimbingan = count(array_filter($bimbingan, function($b) {
                        return $b['status'] === 'Menunggu';
                    }));
                    ?>
                    
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo $total_bimbingan; ?></div>
                        <div class="text-sm text-gray-500">Total Bimbingan</div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $selesai_bimbingan; ?></div>
                        <div class="text-sm text-gray-500">Bimbingan Selesai</div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600"><?php echo $menunggu_bimbingan; ?></div>
                        <div class="text-sm text-gray-500">Menunggu Konfirmasi</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-500">Belum ada jadwal bimbingan</h3>
                    <p class="text-gray-400">Ajukan jadwal bimbingan pertama Anda menggunakan form di atas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
