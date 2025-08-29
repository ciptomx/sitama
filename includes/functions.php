<?php
// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Upload file with validation
function uploadFile($file, $allowedTypes = ['pdf'], $maxSize = 2097152) {
    $targetDir = "../assets/uploads/";
    
    // Create directory if not exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check file size
    if ($file["size"] > $maxSize) {
        return ["error" => "File terlalu besar. Maksimal " . ($maxSize/1024/1024) . "MB"];
    }
    
    // Allow certain file formats
    if (!in_array($fileType, $allowedTypes)) {
        return ["error" => "Hanya file " . implode(', ', $allowedTypes) . " yang diizinkan"];
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["success" => $targetFile];
    } else {
        return ["error" => "Terjadi kesalahan saat mengupload file"];
    }
}

// Get all mahasiswa
function getAllMahasiswa() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.*, u.nama, u.email 
        FROM mahasiswa m 
        JOIN users u ON m.user_id = u.id
        ORDER BY m.nim
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all dosen
function getAllDosen() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT d.*, u.nama, u.email 
        FROM dosen d 
        JOIN users u ON d.user_id = u.id
        ORDER BY u.nama
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get tugas akhir by mahasiswa
function getTugasAkhirByMahasiswa($mahasiswa_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ta.*, d.nidn, u.nama as dosen_nama 
        FROM tugas_akhir ta 
        LEFT JOIN dosen d ON ta.dosen_id = d.id 
        LEFT JOIN users u ON d.user_id = u.id
        WHERE ta.mahasiswa_id = ?
    ");
    $stmt->execute([$mahasiswa_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get pengajuan by dosen
function getPengajuanByDosen($dosen_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ta.*, m.nim, u.nama as mahasiswa_nama 
        FROM tugas_akhir ta 
        JOIN mahasiswa m ON ta.mahasiswa_id = m.id 
        JOIN users u ON m.user_id = u.id
        WHERE ta.dosen_id = ?
        ORDER BY ta.tanggal_pengajuan DESC
    ");
    $stmt->execute([$dosen_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get mahasiswa by user_id
function getMahasiswaByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, u.nama, u.email 
        FROM mahasiswa m 
        JOIN users u ON m.user_id = u.id
        WHERE m.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get dosen by user_id
function getDosenByUserId($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT d.*, u.nama, u.email 
        FROM dosen d 
        JOIN users u ON d.user_id = u.id
        WHERE d.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all tugas akhir
function getAllTugasAkhir() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT ta.*, m.nim, u1.nama as mahasiswa_nama, d.nidn, u2.nama as dosen_nama
        FROM tugas_akhir ta
        JOIN mahasiswa m ON ta.mahasiswa_id = m.id
        JOIN users u1 ON m.user_id = u1.id
        LEFT JOIN dosen d ON ta.dosen_id = d.id
        LEFT JOIN users u2 ON d.user_id = u2.id
        ORDER BY ta.tanggal_pengajuan DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get bimbingan by tugas akhir
function getBimbinganByTugasAkhir($tugas_akhir_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT b.* 
        FROM bimbingan b
        WHERE b.tugas_akhir_id = ?
        ORDER BY b.tanggal DESC, b.waktu DESC
    ");
    $stmt->execute([$tugas_akhir_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get ujian by tugas akhir
function getUjianByTugasAkhir($tugas_akhir_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.*, GROUP_CONCAT(d.nidn) as nidn_penguji
        FROM ujian u
        LEFT JOIN penguji p ON u.id = p.ujian_id
        LEFT JOIN dosen d ON p.dosen_id = d.id
        WHERE u.tugas_akhir_id = ?
        GROUP BY u.id
        ORDER BY u.tanggal DESC
    ");
    $stmt->execute([$tugas_akhir_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Disetujui':
        case 'Dikonfirmasi':
        case 'Selesai':
        case 'Lulus':
        case 'Aktif':
            return 'bg-green-100 text-green-800';
        case 'Review':
        case 'Menunggu':
        case 'Cuti':
            return 'bg-yellow-100 text-yellow-800';
        case 'Ditolak':
        case 'Dibatalkan':
            return 'bg-red-100 text-red-800';
        case 'Terjadwal':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Format tanggal Indonesia
function formatTanggal($date) {
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($date);
    return date('d', $time) . ' ' . $months[date('n', $time) - 1] . ' ' . date('Y', $time);
}
?>
