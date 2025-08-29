<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireRole('admin');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid";
    header('Location: ujian.php');
    exit();
}

// Check if required parameters are set
if (!isset($_POST['ujian_id']) || empty($_POST['ujian_id']) || !isset($_POST['status'])) {
    $_SESSION['error'] = "Parameter yang diperlukan tidak ditemukan";
    header('Location: ujian.php');
    exit();
}

$ujian_id = filter_var($_POST['ujian_id'], FILTER_VALIDATE_INT);
$status = trim($_POST['status']);

// Validate ujian_id
if ($ujian_id === false || $ujian_id <= 0) {
    $_SESSION['error'] = "ID ujian tidak valid";
    header('Location: ujian.php');
    exit();
}

// Validate status
$allowed_statuses = ['Terjadwal', 'Selesai', 'Dibatalkan'];
if (!in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Status tidak valid. Harus salah satu dari: " . implode(', ', $allowed_statuses);
    header('Location: ujian.php');
    exit();
}

// Get current user info for logging
$current_user_id = $_SESSION['user_id'];
$current_user_name = $_SESSION['user_name'];

// Get ujian data for validation and logging
try {
    $stmt = $pdo->prepare("
        SELECT u.*, ta.judul, ta.mahasiswa_id, m.nim, u2.nama as mahasiswa_nama, u2.email as mahasiswa_email
        FROM ujian u
        JOIN tugas_akhir ta ON u.tugas_akhir_id = ta.id
        JOIN mahasiswa m ON ta.mahasiswa_id = m.id
        JOIN users u2 ON m.user_id = u2.id
        WHERE u.id = ?
    ");
    $stmt->execute([$ujian_id]);
    $ujian = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching ujian data: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data ujian";
    header('Location: ujian.php');
    exit();
}

// Check if ujian exists
if (!$ujian) {
    $_SESSION['error'] = "Data ujian tidak ditemukan";
    header('Location: ujian.php');
    exit();
}

// Additional validation based on status change
$errors = [];

// Check if ujian has penguji when changing to Terjadwal
if ($status === 'Terjadwal') {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM penguji WHERE ujian_id = ?");
        $stmt->execute([$ujian_id]);
        $penguji_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($penguji_count < 2) {
            $errors[] = "Tidak dapat menjadwalkan ujian. Minimal harus ada 2 penguji.";
        }
    } catch (PDOException $e) {
        error_log("Error checking penguji count: " . $e->getMessage());
        $errors[] = "Terjadi kesalahan saat memeriksa data penguji";
    }
}

// Check if ujian has penilaian when changing to Selesai
if ($status === 'Selesai') {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM penilaian WHERE ujian_id = ?");
        $stmt->execute([$ujian_id]);
        $penilaian_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($penilaian_count === 0) {
            $errors[] = "Tidak dapat menandai ujian sebagai selesai. Belum ada penilaian yang dimasukkan.";
        }
        
        // Additional check: all penguji should have submitted scores
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.dosen_id) as penguji_count,
                   COUNT(DISTINCT pl.dosen_id) as dinilai_count
            FROM penguji p
            LEFT JOIN penilaian pl ON p.dosen_id = pl.dosen_id AND p.ujian_id = pl.ujian_id
            WHERE p.ujian_id = ?
        ");
        $stmt->execute([$ujian_id]);
        $penilaian_check = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($penilaian_check['penguji_count'] > $penilaian_check['dinilai_count']) {
            $errors[] = "Tidak semua penguji telah memasukkan nilai. Silakan lengkapi penilaian terlebih dahulu.";
        }
    } catch (PDOException $e) {
        error_log("Error checking penilaian count: " . $e->getMessage());
        $errors[] = "Terjadi kesalahan saat memeriksa data penilaian";
    }
}

// Check if trying to change from Selesai to other status
if ($ujian['status'] === 'Selesai' && $status !== 'Selesai') {
    $errors[] = "Tidak dapat mengubah status ujian yang sudah selesai.";
}

// If there are errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header("Location: detail_ujian.php?id=" . $ujian_id);
    exit();
}

// Update ujian status
try {
    $pdo->beginTransaction();
    
    // Get old status for logging
    $old_status = $ujian['status'];
    
    // Update ujian status
    $stmt = $pdo->prepare("UPDATE ujian SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $ujian_id]);
    
    // If changing to Selesai, also update tugas_akhir status to Selesai
    if ($status === 'Selesai') {
        $stmt = $pdo->prepare("UPDATE tugas_akhir SET status = 'Selesai', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$ujian['tugas_akhir_id']]);
        
        // Also update mahasiswa status to Lulus
        $stmt = $pdo->prepare("
            UPDATE mahasiswa m
            JOIN tugas_akhir ta ON m.id = ta.mahasiswa_id
            SET m.status = 'Lulus', m.updated_at = NOW()
            WHERE ta.id = ?
        ");
        $stmt->execute([$ujian['tugas_akhir_id']]);
        
        // Log the graduation event
        $log_message = "Mahasiswa {$ujian['mahasiswa_nama']} ({$ujian['nim']}) dinyatakan Lulus";
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$current_user_id, 'GRADUATION', $log_message]);
    }
    
    // Log the status change
    $log_message = "Status ujian diubah dari '{$old_status}' menjadi '{$status}' untuk mahasiswa {$ujian['mahasiswa_nama']} ({$ujian['nim']})";
    $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$current_user_id, 'UPDATE_UJIAN_STATUS', $log_message]);
    
    $pdo->commit();
    
    $_SESSION['success'] = "Status ujian berhasil diperbarui dari '{$old_status}' menjadi '{$status}'";
    
    // Send email notification if status changed to Selesai
    if ($status === 'Selesai') {
        sendUjianCompletionEmail($ujian['mahasiswa_email'], $ujian['mahasiswa_nama'], $ujian['judul']);
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error updating ujian status: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat memperbarui status ujian: " . $e->getMessage();
}

// Redirect back to ujian detail page
header("Location: detail_ujian.php?id=" . $ujian_id);
exit();

/**
 * Send email notification for ujian completion
 */
function sendUjianCompletionEmail($email, $name, $judul_ta) {
    // In a real application, you would implement actual email sending here
    // This is just a placeholder function
    
    $subject = "Selamat! Ujian Tugas Akhir Anda telah Selesai";
    $message = "
        <h2>Selamat {$name}!</h2>
        <p>Ujian Tugas Akhir Anda dengan judul <strong>{$judul_ta}</strong> telah dinyatakan selesai.</p>
        <p>Status kelulusan dan nilai akhir dapat dilihat di sistem Manajemen Tugas Akhir.</p>
        <br>
        <p>Terima kasih,<br>Tim Administrasi</p>
    ";
    
    // For now, just log the email content
    error_log("Would send email to: {$email}");
    error_log("Subject: {$subject}");
    error_log("Message: {$message}");
    
    return true;
}

/**
 * Create system_logs table if not exists (for logging functionality)
 * This should be in your database migration, but included here for completeness
 */
function createSystemLogsTable($pdo) {
    $sql = "
        CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        error_log("Error creating system_logs table: " . $e->getMessage());
    }
}

// Create system logs table if it doesn't exist
createSystemLogsTable($pdo);
