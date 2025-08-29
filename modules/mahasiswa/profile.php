<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$user = getCurrentUser();
$pageTitle = "Profil Pengguna";

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validate input
    $errors = [];
    
    if (empty($nama) || empty($email)) {
        $errors[] = "Nama dan email harus diisi";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak sesuai";
        }
        
        if (strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter";
        }
    }
    
    // If no errors, update data
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $hashedPassword = hashPassword($password);
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$nama, $email, $hashedPassword, $user['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
                $stmt->execute([$nama, $email, $user['id']]);
            }
            
            // Update session
            $_SESSION['user_name'] = $nama;
            
            $success = "Profil berhasil diperbarui";
            
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Profil Pengguna</h1>
        
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
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex flex-col items-center">
                        <div class="h-24 w-24 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-4xl mb-4">
                            <i class="fas fa-user"></i>
                        </div>
                        <h2 class="text-xl font-bold"><?php echo $user['nama']; ?></h2>
                        <p class="text-gray-500 capitalize"><?php echo $user['role']; ?></p>
                        <p class="text-gray-500"><?php echo $user['email']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Profil</h2>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Username</label>
                            <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                            <p class="text-gray-500 text-xs mt-1">Username tidak dapat diubah</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?php echo $user['nama']; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password Baru</label>
                            <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="update_profile" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
