<?php
// admin/pupuk.php - Manajemen Master Data Pupuk AgroIntelli
session_start();
require_once '../koneksi.php';

// Keamanan: Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

$page_aktif = 'pupuk'; // Penanda menu aktif di sidebar.php
$pesan_sukses = "";
$pesan_error = "";

// 1. PROSES TAMBAH PUPUK
if (isset($_POST['tambah_pupuk'])) {
    $nama_pupuk    = mysqli_real_escape_string($koneksi, $_POST['nama_pupuk']);
    $jenis         = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $kandungan     = mysqli_real_escape_string($koneksi, $_POST['kandungan']);
    $dosis_anjuran = mysqli_real_escape_string($koneksi, $_POST['dosis_anjuran']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $query = "INSERT INTO pupuk (nama_pupuk, jenis, kandungan, dosis_anjuran, deskripsi) 
              VALUES ('$nama_pupuk', '$jenis', '$kandungan', '$dosis_anjuran', '$deskripsi')";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Data pupuk baru berhasil ditambahkan!";
    } else {
        $pesan_error = "Gagal menambah data pupuk: " . mysqli_error($koneksi);
    }
}

// 2. PROSES EDIT PUPUK
if (isset($_POST['edit_pupuk'])) {
    $id            = intval($_POST['id']);
    $nama_pupuk    = mysqli_real_escape_string($koneksi, $_POST['nama_pupuk']);
    $jenis         = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $kandungan     = mysqli_real_escape_string($koneksi, $_POST['kandungan']);
    $dosis_anjuran = mysqli_real_escape_string($koneksi, $_POST['dosis_anjuran']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $query = "UPDATE pupuk SET 
              nama_pupuk = '$nama_pupuk', 
              jenis = '$jenis', 
              kandungan = '$kandungan', 
              dosis_anjuran = '$dosis_anjuran', 
              deskripsi = '$deskripsi' 
              WHERE id = $id";

    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Data pupuk berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui data pupuk: " . mysqli_error($koneksi);
    }
}

// 3. PROSES HAPUS PUPUK
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $query = "DELETE FROM pupuk WHERE id = $id_hapus";
    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Data pupuk berhasil dihapus.";
    } else {
        $pesan_error = "Gagal menghapus data pupuk.";
    }
}

