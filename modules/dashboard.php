<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$role = $_SESSION['user_role'];
$pageTitle = "Dashboard";

// Get stats based on role
if ($role === 'admin') {
    // Admin stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $totalMahasiswa = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM dosen");
    $totalDosen = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tugas_akhir WHERE status = 'Disetujui'");
    $totalTA = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tugas_akhir WHERE status = 'Selesai'");
    $totalTASelesai = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} elseif ($role === 'dosen') {
    // Dosen stats
    $dosen_id = getDosenIdByUserId($user['id']);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas_akhir WHERE dosen_id = ? AND status = 'Diajukan'");
    $stmt->execute([$dosen_id]);
    $pengajuanMenunggu = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bimbingan WHERE status = 'Menunggu' AND tugas_akhir_id IN (SELECT id FROM tugas_akhir WHERE dosen_id = ?)");
    $stmt->execute([$dosen_id]);
    $bimbinganMenunggu = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} else {
    // Mahasiswa stats
    $mahasiswa_id = getMahasiswaIdByUserId($user['id']);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tugas_akhir WHERE mahasiswa_id = ?");
    $stmt->execute([$mahasiswa_id]);
    $totalPengajuan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bimbingan WHERE tugas_akhir_id IN (SELECT id FROM tugas_akhir WHERE mahasiswa_id = ?)");
    $stmt->execute([$mahasiswa_id]);
    $totalBimbingan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ujian WHERE tugas_akhir_id IN (SELECT id FROM tugas_akhir WHERE mahasiswa_id = ?)");
    $stmt->execute([$mahasiswa_id]);
    $totalUjian = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Helper functions
function getDosenIdByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM dosen WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

function getMahasiswaIdByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
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
    <?php include '../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php if ($role === 'admin'): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-user-graduate text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Total Mahasiswa</h2>
                            <p class="text-2xl font-bold"><?php echo $totalMahasiswa; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-chalkboard-teacher text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Total Dosen</h2>
                            <p class="text-2xl font-bold"><?php echo $totalDosen; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">TA dalam Proses</h2>
                            <p class="text-2xl font-bold"><?php echo $totalTA; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-graduation-cap text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">TA Selesai</h2>
                            <p class="text-2xl font-bold"><?php echo $totalTASelesai; ?></p>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($role === 'dosen'): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-clipboard-list text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Pengajuan Menunggu</h2>
                            <p class="text-2xl font-bold"><?php echo $pengajuanMenunggu; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Bimbingan Menunggu</h2>
                            <p class="text-2xl font-bold"><?php echo $bimbinganMenunggu; ?></p>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-clipboard-list text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Total Pengajuan</h2>
                            <p class="text-2xl font-bold"><?php echo $totalPengajuan; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Total Bimbingan</h2>
                            <p class="text-2xl font-bold"><?php echo $totalBimbingan; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-graduation-cap text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-500">Total Ujian</h2>
                            <p class="text-2xl font-bold"><?php echo $totalUjian; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Terbaru</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                            <i class="fas fa-plus"></i>
                        </div>
                        <p>Budi mengajukan judul TA</p>
                    </div>
                    <span class="text-sm text-gray-500">2 jam yang lalu</span>
                </div>
                
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                            <i class="fas fa-check"></i>
                        </div>
                        <p>Dr. Ahmad menyetujui proposal</p>
                    </div>
                    <span class="text-sm text-gray-500">5 jam yang lalu</span>
                </div>
                
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-yellow-100 text-yellow-500 mr-3">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <p>Sesi bimbingan dengan Ani</p>
                    </div>
                    <span class="text-sm text-gray-500">1 hari yang lalu</span>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <p>Jadwal ujian TA Fitri</p>
                    </div>
                    <span class="text-sm text-gray-500">2 hari yang lalu</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($role === 'admin'): ?>
                    <a href="<?php 'BASE_URL'?>/modules/admin/mahasiswa.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span>Kelola Mahasiswa</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/admin/dosen.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                        <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span>Kelola Dosen</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/admin/ujian.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 transition">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span>Jadwal Ujian</span>
                    </a>
                    
                <?php elseif ($role === 'dosen'): ?>
                    <a href="<?php 'BASE_URL'?>/modules/dosen/pengajuan.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <span>Review Pengajuan</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/dosen/bimbingan.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                        <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span>Jadwal Bimbingan</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/dosen/penilaian.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 transition">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                            <i class="fas fa-star"></i>
                        </div>
                        <span>Penilaian Ujian</span>
                    </a>
                    
                <?php else: ?>
                    <a href="<?php 'BASE_URL'?>/modules/mahasiswa/pengajuan.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-500 mr-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <span>Pengajuan Judul</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/mahasiswa/bimbingan.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition">
                        <div class="p-2 rounded-full bg-green-100 text-green-500 mr-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span>Jadwal Bimbingan</span>
                    </a>
                    
                    <a href="<?php 'BASE_URL'?>/modules/mahasiswa/ujian.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 transition">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-500 mr-3">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span>Jadwal Ujian</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>
