<?php
// sidebar.php - Sidebar Khusus Modul Admin AgroIntelli
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nama_admin = $_SESSION['nama'] ?? 'Administrator';
$username_admin = $_SESSION['username'] ?? 'admin';
$active_page = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR OVERLAY -->
<div id="sidebar-overlay" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300"></div>

<!-- SIDEBAR DRAWER PANEL -->
<aside id="sidebar-drawer" class="fixed top-0 left-0 h-full w-72 sm:w-80 bg-slate-900 border-r border-slate-800 z-50 transform -translate-x-full shadow-2xl flex flex-col justify-between transition-transform duration-300 ease-in-out">
    <div>
        <!-- Header Sidebar -->
        <div class="p-6 border-b border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class='bx bx-slider-alt text-emerald-400 text-xl'></i>
                <h3 class="font-bold text-white text-base tracking-tight">Navigasi Admin</h3>
            </div>
            <button id="close-sidebar" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition cursor-pointer">
                <i class='bx bx-x text-xl'></i>
            </button>
        </div>

        <!-- Menu Navigasi Admin -->
        <div class="p-4 space-y-1">
            <p class="text-[10px] font-mono uppercase tracking-wider text-slate-500 px-3 py-2">Utama</p>
            <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'index.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <i class='bx bx-grid-alt text-lg <?php echo ($active_page == 'index.php') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400'; ?>'></i> Dashboard Control Center
            </a>

            <p class="text-[10px] font-mono uppercase tracking-wider text-slate-500 px-3 py-2 mt-4">Modul Kelola</p>
            <a href="pengguna.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'pengguna.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <div class="flex items-center gap-3">
                    <i class='bx bx-user-check text-lg text-slate-400 group-hover:text-emerald-400'></i> Hak Akses Pengguna
                </div>
            </a>

            <a href="pupuk.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'pupuk.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <div class="flex items-center gap-3">
                    <i class='bx bx-leaf text-lg text-slate-400 group-hover:text-emerald-400'></i> Kelola Data Pupuk
                </div>
            </a>

            <a href="berita.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'berita.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <div class="flex items-center gap-3">
                    <i class='bx bx-news text-lg text-slate-400 group-hover:text-emerald-400'></i> Info &amp; Edukasi Petani
                </div>
            </a>

            <a href="pengaturan.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'pengaturan.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <div class="flex items-center gap-3">
                    <i class='bx bx-cog text-lg text-slate-400 group-hover:text-emerald-400'></i> Pengaturan Rule AI
                </div>
            </a>

            <a href="monitoring.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'monitoring.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <div class="flex items-center gap-3">
                    <i class='bx bx-chip text-lg text-slate-400 group-hover:text-emerald-400'></i> Monitoring Perangkat IoT
                </div>
            </a>

            <p class="text-[10px] font-mono uppercase tracking-wider text-slate-500 px-3 py-2 mt-4">Informasi</p>
            <a href="tentang.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium <?php echo ($active_page == 'tentang.php') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-emerald-400'; ?> transition group">
                <i class='bx bx-info-circle text-lg text-slate-400 group-hover:text-emerald-400'></i> Detail &amp; Logika Sistem
            </a>
        </div>
    </div>

    <!-- User Profile & Logout -->
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
        <div class="mb-3 px-3">
            <p class="text-xs font-semibold text-white"><?php echo htmlspecialchars($nama_admin); ?></p>
            <p class="text-[11px] text-slate-500 font-mono truncate"><?php echo htmlspecialchars($username_admin); ?></p>
        </div>
        <a href="../logout.php" class="w-full text-center text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 py-2.5 rounded-xl font-medium transition flex items-center justify-center gap-2">
            <i class='bx bx-log-out text-sm'></i> Keluar Akun
        </a>
    </div>
</aside>

<!-- SCRIPT TOGGLE SIDEBAR -->
<script>
    (function() {
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar-drawer');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar(open) {
            if (!sidebar || !overlay) return;
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
    })();
</script>