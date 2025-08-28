Berikut adalah usulan struktur direktori dan file untuk proyek SiTaMa:  
sitama/ 
├── app/ 
│   ├── Controllers/ 
│   │   ├── Auth/ 
│   │   │   ├── LoginController.php       // Mengelola proses login 
│   │   │   └── RegisterController.php    // Mengelola proses registrasi mahasiswa 
│   │   │ 
│   │   ├── Admin/ 
│   │   │   ├── DashboardController.php   // Logika untuk dashboard admin 
│   │   │   ├── UserController.php        
// CRUD (Create, Read, Update, Delete) data Dosen & 
Mahasiswa 
│   │   │   ├── AnnouncementController.php// Mengelola pengumuman 
│   │   │   ├── SikartikController.php    // Mengelola periode KP, verifikasi, token 
│   │   │   └── SimtaController.php       // Mengelola rubrik, verifikasi berkas, penjadwalan 
│   │   │ 
│   │   ├── Dosen/ 
│   │   │   ├── DashboardController.php   // Logika untuk dashboard dosen 
│   │   │   ├── SikartikController.php    // Review proposal & laporan KP, input nilai 
│   │   │   └── SimtaController.php       // Bimbingan, penilaian seminar & sidang 
│   │   │ 
│   │   └── Mahasiswa/ 
│   │       ├── DashboardController.php   // Logika untuk dashboard mahasiswa 
│   │       ├── SikartikController.php    // Unggah proposal & laporan KP, lihat nilai 
│   │       └── SimtaController.php       // Unggah proposal & skripsi, log bimbingan, lihat jadwal 
│   │ 
│   ├── Models/ 
│   │   ├── User.php                    
│   │   ├── Role.php                    
│   │   ├── Announcement.php            
│   │   ├── ProposalKp.php              
│   │   ├── ReportKp.php                
// Model untuk tabel users (Admin, Dosen, Mahasiswa) 
// Model untuk peran pengguna 
// Model untuk tabel pengumuman 
// Model untuk data Kerja Praktik 
// Model untuk laporan Kerja Praktik 
│   │   ├── AssessmentTokenKp.php       // Model untuk token penilaian pembimbing lapangan 
│   │   ├── ProposalTa.php              
// Model untuk data Tugas Akhir 
│   │   ├── GuidanceLog.php             
│   │   ├── Schedule.php                
│   │   └── Grade.php                   
│   │ 
│   └── Helpers/ 
│       └── AuthHelper.php              
│ 
// Model untuk log bimbingan skripsi 
// Model untuk jadwal seminar & sidang 
// Model untuk menyimpan nilai-nilai 
// Fungsi bantuan terkait otentikasi & otorisasi 
├── config/ 
│   ├── app.php                         // Konfigurasi utama aplikasi 
│   └── database.php                    // Konfigurasi koneksi database 
│ 
├── public/ 
│   ├── css/ 
│   │   └── style.css                   // File CSS utama 
│   ├── js/ 
│   │   └── app.js                      // File JavaScript utama 
│   ├── images/                         // Aset gambar 
│   └── index.php                       // Titik masuk (entry point) tunggal untuk semua request 
│ 
├── routes/ 
│   ├── web.php                         // Mendefinisikan semua rute aplikasi 
│   └── api.php                         // Rute untuk API (jika ada) 
│ 
└── resources/ 
    └── views/ 
        ├── layouts/ 
        │   ├── app.php                 // Template layout utama (header, footer, sidebar) 
        │   └── auth.php                // Template layout untuk halaman login/register 
        │ 
        ├── auth/ 
        │   ├── login.php 
        │   └── register.php 
        │ 
        ├── partials/ 
        │   ├── header.php 
        │   ├── sidebar_admin.php 
        │   ├── sidebar_dosen.php 
        │   └── sidebar_mahasiswa.php 
        │ 
        ├── dashboard_admin.php 
        ├── dashboard_dosen.php 
        ├── dashboard_mahasiswa.php 
        │ 
        ├── sikap/ 
        │   ├── admin/ 
        │   │   ├── periods.php         // Halaman kelola periode KP 
        │   │   └── students.php        // Halaman daftar mahasiswa KP & penilaian 
        │   ├── dosen/ 
        │   │   └── guidance.php        // Halaman bimbingan KP 
        │   └── mahasiswa/ 
        │       ├── proposal.php        // Form unggah proposal KP 
        │       ├── report.php          // Form unggah laporan KP 
        │       └── grades.php          // Halaman lihat nilai KP 
        │ 
        └── sitama/ 
            ├── admin/ 
            │   ├── verification.php    // Halaman verifikasi berkas TA 
            │   └── scheduling.php      // Halaman penjadwalan seminar/sidang 
            ├── dosen/ 
            │   ├── guidance.php        // Halaman bimbingan skripsi 
            │   └── assessment.php      // Form penilaian seminar/sidang 
            └── mahasiswa/ 
                ├── proposal.php        // Form unggah proposal TA 
                ├── guidance_log.php    // Halaman log bimbingan 
                ├── schedule.php        // Halaman lihat jadwal 
                └── final_report.php    // Form unggah skripsi final 
 
Penjelasan Struktur 
● app/: Ini adalah inti dari aplikasi Anda. 
○ Controllers: Mengatur alur data. Setiap peran (Admin, Dosen, Mahasiswa) memiliki 
direktorinya sendiri untuk menjaga agar logika tetap terpisah dan rapi. 
○ Models: Merepresentasikan tabel di database Anda. Setiap file model berinteraksi 
dengan satu tabel spesifik. 
● public/: Satu-satunya direktori yang dapat diakses langsung dari browser. index.php 
akan menangani semua permintaan dan mengarahkannya ke router. 
● routes/: Mendefinisikan URL aplikasi Anda dan menghubungkannya ke metode di 
Controller. 
● resources/views/: Berisi semua file HTML (atau template PHP). Strukturnya 
mencerminkan modul (sitama, sikap) dan peran pengguna untuk navigasi yang mudah. 
Penggunaan layouts dan partials membantu menghindari duplikasi kode pada tampilan.
