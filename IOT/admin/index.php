<?php
// admin/index.php - Dashboard Administrator AgroIntelli dengan Left Sidebar Dynamic
session_start();
require_once '../koneksi.php';

// Keamanan: Cek apakah user sudah login dan role-nya adalah 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

// Ambil Ringkasan Statistik Sistem dari Database
$total_pengguna = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengguna"))['total'] ?? 0;
$total_lahan    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM lahan"))['total'] ?? 0;
$total_sensor   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sensor_data"))['total'] ?? 0;
$total_pupuk    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pupuk"))['total'] ?? 0;

// Ambil Aktivitas Pengguna Terbaru
$query_pengguna_baru = mysqli_query($koneksi, "SELECT nama, username, role, tanggal_daftar FROM pengguna ORDER BY id_pengguna DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgroIntelli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); }

        /* Animasi Transisi Element */
        .fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-[#050b0a] text-slate-100 min-h-screen relative overflow-x-hidden">

    <!-- Navbar Minimalis Admin -->
    <nav class="border-b border-slate-800 bg-slate-900/80 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Sisi Kiri: Tombol Menu Garis Tiga + Logo -->
                <div class="flex items-center gap-3">
                    <!-- Tombol Garis Tiga (Hamburger Button) di Kiri -->
                    <button id="open-sidebar" class="w-10 h-10 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 rounded-xl flex items-center justify-center transition shadow-lg hover:text-emerald-400 focus:outline-none cursor-pointer">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>

                    <!-- Logo & Title -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400 font-bold">
                            <i class='bx bx-brain text-xl'></i>
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-tight text-white">AgroIntelli</span>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded font-mono ml-2">ADMIN</span>
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Info Profil -->
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400 hidden sm:inline">Halo, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></strong></span>
                    <a href="../logout.php" class="text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-3 py-2 rounded-xl transition flex items-center gap-1">
                        <i class='bx bx-log-out text-sm'></i> <span class="hidden sm:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- INCLUDE KOMPONEN SIDEBAR DARI REUSABLE FILE -->
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 fade-in">
        
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 glass-card p-6 rounded-3xl border-slate-800">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Pusat Kontrol &amp; Pengawasan Sistem</h1>
                <p class="text-xs text-slate-400 mt-1">Kelola instrumen IoT, intelligence AI, serta data pengguna perkebunan kakao digital.</p>
            </div>
            <div class="flex gap-2">
                <a href="pengguna.php" class="text-xs bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 px-4 py-2.5 rounded-xl border border-emerald-500/20 transition flex items-center gap-2">
                    <i class='bx bx-user-check text-sm'></i> Kelola Hak Akses
                </a>
                <a href="../tentang.php" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2.5 rounded-xl border border-slate-700 transition flex items-center gap-2">
                    <i class='bx bx-info-circle text-emerald-400'></i> Detail Sistem
                </a>
            </div>
        </div>

        <!-- Grid Kartu Statistik ringkasan DB -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Pengguna -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Total Pengguna</p>
                        <h3 class="text-2xl font-bold text-white mt-1"><?php echo $total_pengguna; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                        <i class='bx bx-user text-2xl'></i>
                    </div>
                </div>
                <div class="mt-4 text-[11px] text-slate-400 flex items-center gap-1">
                    <span class="text-emerald-400 font-medium">Terdaftar</span> dalam sistem
                </div>
            </div>

            <!-- Total Lahan -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Lahan Terdaftar</p>
                        <h3 class="text-2xl font-bold text-white mt-1"><?php echo $total_lahan; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-center justify-center text-blue-400">
                        <i class='bx bx-map-pin text-2xl'></i>
                    </div>
                </div>
                <div class="mt-4 text-[11px] text-slate-400 flex items-center gap-1">
                    <span class="text-blue-400 font-medium">Plot Kakao</span> aktif dipantau
                </div>
            </div>

            <!-- Record Sensor IoT -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Log Telemetri IoT</p>
                        <h3 class="text-2xl font-bold text-white mt-1"><?php echo $total_sensor; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-center text-amber-400">
                        <i class='bx bx-chip text-2xl'></i>
                    </div>
                </div>
                <div class="mt-4 text-[11px] text-slate-400 flex items-center gap-1">
                    <span class="text-amber-400 font-medium">ESP8266</span> data terkirim
                </div>
            </div>

            <!-- Database Pupuk -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-mono uppercase tracking-wider text-slate-400">Database Pupuk</p>
                        <h3 class="text-2xl font-bold text-white mt-1"><?php echo $total_pupuk; ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/10 border border-purple-500/20 rounded-xl flex items-center justify-center text-purple-400">
                        <i class='bx bx-leaf text-2xl'></i>
                    </div>
                </div>
                <div class="mt-4 text-[11px] text-slate-400 flex items-center gap-1">
                    <span class="text-purple-400 font-medium">Formula AI</span> rekomendasi
                </div>
            </div>
        </div>

        <!-- Section 2 Kolom: Pengguna Terbaru & Ringkasan Alur Kerja AgroIntelli -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Pengguna Terakhir (2 Kolom) -->
            <div class="lg:col-span-2 glass-card p-6 rounded-3xl border-slate-800">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class='bx bx-group text-emerald-400'></i> Pengguna Terbaru Terdaftar
                    </h2>
                    <a href="pengguna.php" class="text-xs text-emerald-400 hover:underline">Lihat Semua &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                                <th class="py-3 px-4">Nama</th>
                                <th class="py-3 px-4">Username / Email</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4 text-right">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-xs">
                            <?php if (mysqli_num_rows($query_pengguna_baru) > 0) {
                                while ($row = mysqli_fetch_assoc($query_pengguna_baru)) { ?>
                                    <tr class="hover:bg-slate-800/30 transition">
                                        <td class="py-3 px-4 font-semibold text-white"><?php echo htmlspecialchars($row['nama']); ?></td>
                                        <td class="py-3 px-4 text-slate-400 font-mono"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="py-3 px-4">
                                            <?php if($row['role'] == 'admin') { ?>
                                                <span class="bg-purple-500/10 text-purple-400 border border-purple-500/20 px-2 py-0.5 rounded text-[10px] font-mono">admin</span>
                                            <?php } else { ?>
                                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-[10px] font-mono">operator</span>
                                            <?php } ?>
                                        </td>
                                        <td class="py-3 px-4 text-right text-slate-400 font-mono"><?php echo date('d M Y', strtotime($row['tanggal_daftar'])); ?></td>
                                    </tr>
                            <?php } } else { ?>
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-500">Belum ada data pengguna.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Arsitektur Sistem (1 Kolom) -->
            <div class="glass-card p-6 rounded-3xl border-slate-800 space-y-6">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class='bx bx-line-chart text-emerald-400'></i> Status Arsitektur AgroIntelli
                </h2>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Sistem bekerja dengan memadukan modul hardware IoT (ESP8266) dan model AI berbasis rule-based / scoring untuk mendeteksi nutrisi tanah tanaman kakao.
                </p>

                <div class="space-y-4 text-xs">
                    <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 flex items-start gap-3">
                        <div class="w-7 h-7 bg-emerald-500/10 text-emerald-400 rounded-lg flex items-center justify-center flex-shrink-0 font-bold">1</div>
                        <div>
                            <strong class="text-white block">Node Sensor ESP8266</strong>
                            <span class="text-slate-400">Pengiriman interval data pH, kelembapan, &amp; suhu tanah secara otomatis.</span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 flex items-start gap-3">
                        <div class="w-7 h-7 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center flex-shrink-0 font-bold">2</div>
                        <div>
                            <strong class="text-white block">Pemrosesan AI &amp; Aturan Pakar</strong>
                            <span class="text-slate-400">Pemberian bobot/poin pada parameter kritis untuk estimasi kebutuhan pupuk.</span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 flex items-start gap-3">
                        <div class="w-7 h-7 bg-amber-500/10 text-amber-400 rounded-lg flex items-center justify-center flex-shrink-0 font-bold">3</div>
                        <div>
                            <strong class="text-white block">Output Rekomendasi Presisi</strong>
                            <span class="text-slate-400">Petani menerima takaran dosis &amp; jadwal pemupukan via dashboard.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <footer class="text-center py-6 border-t border-slate-800/80 text-xs text-slate-500">
            AGROINTELLI &copy; 2026 | Smart Precision Farming System - Kakao Lampung
        </footer>

    </div>

</body>
</html>