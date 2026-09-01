<?php
// admin/pengaturan.php - Pengaturan Parameter Sistem AI AgroIntelli
session_start();
require_once '../koneksi.php';

// Keamanan: Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

$page_aktif = 'pengaturan'; // Penanda menu aktif di sidebar
$pesan_sukses = "";
$pesan_error = "";

// 1. PROSES SIMPAN / UPDATE PENGATURAN
if (isset($_POST['simpan_pengaturan'])) {
    $success_count = 0;
    
    if (isset($_POST['nilai']) && is_array($_POST['nilai'])) {
        foreach ($_POST['nilai'] as $id => $val) {
            $id_setting = intval($id);
            $nilai_baru = mysqli_real_escape_string($koneksi, $val);
            $ket_baru = mysqli_real_escape_string($koneksi, $_POST['keterangan'][$id_setting] ?? '');

            $query_update = "UPDATE pengaturan SET 
                                nilai = '$nilai_baru', 
                                keterangan = '$ket_baru' 
                             WHERE id = $id_setting";
                             
            if (mysqli_query($koneksi, $query_update)) {
                $success_count++;
            }
        }
    }

    if ($success_count > 0) {
        $pesan_sukses = "Pengaturan sistem dan parameter AI berhasil diperbarui!";
    } else {
        $pesan_error = "Tidak ada perubahan yang disimpan atau terjadi kesalahan.";
    }
}

// 2. PROSES TAMBAH PARAMETER BARU
if (isset($_POST['tambah_parameter'])) {
    $param_baru = mysqli_real_escape_string($koneksi, $_POST['parameter']);
    $nilai_baru = mysqli_real_escape_string($koneksi, $_POST['nilai_baru']);
    $ket_baru = mysqli_real_escape_string($koneksi, $_POST['keterangan_baru']);

    if (!empty($param_baru)) {
        $query_tambah = "INSERT INTO pengaturan (parameter, nilai, keterangan) VALUES ('$param_baru', '$nilai_baru', '$ket_baru')";
        if (mysqli_query($koneksi, $query_tambah)) {
            $pesan_sukses = "Parameter baru berhasil ditambahkan!";
        } else {
            $pesan_error = "Gagal menambahkan parameter: " . mysqli_error($koneksi);
        }
    }
}

// 3. PROSES HAPUS PARAMETER
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM pengaturan WHERE id = $id_hapus")) {
        $pesan_sukses = "Parameter berhasil dihapus.";
    } else {
        $pesan_error = "Gagal menghapus parameter.";
    }
}

// Ambil Seluruh Data Pengaturan dari Tabel `pengaturan`
$query_pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan System & AI - AgroIntelli</title>
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

    <!-- Navbar Minimalis Admin -->
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

    <!-- INCLUDE SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 fade-in">
        
        <!-- Header Section -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <i class='bx bx-cog text-emerald-400'></i> Pengaturan System &amp; AI
                </h1>
                <p class="text-xs text-slate-400 mt-1">Kelola konfigurasi ambang batas sensor dan parameter model AI tanaman kakao.</p>
            </div>
            <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 w-fit cursor-pointer">
                <i class='bx bx-plus-circle text-base'></i> Tambah Parameter
            </button>
        </div>

        <!-- Alert Notifikasi -->
        <?php if ($pesan_sukses != "") { ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-2xl flex items-center gap-2 font-mono fade-in">
                <i class='bx bx-check-circle text-lg'></i>
                <span><?php echo $pesan_sukses; ?></span>
            </div>
        <?php } ?>

        <?php if ($pesan_error != "") { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-4 rounded-2xl flex items-center gap-2 font-mono fade-in">
                <i class='bx bx-error-circle text-lg'></i>
                <span><?php echo $pesan_error; ?></span>
            </div>
        <?php } ?>

        <!-- Form Edit Pengaturan -->
        <form action="" method="POST" class="space-y-6">
            <div class="glass-card p-6 rounded-3xl border-slate-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                                <th class="py-3.5 px-4 w-12">ID</th>
                                <th class="py-3.5 px-4 w-1/4">Parameter</th>
                                <th class="py-3.5 px-4 w-1/4">Nilai Konfigurasi</th>
                                <th class="py-3.5 px-4">Keterangan</th>
                                <th class="py-3.5 px-4 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-xs">
                            <?php if ($query_pengaturan && mysqli_num_rows($query_pengaturan) > 0) {
                                while ($row = mysqli_fetch_assoc($query_pengaturan)) { ?>
                                    <tr class="hover:bg-slate-800/30 transition">
                                        <td class="py-4 px-4 font-mono text-slate-500">#<?php echo $row['id']; ?></td>
                                        <td class="py-4 px-4 font-bold text-emerald-400 font-mono">
                                            <?php echo htmlspecialchars($row['parameter']); ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <input type="text" name="nilai[<?php echo $row['id']; ?>]" value="<?php echo htmlspecialchars($row['nilai']); ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 font-mono">
                                        </td>
                                        <td class="py-4 px-4">
                                            <input type="text" name="keterangan[<?php echo $row['id']; ?>]" value="<?php echo htmlspecialchars($row['keterangan']); ?>" class="w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-3 py-2 text-slate-300 focus:outline-none focus:border-emerald-500">
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus parameter ini?');" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl transition inline-block" title="Hapus Parameter">
                                                <i class='bx bx-trash text-sm'></i>
                                            </a>
                                        </td>
                                    </tr>
                            <?php } } else { ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-500">Belum ada parameter di tabel `pengaturan`.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 border-t border-slate-800/80 pt-4 flex justify-end">
                    <button type="submit" name="simpan_pengaturan" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold px-6 py-2.5 rounded-xl text-xs transition flex items-center gap-2 shadow-lg cursor-pointer">
                        <i class='bx bx-save text-base'></i> Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <footer class="text-center py-6 border-t border-slate-800/80 text-xs text-slate-500">
            AGROINTELLI &copy; 2026 | Smart Precision Farming System - Kakao Lampung
        </footer>
    </div>

    <!-- MODAL TAMBAH PARAMETER BARU -->
    <div id="modal-tambah" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-card bg-slate-900 border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2"><i class='bx bx-plus-circle text-emerald-400'></i> Tambah Parameter Pengaturan</h3>
                <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer"><i class='bx bx-x text-xl'></i></button>
            </div>
            <form action="" method="POST" class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Nama Parameter (Kode)</label>
                    <input type="text" name="parameter" placeholder="contoh: batas_nitrogen" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nilai Parameter</label>
                    <input type="text" name="nilai_baru" placeholder="contoh: 40" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Keterangan</label>
                    <textarea name="keterangan_baru" rows="2" placeholder="Deskripsi atau fungsi parameter..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                    <button type="submit" name="tambah_parameter" class="px-4 py-2 bg-emerald-500 text-slate-950 font-semibold rounded-xl hover:bg-emerald-600 transition">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT ANIMASI INTERAKTIF SIDEBAR -->
    <script>
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar-drawer');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar(open) {
            if (open) {
                overlay.classList.remove('pointer-events-none', 'opacity-0');
                overlay.classList.add('opacity-100');
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
            } else {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
            }
        }

        if (openBtn) openBtn.addEventListener('click', () => toggleSidebar(true));
        if (closeBtn) closeBtn.addEventListener('click', () => toggleSidebar(false));
        if (overlay) overlay.addEventListener('click', () => toggleSidebar(false));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleSidebar(false);
        });
    </script>

</body>
</html>