// Ambil Seluruh Data Pupuk
$query_pupuk = mysqli_query($koneksi, "SELECT * FROM pupuk ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pupuk - AgroIntelli</title>
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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 glass-card p-6 rounded-3xl border-slate-800">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <i class='bx bx-leaf text-emerald-400'></i> Master Data Pupuk
                </h1>
                <p class="text-xs text-slate-400 mt-1">Kelola katalog pupuk, takaran dosis, dan kandungan nutrisi untuk rekomendasi sistem.</p>
            </div>
            <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="text-xs bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold px-4 py-2.5 rounded-xl transition flex items-center gap-2 cursor-pointer shadow-lg shadow-emerald-500/10">
                <i class='bx bx-plus text-base'></i> Tambah Jenis Pupuk
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

        <!-- Tabel Data Pupuk -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                            <th class="py-3.5 px-4">ID</th>
                            <th class="py-3.5 px-4">Nama Pupuk</th>
                            <th class="py-3.5 px-4">Jenis</th>
                            <th class="py-3.5 px-4">Kandungan Nutrisi</th>
                            <th class="py-3.5 px-4">Dosis Anjuran</th>
                            <th class="py-3.5 px-4">Deskripsi / Fungsi</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs">
                        <?php if ($query_pupuk && mysqli_num_rows($query_pupuk) > 0) {
                            while ($pupuk = mysqli_fetch_assoc($query_pupuk)) { ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500">#<?php echo $pupuk['id']; ?></td>
                                    <td class="py-4 px-4 font-semibold text-white"><?php echo htmlspecialchars($pupuk['nama_pupuk'] ?? '-'); ?></td>
                                    <td class="py-4 px-4">
                                        <span class="bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-1 rounded-lg text-[10px] font-mono">
                                            <?php echo htmlspecialchars($pupuk['jenis'] ?? 'Anorganik'); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 font-mono text-emerald-400 font-semibold"><?php echo htmlspecialchars($pupuk['kandungan'] ?? '-'); ?></td>
                                    <td class="py-4 px-4 font-mono text-amber-400"><?php echo htmlspecialchars($pupuk['dosis_anjuran'] ?? '-'); ?></td>
                                    <td class="py-4 px-4 text-slate-400 max-w-xs truncate"><?php echo htmlspecialchars($pupuk['deskripsi'] ?? '-'); ?></td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openModalEdit(<?php echo htmlspecialchars(json_encode($pupuk)); ?>)" class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/20 rounded-xl transition cursor-pointer" title="Edit Data">
                                                <i class='bx bx-edit text-sm'></i>
                                            </button>
                                            <a href="?hapus=<?php echo $pupuk['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pupuk ini?');" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl transition" title="Hapus Data">
                                                <i class='bx bx-trash text-sm'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-500">Belum ada data pupuk terdaftar. Klik tombol di atas untuk membuat baru.</td>
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

    <!-- MODAL TAMBAH PUPUK -->
    <div id="modal-tambah" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-card bg-slate-900 border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2"><i class='bx bx-plus-circle text-emerald-400'></i> Tambah Pupuk Baru</h3>
                <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer"><i class='bx bx-x text-xl'></i></button>
            </div>
            <form action="" method="POST" class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Nama Pupuk</label>
                    <input type="text" name="nama_pupuk" required placeholder="Contoh: NPK Phonska / Urea" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Jenis Pupuk</label>
                    <select name="jenis" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <option value="Anorganik">Anorganik</option>
                        <option value="Organik">Organik</option>
                        <option value="Hayati">Hayati / Biologi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Kandungan Nutrisi</label>
                    <input type="text" name="kandungan" placeholder="Contoh: N 16%, P 16%, K 16%" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Dosis Anjuran</label>
                    <input type="text" name="dosis_anjuran" placeholder="Contoh: 200 gram / pohon" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Deskripsi / Peruntukan</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat fungsi pupuk ini..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                    <button type="submit" name="tambah_pupuk" class="px-4 py-2 bg-emerald-500 text-slate-950 font-semibold rounded-xl hover:bg-emerald-600 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PUPUK -->
    <div id="modal-edit" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-card bg-slate-900 border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2"><i class='bx bx-edit text-blue-400'></i> Edit Data Pupuk</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-white cursor-pointer"><i class='bx bx-x text-xl'></i></button>
            </div>
            <form action="" method="POST" class="space-y-3 text-xs">
                <input type="hidden" name="id" id="edit-id">
                <div>
                    <label class="block text-slate-400 mb-1">Nama Pupuk</label>
                    <input type="text" name="nama_pupuk" id="edit-nama" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Jenis Pupuk</label>
                    <select name="jenis" id="edit-jenis" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                        <option value="Anorganik">Anorganik</option>
                        <option value="Organik">Organik</option>
                        <option value="Hayati">Hayati / Biologi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Kandungan Nutrisi</label>
                    <input type="text" name="kandungan" id="edit-kandungan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Dosis Anjuran</label>
                    <input type="text" name="dosis_anjuran" id="edit-dosis" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Deskripsi / Peruntukan</label>
                    <textarea name="deskripsi" id="edit-deskripsi" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl hover:bg-slate-700 transition">Batal</button>
                    <button type="submit" name="edit_pupuk" class="px-4 py-2 bg-blue-500 text-slate-950 font-semibold rounded-xl hover:bg-blue-600 transition">Perbarui Data</button>
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

        function openModalEdit(data) {
            document.getElementById('edit-id').value = data.id;
            document.getElementById('edit-nama').value = data.nama_pupuk || '';
            document.getElementById('edit-jenis').value = data.jenis || 'Anorganik';
            document.getElementById('edit-kandungan').value = data.kandungan || '';
            document.getElementById('edit-dosis').value = data.dosis_anjuran || '';
            document.getElementById('edit-deskripsi').value = data.deskripsi || '';
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>

</body>
</html>