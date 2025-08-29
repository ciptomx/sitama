<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('admin');

$pageTitle = "Manajemen Mahasiswa";

// Get all mahasiswa
$mahasiswa = getAllMahasiswa();

// Add new mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mahasiswa'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nim = trim($_POST['nim']);
    $prodi = trim($_POST['prodi']);
    $status = trim($_POST['status']);
    
    // Validate input
    $errors = [];
    
    if (empty($username) || empty($password) || empty($nama) || empty($email) || empty($nim) || empty($prodi)) {
        $errors[] = "Semua field harus diisi";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!preg_match('/^[0-9]{10}$/', $nim)) {
        $errors[] = "NIM harus terdiri dari 10 digit angka";
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username sudah digunakan";
    }
    
    // Check if NIM already exists
    $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    if ($stmt->fetch()) {
        $errors[] = "NIM sudah digunakan";
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert into users table
            $hashedPassword = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama, email) VALUES (?, ?, 'mahasiswa', ?, ?)");
            $stmt->execute([$username, $hashedPassword, $nama, $email]);
            $user_id = $pdo->lastInsertId();
            
            // Insert into mahasiswa table
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (user_id, nim, prodi, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $nim, $prodi, $status]);
            
            $pdo->commit();
            
            // Refresh page to show new data
            header("Location: mahasiswa.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Delete mahasiswa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if mahasiswa has tugas akhir
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tugas_akhir WHERE mahasiswa_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $errors[] = "Tidak dapat menghapus mahasiswa yang sudah memiliki tugas akhir";
    } else {
        // Get user_id from mahasiswa
        $stmt = $pdo->prepare("SELECT user_id FROM mahasiswa WHERE id = ?");
        $stmt->execute([$id]);
        $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($mahasiswa) {
            try {
                $pdo->beginTransaction();
                
                // Delete from mahasiswa table
                $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete from users table
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$mahasiswa['user_id']]);
                
                $pdo->commit();
                
                // Refresh page
                header("Location: mahasiswa.php");
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Terjadi kesalahan: " . $e->getMessage();
            }
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Mahasiswa</h1>
            <button onclick="toggleAddForm()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Mahasiswa
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
        
        <!-- Add Mahasiswa Form -->
        <div id="add-form" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Mahasiswa Baru</h2>
            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                        <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                        <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nim">NIM</label>
                        <input type="text" id="nim" name="nim" required pattern="[0-9]{10}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="prodi">Program Studi</label>
                        <select id="prodi" name="prodi" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Program Studi</option>
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Sistem Informasi">Sistem Informasi</option>
                            <option value="Teknik Komputer">Teknik Komputer</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">Status</label>
                    <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Aktif">Aktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Lulus">Lulus</option>
                    </select>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="toggleAddForm()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition mr-2">
                        Batal
                    </button>
                    <button type="submit" name="add_mahasiswa" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan dan Constraints:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>NIM harus unik dan terdiri dari 10 digit angka</li>
                <li>Email harus menggunakan domain kampus (@unmuhpnk.ac.id)</li>
                <li>Status mahasiswa hanya dapat berupa: Aktif, Cuti, atau Lulus</li>
                <li>Mahasiswa yang sudah memiliki tugas akhir tidak dapat dihapus</li>
            </ul>
        </div>
        
        <!-- Mahasiswa Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($mahasiswa as $m): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $m['nim']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $m['nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $m['email']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $m['prodi']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $statusClass = '';
                            if ($m['status'] == 'Aktif') $statusClass = 'bg-green-100 text-green-800';
                            elseif ($m['status'] == 'Cuti') $statusClass = 'bg-yellow-100 text-yellow-800';
                            else $statusClass = 'bg-blue-100 text-blue-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo $m['status']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="edit_mahasiswa.php?id=<?php echo $m['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <a href="mahasiswa.php?delete=<?php echo $m['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
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
