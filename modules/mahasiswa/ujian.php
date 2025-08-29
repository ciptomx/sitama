<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('mahasiswa');

$pageTitle = "Jadwal Ujian";

// Get current mahasiswa
$mahasiswa = getMahasiswaByUserId($_SESSION['user_id']);
$mahasiswa_id = $mahasiswa['id'];

// Get tugas akhir for this mahasiswa
$tugas_akhir = getTugasAkhirByMahasiswa($mahasiswa_id);

// Get ujian for this mahasiswa
$ujian = [];
if ($tugas_akhir) {
    $ujian = getUjianByTugasAkhir($tugas_akhir['id']);
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Jadwal Ujian</h1>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan Ujian:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>Ujian hanya dapat dijadwalkan untuk mahasiswa yang telah menyelesaikan semua bimbingan wajib</li>
                <li>Harus memiliki minimal 2 penguji selain pembimbing</li>
                <li>Nilai akhir merupakan rata-rata dari semua penguji</li>
                <li>Ujian ulang hanya dapat diambil maksimal 2 kali</li>
            </ul>
        </div>
        
        <!-- Status Tugas Akhir -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Status Tugas Akhir</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                    <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        <?php echo $tugas_akhir ? $tugas_akhir['judul'] : 'Belum diajukan'; ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <div class="px-3 py-2">
                        <?php if ($tugas_akhir): ?>
                            <?php $statusClass = getStatusBadgeClass($tugas_akhir['status']); ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semib极 rounded-full <?php echo $statusClass; ?>">
                                <?php echo $tugas_akhir['status']; ?>
                            </span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Belum Diajukan
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($tugas_akhir): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Pembimbing</label>
                        <p class="px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            <?php echo $tugas_akhir['dosen_nama'] ? $tugas_akhir['dosen_nama'] . ' (' . $tugas_akhir['nidn'] . ')' : 'Belum dipilih'; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pengajuan</label>
                        <p class="px-3 py-2">
                            <?php echo $tugas_akhir['tanggal_pengajuan'] ? formatTanggal($tugas_akhir['tanggal_pengajuan']) : '-'; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Daftar Ujian -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Daftar Ujian</h2>
            
            <?php if (!empty($ujian)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal/Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penguji</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($ujian as $u): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo formatTanggal($u['tanggal']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($u['waktu'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $u['ruangan']; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500">
                                        <?php 
                                        if ($u['nidn_penguji']) {
                                            $penguji = explode(',', $u['nidn_penguji']);
                                            echo count($penguji) . ' penguji';
                                        } else {
                                            echo 'Belum ditentukan';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php $statusClass = getStatusBadgeClass($u['status']); ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo $u['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-medium text-gray-500">Belum ada jadwal ujian</h3>
                    <p class="text-gray-400">
                        <?php if ($tugas_akhir && $tugas_akhir['status'] === 'Disetujui'): ?>
                            Admin akan menjadwalkan ujian setelah Anda menyelesaikan semua bimbingan wajib.
                        <?php elseif ($tugas_akhir): ?>
                            Judul TA Anda harus disetujui terlebih dahulu sebelum ujian dapat dijadwalkan.
                        <?php else: ?>
                            Anda harus mengajukan judul TA terlebih dahulu.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Informasi Penting -->
        <?php if ($tugas_akhir && $tugas_akhir['status'] === 'Disetujui'): ?>
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Informasi Penting</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Pastikan Anda telah menyelesaikan minimal 8x bimbingan sebelum ujian. Hubungi admin jika Anda merasa telah memenuhi syarat tetapi belum dijadwalkan ujian.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
