<?php
// admin/pengguna.php - Kelola Hak Akses Pengguna AgroIntelli
session_start();
require_once '../koneksi.php';

// Keamanan: Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

$page_aktif = 'pengguna'; // Penanda menu aktif di sidebar.php
$pesan_sukses = "";
$pesan_error = "";

// 1. PROSES UBAH ROLE PENGGUNA
if (isset($_POST['ubah_role'])) {
    $id_user = intval($_POST['id_user']);
    $role_baru = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Mencegah admin mengubah role dirinya sendiri secara tidak sengaja
    if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $id_user) {
        $pesan_error = "Anda tidak dapat mengubah role akun Anda sendiri yang sedang aktif!";
    } else {
        $query_update = "UPDATE pengguna SET role = '$role_baru' WHERE id_pengguna = $id_user";
        if (mysqli_query($koneksi, $query_update)) {
            $pesan_sukses = "Role pengguna berhasil diperbarui!";
        } else {
            $pesan_error = "Gagal memperbarui role: " . mysqli_error($koneksi);
        }
    }
}

// 2. PROSES HAPUS PENGGUNA
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);

    if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $id_hapus) {
        $pesan_error = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!";
    } else {
        $query_hapus = "DELETE FROM pengguna WHERE id_pengguna = $id_hapus";
        if (mysqli_query($koneksi, $query_hapus)) {
            $pesan_sukses = "Pengguna berhasil dihapus dari sistem.";
        } else {
            $pesan_error = "Gagal menghapus pengguna.";
        }
    }
}

// Ambil Seluruh Data Pengguna
$query_users = mysqli_query($koneksi, "SELECT * FROM pengguna ORDER BY id_pengguna DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hak Akses Pengguna - AgroIntelli</title>
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
                <!-- Sisi Kiri: Tombol Menu Garis Tiga + Logo -->
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

    <!-- INCLUDE SIDEBAR MODULAR -->
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 fade-in">
        
        <!-- Header Section -->
        <div class="glass-card p-6 rounded-3xl border-slate-800">
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class='bx bx-user-check text-emerald-400'></i> Kelola Hak Akses Pengguna
            </h1>
            <p class="text-xs text-slate-400 mt-1">Kelola peran (Role Admin/Petani) serta hak akses pengguna yang terdaftar di AgroIntelli.</p>
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

        <!-- Tabel Data Pengguna -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                            <th class="py-3.5 px-4">ID</th>
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Username</th>
                            <th class="py-3.5 px-4">Role / Hak Akses</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs">
                        <?php if ($query_users && mysqli_num_rows($query_users) > 0) {
                            while ($user = mysqli_fetch_assoc($query_users)) { ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500">#<?php echo $user['id_pengguna']; ?></td>
                                    <td class="py-4 px-4 font-semibold text-white">
                                        <?php echo htmlspecialchars($user['nama'] ?? $user['username'] ?? '-'); ?>
                                    </td>
                                    <td class="py-4 px-4 font-mono text-slate-400">
                                        <?php echo htmlspecialchars($user['username'] ?? '-'); ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php if (($user['role'] ?? 'petani') === 'admin') { ?>
                                            <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold">
                                                ADMIN
                                            </span>
                                        <?php } else { ?>
                                            <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2.5 py-1 rounded-lg text-[10px] font-mono">
                                                PETANI
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openModalEditRole(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/20 rounded-xl transition cursor-pointer" title="Ubah Role">
                                                <i class='bx bx-edit text-sm'></i>
                                            </button>
                                            <a href="?hapus=<?php echo $user['id_pengguna']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl transition" title="Hapus User">
                                                <i class='bx bx-trash text-sm'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500">Belum ada pengguna terdaftar di sistem.</td>
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

    <!-- MODAL EDIT ROLE PENGGUNA -->
    <div id="modal-role" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-card bg-slate-900 border-slate-800 rounded-3xl max-w-sm w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2"><i class='bx bx-user-voice text-blue-400'></i> Ubah Role Pengguna</h3>
                <button onclick="document.getElementById('modal-role').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer"><i class='bx bx-x text-xl'></i></button>
            </div>
            <form action="" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="id_user" id="role-id-user">
                <div>
                    <label class="block text-slate-400 mb-1">Username / Nama</label>
                    <input type="text" id="role-username" readonly class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-3 py-2 text-slate-400 font-mono cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Pilih Role / Hak Akses Baru</label>
                    <select name="role" id="role-select" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <option value="petani">Petani (User Biasa)</option>
                        <option value="admin">Administrator (Akses Penuh)</option>
                    </select>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-role').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                    <button type="submit" name="ubah_role" class="px-4 py-2 bg-emerald-500 text-slate-950 font-semibold rounded-xl hover:bg-emerald-600 transition">Simpan Akses</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT ANIMASI INTERAKTIF SIDEBAR & MODAL -->
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

        function openModalEditRole(user) {
            document.getElementById('role-id-user').value = user.id_pengguna || user.id;
            document.getElementById('role-username').value = user.username || user.nama || '';
            document.getElementById('role-select').value = user.role || 'petani';
            document.getElementById('modal-role').classList.remove('hidden');
        }
    </script>

</body>
</html>