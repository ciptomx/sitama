<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user has access
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Detail Ujian";

// Get ujian ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect based on role
    if (hasRole('admin')) {
        header('Location: modules/admin/ujian.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$ujian_id = $_GET['id'];

// Get ujian data
$stmt = $pdo->prepare("
    SELECT u.*, ta.judul, m.nim, mhs.nama as mahasiswa_nama, mhs.email as mahasiswa_email,
           d.nidn as pembimbing_nidn, d2.nama as pembimbing_nama
    FROM ujian u
    JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
    JOIN mahasiswa m ON ta.mahasiswa_id = m.id
    JOIN users mhs ON m.user_id = mhs.id
    LEFT JOIN dosen d ON ta.dosen_id = d.id
    LEFT JOIN users d2 ON d.user_id = d2.id
    WHERE u.id = ?
");
$stmt->execute([$ujian_id]);
$ujian = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if ujian exists
if (!$ujian) {
    // Redirect based on role
    if (hasRole('admin')) {
        header('Location: modules/admin/ujian.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

// Get penguji data
$stmt = $pdo->prepare("
    SELECT p.*, d.nidn, u.nama as dosen_nama, u.email as dosen_email
    FROM penguji p
    JOIN dosen d ON p.dosen_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE p.ujian_id = ?
");
$stmt->execute([$ujian_id]);
$penguji = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get penilaian data
$stmt = $pdo->prepare("
    SELECT p.*, d.nidn, u.nama as dosen_nama
    FROM penilaian p
    JOIN dosen d ON p.dosen_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE p.ujian_id = ?
");
$stmt->execute([$ujian_id]);
$penilaian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average scores if available
$average_score = null;
if (!empty($penilaian)) {
    $total = 0;
    foreach ($penilaian as $nilai) {
        $total += $nilai['nilai_total'];
    }
    $average_score = $total / count($penilaian);
}

// Check if user has permission to view this ujian
$canView = false;
if (hasRole('admin')) {
    $canView = true;
} elseif (hasRole('dosen')) {
    // Check if dosen is penguji or pembimbing
    $dosen = getDosenByUserId($_SESSION['user_id']);
    if ($dosen) {
        foreach ($penguji as $p) {
            if ($p['dosen_id'] == $dosen['id']) {
                $canView = true;
                break;
            }
        }
        // Check if dosen is pembimbing
        if (!$canView && $ujian['pembimbing_nidn']) {
            $stmt = $pdo->prepare("SELECT id FROM dosen WHERE nidn = ? AND user_id = ?");
            $stmt->execute([$ujian['pembimbing_nidn'], $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $canView = true;
            }
        }
    }
} elseif (hasRole('mahasiswa')) {
    // Check if mahasiswa is the owner
    $mahasiswa = getMahasiswaByUserId($_SESSION['user_id']);
    if ($mahasiswa) {
        $stmt = $pdo->prepare("
            SELECT ta.id 
            FROM tugas_akhir ta 
            WHERE ta.mahasiswa_id = ? AND ta.id = (
                SELECT tugas_akhir_id FROM ujian WHERE id = ?
            )
        ");
        $stmt->execute([$mahasiswa['id'], $ujian_id]);
        if ($stmt->fetch()) {
            $canView = true;
        }
    }
}

if (!$canView) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistem Manajemen Tugas Akhir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.极/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center mb-6">
            <?php if (hasRole('admin')): ?>
                <a href="modules/admin/ujian.php" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Ujian
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="text-blue-500 hover:text-blue-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
            <?php endif; ?>
            <h1 class="text-2xl font-bold text-gray-800 ml-4">Detail Ujian</h1>
        </div>
        
        <!-- Ujian Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informasi Ujian</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mahasiswa</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo htmlspecialchars($ujian['mahasiswa_nama']); ?> (<?php echo htmlspecialchars($ujian['nim']); ?>)
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo htmlspecialchars($ujian['mahasiswa_email']); ?>
                    </p>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Judul Tugas Akhir</label>
                <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                    <?php echo htmlspecialchars($ujian['judul']); ?>
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Ujian</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo formatTanggal($ujian['tanggal']); ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Waktu</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo date('H:i', strtotime($ujian['waktu'])); ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Ruangan</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo htmlspecialchars($ujian['ruangan']); ?>
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pembimbing</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo $ujian['pembimbing_nama'] ? htmlspecialchars($ujian['pembimbing_nama']) . ' (' . htmlspecialchars($ujian['pembimbing_nidn']) . ')' : 'Belum ditentukan'; ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status Ujian</label>
                    <div class="px-3 py-2">
                        <?php $statusClass = getStatusBadgeClass($ujian['status']); ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                            <?php echo $ujian['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Penguji Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Daftar Penguji</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    <?php echo count($penguji); ?> Penguji
                </span>
            </div>
            
            <?php if (!empty($penguji)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIDN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Penguji</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($penguji as $p): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['nidn']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['dosen_nama']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['dosen_email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['peran']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-user-times text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Belum ada penguji yang ditentukan</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Penilaian Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Hasil Penilaian</h2>
                <?php if ($average_score !== null): ?>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                        Rata-rata: <?php echo number_format($average_score, 2); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($penilaian)): ?>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penguji</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Presentasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Makalah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($penilaian as $nilai): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($nilai['dosen_nama']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($nilai['nilai_presentasi'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($nilai['nilai_makalah'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($nilai['nilai_responsi'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?php echo number_format($nilai['nilai_total'], 2); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatTanggal($nilai['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Catatan Penguji -->
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Catatan dari Penguji:</h3>
                <div class="space-y-4">
                    <?php foreach ($penilaian as $nilai): ?>
                        <?php if (!empty($nilai['catatan'])): ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($nilai['dosen_nama']); ?></span>
                                </div>
                                <p class="text-gray-600"><?php echo htmlspecialchars($nilai['catatan']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">Belum ada penilaian yang dimasukkan</p>
                    <?php if (hasRole('dosen')): ?>
                        <p class="text-gray-400 text-sm mt-2">Anda dapat memasukkan penilaian melalui menu Penilaian</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <?php if (hasRole('admin')): ?>
            <div class="mt-6 flex justify-end space-x-4">
                <a href="ujian.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition">
                    Kembali
                </a>
                <a href="edit_ujian.php?id=<?php echo $ujian_id; ?>" class="bg-yellow-500 text-white px-6 py-2 rounded-md hover:bg-yellow-600 transition">
                    <i class="fas fa-edit mr-2"></i>Edit Ujian
                </a>
                <?php if ($ujian['status'] === 'Terjadwal'): ?>
                    <form method="POST" action="update_ujian_status.php" class="inline">
                        <input type="hidden" name="ujian_id" value="<?php echo $ujian_id; ?>">
                        <input type="hidden" name="status" value="Selesai">
                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition">
                            <i class="fas fa-check mr-2"></i>Tandai Selesai
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Print functionality
        function printUjianDetail() {
            window.print();
        }
    </script>
</body>
</html>
