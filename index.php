<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Tugas Akhir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-pattern {
            background-color: #3498db;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-2">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="text-xl font-bold text-blue-600">Sistem TA</span>
                </div>
                <div>
                    <a href="login.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-pattern py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Sistem Manajemen Tugas Akhir</h1>
                <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                    Platform terintegrasi untuk mengelola tugas akhir mahasiswa dengan fitur lengkap untuk admin, dosen, dan mahasiswa.
                </p>
                <a href="login.php" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-200 inline-flex items-center">
                    <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Fitur Utama Sistem</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Manajemen Mahasiswa</h3>
                    <p class="text-gray-600">
                        Kelola data mahasiswa, status TA, dan progress penyelesaian tugas akhir secara terpusat.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Manajemen Dosen</h3>
                    <p class="text-gray-600">
                        Kelola data dosen pembimbing dan penguji, serta pembagian tugas bimbingan.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-clipboard-list text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Pengajuan Judul</h3>
                    <p class="text-gray-600">
                        Proses pengajuan judul TA yang terstruktur dengan review oleh dosen pembimbing.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Jadwal Bimbingan</h3>
                    <p class="text-gray-600">
                        Sistem penjadwalan bimbingan antara mahasiswa dan dosen pembimbing.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-graduation-cap text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Ujian TA</h3>
                    <p class="text-gray-600">
                        Pengelolaan jadwal ujian, penentuan penguji, dan proses penilaian.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="feature-card bg-gray-50 rounded-lg p-6 shadow-md transition duration-300">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Monitoring Progress</h3>
                    <p class="text-gray-600">
                        Pantau progress penyelesaian TA mahasiswa secara real-time.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Role Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Untuk Semua Peran</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Admin -->
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-center text-gray-800 mb-4">Administrator</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Manajemen data mahasiswa</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Manajemen data dosen</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Monitoring progress TA</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Pengaturan jadwal ujian</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Dosen -->
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-chalkboard-teacher text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-center text-gray-800 mb-4">Dosen</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Review pengajuan judul TA</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Manajemen jadwal bimbingan</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Pemberian komentar bimbingan</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Penilaian ujian TA</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Mahasiswa -->
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-user-graduate text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-center text-gray-800 mb-4">Mahasiswa</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Pengajuan judul TA</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Pengelolaan jadwal bimbingan</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Upload proposal dan laporan</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Melibat jadwal ujian</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold mb-2">500+</div>
                    <div class="text-blue-100">Mahasiswa</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">50+</div>
                    <div class="text-blue-100">Dosen</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">300+</div>
                    <div class="text-blue-100">Tugas Akhir</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">95%</div>
                    <div class="text-blue-100">Kepuasan Pengguna</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gray-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Siap Mengelola Tugas Akhir dengan Lebih Baik?</h2>
            <p class="text-gray-300 mb-8 max-w-2xl mx-auto">
                Bergabung dengan sistem manajemen tugas akhir terintegrasi untuk memudahkan proses bimbingan, pengajuan, dan ujian.
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Sekarang
                </a>
                <a href="#demo" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-eye mr-2"></i>Lihat Demo
                </a>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Demo Sistem</h2>
            
            <div class="bg-gray-800 rounded-lg p-6 mb-8">
                <div class="bg-gray-900 rounded-t-lg p-3 flex items-center">
                    <div class="flex space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="flex-1 text-center text-gray-400 text-sm">
                        sistem-ta.demo
                    </div>
                </div>
                <div class="bg-gray-700 p-4 rounded-b-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-gray-600 p-4 rounded">
                            <h3 class="text-white font-semibold mb-2">Admin</h3>
                            <p class="text-gray-300 text-sm">Username: <span class="font-mono">admin</span></p>
                            <p class="text-gray-300 text-sm">Password: <span class="font-mono">password</span></p>
                        </div>
                        <div class="bg-gray-600 p-4 rounded">
                            <h3 class="text-white font-semibold mb-2">Dosen</h3>
                            <p class="text-gray-300 text-sm">Username: <span class="font-mono">dosen</span></p>
                            <p class="text-gray-300 text-sm">Password: <span class="font-mono">password</span></p>
                        </div>
                        <div class="bg-gray-600 p-4 rounded">
                            <h3 class="text-white font-semibold mb-2">Mahasiswa</h3>
                            <p class="text-gray-300 text-sm">Username: <span class="font-mono">mahasiswa</span></p>
                            <p class="text-gray-300 text-sm">Password: <span class="font-mono">password</span></p>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition duration-200 inline-block">
                            <i class="fas fa-play-circle mr-2"></i>Coba Demo
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Fitur Demo</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Login dengan berbagai peran (Admin, Dosen, Mahasiswa)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Manajemen data mahasiswa dan dosen (Admin)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Pengajuan judul TA (Mahasiswa)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Review pengajuan (Dosen)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Manajemen jadwal bimbingan</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Pengaturan jadwal ujian (Admin)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Penilaian ujian (Dosen)</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Teknologi Used</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-100 p-4 rounded-lg flex items-center">
                            <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fab fa-php"></i>
                            </div>
                            <span class="font-semibold">PHP</span>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-lg flex items-center">
                            <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-database"></i>
                            </div>
                            <span class="font-semibold">MySQL</span>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-lg flex items-center">
                            <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fab fa-js"></i>
                            </div>
                            <span class="font-semibold">JavaScript</span>
                        </div>
                        <div class="bg-gray-100 p-4 rounded-lg flex items-center">
                            <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fab fa-css3-alt"></i>
                            </div>
                            <span class="font-semibold">Tailwind CSS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Pertanyaan Umum</h2>
            
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button class="faq-question w-full text-left p-4 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Bagaimana cara mengajukan judul TA?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Setelah login sebagai mahasiswa, masuk ke menu "Pengajuan" dan isi form pengajuan judul. 
                            Upload proposal dalam format PDF dan tunggu review dari dosen pembimbing.
                        </p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button class="faq-question w-full text-left p-4 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Berapa kali minimal bimbingan sebelum ujian?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Minimal 8 kali bimbingan harus diselesaikan sebelum mahasiswa dapat mendaftar untuk ujian tugas akhir.
                        </p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button class="faq-question w-full text-left p-4 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Bagaimana proses penilaian ujian?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer p极 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Setiap penguji akan memberikan nilai untuk presentasi, makalah, dan responsi. 
                            Nilai akhir merupakan rata-rata dari semua penguji.
                        </p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <button class="faq-question w-full text-left p-4 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Apakah bisa mengajukan ulang jika judul ditolak?</span>
                        <i class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    <div class="faq-answer p-4 border-t border-gray-200 hidden">
                        <p class="text-gray-600">
                            Ya, mahasiswa dapat mengajukan judul baru maksimal 3 kali dalam satu semester 
                            jika judul sebelumnya ditolak oleh dosen pembimbing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Sistem TA</h3>
                    <p class="text-gray-400">
                        Platform terintegrasi untuk manajemen tugas akhir mahasiswa dengan fitur lengkap.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition">Login</a></li>
                        <li><a href="#demo" class="text-gray-400 hover:text-white transition">Demo</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Fitur</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Peran Pengguna</h4>
                    <ul class="space-y-2">
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition">Admin</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition">Dosen</a></li>
                        <li><a href="login.php" class="text-gray-400 hover:text-white transition">Mahasiswa</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>
                            <span class="text-gray-400">admin@sistem-ta.ac.id</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone text-gray-400 mr-2"></i>
                            <span class="text-gray-400">(021) 1234-5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                            <span class="text-gray-400">Jakarta, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Sistem Manajemen Tugas Akhir. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // FAQ toggle functionality
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.nextElementSibling;
                const icon = button.querySelector('i');
                
                // Toggle answer visibility
                answer.classList.toggle('hidden');
                
                // Rotate icon
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
