<?php
// Prevent direct access
if (!defined('BASE_URL')) {
    exit('No direct script access allowed');
}
?>

<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center">
            <h1 class="text-xl font-bold text-blue-600">Sistem TA</h1>
            <nav class="ml-8 hidden md:block">
                <ul class="flex space-x-6">
                    <li><a href="<?php 'BASE_URL'?>/modules/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a></li>
                    <?php if (hasRole('admin')): ?>
                        <li><a href="<?php 'BASE_URL'?>/modules/admin/mahasiswa.php" class="text-gray-700 hover:text-blue-600 font-medium">Mahasiswa</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/admin/dosen.php" class="text-gray-700 hover:text-blue-600 font-medium">Dosen</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/admin/ujian.php" class="text-gray-700 hover:text-blue-600 font-medium">Ujian</a></li>
                    <?php elseif (hasRole('dosen')): ?>
                        <li><a href="<?php 'BASE_URL'?>/modules/dosen/pengajuan.php" class="text-gray-700 hover:text-blue-600 font-medium">Pengajuan</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/dosen/bimbingan.php" class="text-gray-700 hover:text-blue-600 font-medium">Bimbingan</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/dosen/penilaian.php" class="text-gray-700 hover:text-blue-600 font-medium">Penilaian</a></li>
                    <?php else: ?>
                        <li><a href="<?php 'BASE_URL'?>/modules/mahasiswa/pengajuan.php" class="text-gray-700 hover:text-blue-600 font-medium">Pengajuan</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/mahasiswa/bimbingan.php" class="text-gray-700 hover:text-blue-600 font-medium">Bimbingan</a></li>
                        <li><a href="<?php 'BASE_URL'?>/modules/mahasiswa/ujian.php" class="text-gray-700 hover:text-blue-600 font-medium">Ujian</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="text-right hidden md:block">
                <p class="text-sm font-medium"><?php echo $_SESSION['user_name']; ?></p>
                <p class="text-xs text-gray-500 capitalize"><?php echo $_SESSION['user_role']; ?></p>
            </div>
            <div class="relative">
                <button id="user-menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                </button>
                
                <!-- Dropdown menu -->
                <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                    <a href="<?php 'BASE_URL'?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                    <a href="<?php 'BASE_URL'?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// Toggle user dropdown
document.getElementById('user-menu-button').addEventListener('click', function() {
    document.getElementById('user-menu').classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('user-menu');
    const button = document.getElementById('user-menu-button');
    
    if (!menu.contains(event.target) && !button.contains(event.target)) {
        menu.classList.add('hidden');
    }
});
</script>
