<?php
// admin/monitoring.php - Dashboard Monitoring Petani, Lahan, & Telemetri Sensor
session_start();
require_once '../koneksi.php';

// Keamanan: Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

// 1. Query Total Ringkasan (User, Lahan, Sensor)
$q_total_user = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna WHERE role = 'operator'");
$total_user = mysqli_fetch_assoc($q_total_user)['total'] ?? 0;

$q_total_lahan = mysqli_query($koneksi, "SELECT COUNT(*) AS total, SUM(jumlah_sensor) AS total_sensor FROM lahan");
$data_lahan_stat = mysqli_fetch_assoc($q_total_lahan);
$total_lahan = $data_lahan_stat['total'] ?? 0;
$total_sensor = $data_lahan_stat['total_sensor'] ?? 0;

// 2. Query Utama: Menampilkan Daftar Petani / User, Lahan, Jumlah Sensor & Keadaan Lahan
$query_monitoring = mysqli_query($koneksi, "
    SELECT 
        p.nama AS nama_petani,
        p.username,
        l.id AS id_lahan,
        l.nama_lahan,
        l.lokasi,
        l.luas_lahan,
        l.jumlah_sensor,
        l.rekomendasi_sensor,
        l.kondisi
    FROM lahan l
    JOIN pengguna p ON l.id_pengguna = p.id_pengguna
    ORDER BY l.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Lahan & Sensor Petani - AgroIntelli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px); }

        .fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-[#050b0a] text-slate-100 min-h-screen relative overflow-x-hidden">

    <!-- Navbar Top Minimalis -->
    <nav class="border-b border-slate-800 bg-slate-900/80 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <button id="open-sidebar" class="w-10 h-10 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 rounded-xl flex items-center justify-center transition shadow-lg hover:text-emerald-400 focus:outline-none cursor-pointer">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>

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

                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400 hidden sm:inline">Halo, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?></strong></span>
                    <a href="../logout.php" class="text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-3 py-2 rounded-xl transition flex items-center gap-1">
                        <i class='bx bx-log-out text-sm'></i> <span class="hidden sm:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CALL MODULAR SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 fade-in">
        
        <!-- Header Page -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <i class='bx bx-chip text-emerald-400'></i> Monitoring Petani &amp; Perangkat Lahan
                </h1>
                <p class="text-xs text-slate-400 mt-1">Sentral pemantauan seluruh akun petani/operator, pemetaan lahan kakao, serta sebaran sensor IoT.</p>
            </div>
            <div class="flex items-center gap-2 font-mono text-xs">
                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Sistem Aktif
                </span>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Card Total Petani/User -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-mono uppercase text-slate-400">Total Petani / User</p>
                    <h3 class="text-3xl font-bold text-white font-mono mt-1"><?php echo $total_user; ?></h3>
                    <p class="text-[10px] text-slate-500 mt-1">Petani/Operator terdaftar</p>
                </div>
                <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center justify-center text-emerald-400 text-2xl">
                    <i class='bx bx-group'></i>
                </div>
            </div>

            <!-- Card Total Lahan -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-mono uppercase text-slate-400">Total Lahan Kakao</p>
                    <h3 class="text-3xl font-bold text-white font-mono mt-1"><?php echo $total_lahan; ?></h3>
                    <p class="text-[10px] text-slate-500 mt-1">Area perkebunan aktif</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-center justify-center text-blue-400 text-2xl">
                    <i class='bx bx-map-alt'></i>
                </div>
            </div>

            <!-- Card Total Sensor -->
            <div class="glass-card p-5 rounded-2xl border-slate-800 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-mono uppercase text-slate-400">Total Perangkat Sensor</p>
                    <h3 class="text-3xl font-bold text-emerald-400 font-mono mt-1"><?php echo $total_sensor; ?></h3>
                    <p class="text-[10px] text-slate-500 mt-1">Node terpasang di lapangan</p>
                </div>
                <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center text-amber-400 text-2xl">
                    <i class='bx bx-wifi text-2xl'></i>
                </div>
            </div>
        </div>

        <!-- Monitoring Table -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 overflow-hidden space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <h3 class="font-bold text-white text-sm flex items-center gap-2">
                    <i class='bx bx-list-ul text-emerald-400'></i> Daftar Petani, Lahan &amp; Keadaan Perangkat Sensor
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                            <th class="py-3.5 px-4">Nama Petani / User</th>
                            <th class="py-3.5 px-4">Nama Lahan</th>
                            <th class="py-3.5 px-4">Lokasi &amp; Luas</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Sensor</th>
                            <th class="py-3.5 px-4 text-center">Rekomendasi Sensor</th>
                            <th class="py-3.5 px-4 text-center">Keadaan Lahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs font-mono">
                        <?php if ($query_monitoring && mysqli_num_rows($query_monitoring) > 0) {
                            while ($row = mysqli_fetch_assoc($query_monitoring)) { ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-4 px-4 font-sans">
                                        <div class="font-semibold text-white"><?php echo htmlspecialchars($row['nama_petani']); ?></div>
                                        <div class="text-[11px] text-slate-500 font-mono"><?php echo htmlspecialchars($row['username']); ?></div>
                                    </td>
                                    <td class="py-4 px-4 font-sans">
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
                                            <?php echo htmlspecialchars($row['nama_lahan']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 font-sans text-slate-300">
                                        <div><?php echo htmlspecialchars($row['lokasi'] ?? '-'); ?></div>
                                        <div class="text-[11px] text-slate-500 font-mono"><?php echo $row['luas_lahan'] ? $row['luas_lahan'] . ' m²' : '-'; ?></div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="px-3 py-1 rounded-xl bg-slate-800 text-slate-200 border border-slate-700 font-bold">
                                            <?php echo intval($row['jumlah_sensor']); ?> Unit
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center text-slate-400">
                                        <?php echo intval($row['rekomendasi_sensor']); ?> Unit
                                    </td>
                                    <td class="py-4 px-4 text-center font-sans">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 capitalize">
                                            <?php echo htmlspecialchars($row['kondisi'] ?? 'Normal'); ?>
                                        </span>
                                    </td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 font-sans">Belum ada data petani atau lahan yang terdaftar.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center py-6 border-t border-slate-800/80 text-xs text-slate-500">
            AGROINTELLI &copy; 2026 | Smart Precision Farming System - Kakao Lampung
        </footer>
    </div>

</body>
</html>