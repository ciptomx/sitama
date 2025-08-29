<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireRole('admin');

$pageTitle = "Manajemen Dosen";

// Get all dosen
$dosen = getAllDosen();

// Add new dosen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_dosen'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $nidn = trim($_POST['nidn']);
    $bidang_keahlian = trim($_POST['bidang_keahlian']);
    $peran = trim($_POST['peran']);
    
    // Validate input
    $errors = [];
    
    if (empty($username) || empty($password) || empty($nama) || empty($email) || empty($nidn) || empty($bidang_keahlian)) {
        $errors[] = "Semua field harus diisi";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!preg_match('/^[0-9]{8,10}$/', $nidn)) {
        $errors[] = "NIDN harus terdiri dari 8-10 digit angka";
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username sudah digunakan";
    }
    
    // Check if NIDN already exists
    $stmt = $pdo->prepare("SELECT id FROM dosen WHERE nidn = ?");
    $stmt->execute([$nidn]);
    if ($stmt->fetch()) {
        $errors[] = "NIDN sudah digunakan";
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert into users table
            $hashedPassword = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama, email) VALUES (?, ?, 'dosen', ?, ?)");
            $stmt->execute([$username, $hashedPassword, $nama, $email]);
            $user_id = $pdo->lastInsertId();
            
            // Insert into dosen table
            $stmt = $pdo->prepare("INSERT INTO dosen (user_id, nidn, bidang_keahlian, peran) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $nidn, $bidang_keahlian, $peran]);
            
            $pdo->commit();
            
            // Refresh page to show new data
            header("Location: dosen.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Delete dosen
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if dosen has tugas akhir
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tugas_akhir WHERE dosen_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        $errors[] = "Tidak dapat menghapus dosen yang sedang membimbing tugas akhir";
    } else {
        // Get user_id from dosen
        $stmt = $pdo->prepare("SELECT user_id FROM dosen WHERE id = ?");
        $stmt->execute([$id]);
        $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dosen) {
            try {
                $pdo->beginTransaction();
                
                // Delete from dosen table
                $stmt = $pdo->prepare("DELETE FROM dosen WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete from users table
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$dosen['user_id']]);
                
                $pdo->commit();
                
                // Refresh page
                header("Location: dosen.php");
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
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Dosen</h1>
            <button onclick="toggleAddForm()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Dosen
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
        
        <!-- Add Dosen Form -->
        <div id="add-form" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tambah Dosen Baru</h2>
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
                    <label class="极 text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap</label>
                    <input type="text" id="nama极" name="nama" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus极outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nidn">NIDN</label>
                        <input type="text" id="nidn" name="nidn" required pattern="[0-9]{8,10}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="bidang_keahlian">Bidang Keahlian</label>
                        <input type="text" id="bidang_keahlian" name="bidang_keahlian" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="peran">Peran</label>
                    <select id="peran" name="peran" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Peran</option>
                        <option value="Pembimbing">Pembimbing</option>
                        <option value="Penguji">Penguji</option>
                        <option value="Keduanya">Keduanya</option>
                    </select>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="toggleAddForm()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition mr-2">
                        Batal
                    </button>
                    <button type="submit" name="add_dosen" class="bg-blue-极 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Rules Container -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <h3 class="font-bold text-blue-800">Aturan dan Constraints:</h3>
            <ul class="list-disc list-inside text-blue-700 mt-2">
                <li>NIDN harus unik dan terdiri dari 8-10 digit angka</li>
                <li>Email harus menggunakan domain kampus (@unmuhpnk.ac.id)</li>
                <li>Dosen dapat memiliki peran: Pembimbing, Penguji, atau Keduanya</li>
                <li>Dosen yang sedang membimbing tidak dapat dihapus</li>
            </ul>
        </div>
        
        <!-- Dosen Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIDN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang Keahlian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($dosen as $d): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $d['nidn']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $d['nama']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $d['email']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $d['bidang_keahlian']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                            $peranClass = '';
                            if ($d['peran'] == 'Pembimbing') $peranClass = 'bg-blue-100 text-blue-800';
                            elseif ($d['peran'] == 'Penguji') $peranClass = 'bg-purple-100 text-purple-800';
                            else $peranClass = 'bg-indigo-100 text-indigo-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $peranClass; ?>">
                                <?php echo $d['peran']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="edit_dosen.php?id=<?php echo $d['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <a href="dosen.php?delete=<?php echo $d['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
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
