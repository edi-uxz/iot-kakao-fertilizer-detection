<?php
// admin/berita.php - Kelola Berita & Edukasi Pertanian Kakao
session_start();
require_once '../koneksi.php';

// Keamanan: Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php?error=unauthorized");
    exit;
}

$pesan = '';
$tipe_pesan = '';

// Proses Tambah / Edit Berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_berita'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $judul = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $kategori = mysqli_real_escape_string($koneksi, trim($_POST['kategori']));
    $konten = mysqli_real_escape_string($koneksi, trim($_POST['konten']));
    $penulis = mysqli_real_escape_string($koneksi, trim($_SESSION['nama'] ?? 'Admin'));

    if (!empty($judul) && !empty($konten)) {
        if ($id > 0) {
            // Update Berita
            $query = "UPDATE berita SET judul='$judul', kategori='$kategori', konten='$konten' WHERE id=$id";
            if (mysqli_query($koneksi, $query)) {
                $pesan = "Berita berhasil diperbarui!";
                $tipe_pesan = "sukses";
            } else {
                $pesan = "Gagal memperbarui berita: " . mysqli_error($koneksi);
                $tipe_pesan = "gagal";
            }
        } else {
            // Tambah Berita Baru
            $query = "INSERT INTO berita (judul, kategori, konten, penulis, tanggal) VALUES ('$judul', '$kategori', '$konten', '$penulis', NOW())";
            if (mysqli_query($koneksi, $query)) {
                $pesan = "Berita baru berhasil diterbitkan!";
                $tipe_pesan = "sukses";
            } else {
                $pesan = "Gagal menerbitkan berita: " . mysqli_error($koneksi);
                $tipe_pesan = "gagal";
            }
        }
    } else {
        $pesan = "Judul dan konten berita wajib diisi!";
        $tipe_pesan = "gagal";
    }
}

// Proses Hapus Berita
if (isset($_GET['action']) && $_GET['action'] === 'hapus') {
    $id_hapus = intval($_GET['id']);
    if ($id_hapus > 0) {
        if (mysqli_query($koneksi, "DELETE FROM berita WHERE id=$id_hapus")) {
            header("Location: berita.php?msg=deleted");
            exit;
        }
    }
}

// Ambil data berita untuk diedit
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $id_edit = intval($_GET['id']);
    $q_edit = mysqli_query($koneksi, "SELECT * FROM berita WHERE id=$id_edit");
    if ($q_edit && mysqli_num_rows($q_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($q_edit);
    }
}

// Ambil semua daftar berita
$query_berita = mysqli_query($koneksi, "SELECT * FROM berita ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita &amp; Edukasi - AgroIntelli</title>
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

    <!-- INCLUDE REUSABLE SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 fade-in">
        
        <!-- Notification Message -->
        <?php if (!empty($pesan)): ?>
            <div class="p-4 rounded-2xl text-xs font-medium flex items-center justify-between border <?php echo $tipe_pesan === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-red-500/10 border-red-500/30 text-red-400'; ?>">
                <div class="flex items-center gap-2">
                    <i class='bx <?php echo $tipe_pesan === 'sukses' ? 'bx-check-circle' : 'bx-error-circle'; ?> text-lg'></i>
                    <span><?php echo $pesan; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="p-4 rounded-2xl text-xs font-medium bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center gap-2">
                <i class='bx bx-info-circle text-lg'></i>
                <span>Berita berhasil dihapus.</span>
            </div>
        <?php endif; ?>

        <!-- Form Tambah / Edit Berita -->
        <div class="glass-card p-6 rounded-3xl border-slate-800">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class='bx bx-edit text-emerald-400'></i> <?php echo $edit_data ? 'Edit Berita / Artikel' : 'Terbitkan Berita &amp; Edukasi Baru'; ?>
            </h2>

            <form action="berita.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? 0; ?>">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Judul Artikel / Informasi</label>
                        <input type="text" name="judul" required value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" placeholder="Contoh: Tips Mengatasi Hama Kakao Saat Musim Hujan" class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Kategori Informasi</label>
                        <select name="kategori" class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-4 py-2.5 focus:outline-none focus:border-emerald-500">
                            <?php 
                            $kategori_list = ['Edukasi Kakao', 'Tips Pemupukan', 'Hama & Penyakit', 'Pengumuman Petani'];
                            foreach ($kategori_list as $kat) {
                                $sel = (isset($edit_data['kategori']) && $edit_data['kategori'] === $kat) ? 'selected' : '';
                                echo "<option value='$kat' $sel>$kat</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Isi Konten Berita / Petunjuk Lapangan</label>
                    <textarea name="konten" rows="5" required placeholder="Tuliskan penjelasan lengkap yang mudah dipahami oleh petani..." class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl p-4 focus:outline-none focus:border-emerald-500"><?php echo htmlspecialchars($edit_data['konten'] ?? ''); ?></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" name="simpan_berita" class="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold text-xs px-5 py-2.5 rounded-xl transition flex items-center gap-1 cursor-pointer">
                        <i class='bx bx-send text-base'></i> <?php echo $edit_data ? 'Simpan Perubahan' : 'Terbitkan Informasi'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="berita.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs px-4 py-2.5 rounded-xl transition">Batal Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Berita -->
        <div class="glass-card p-6 rounded-3xl border-slate-800 overflow-hidden space-y-4">
            <h3 class="font-bold text-white text-sm flex items-center gap-2">
                <i class='bx bx-list-ul text-emerald-400'></i> Riwayat Info &amp; Edukasi Terpublikasi
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-mono uppercase text-slate-400">
                            <th class="py-3 px-4">Tanggal Publikasi</th>
                            <th class="py-3 px-4">Judul &amp; Kategori</th>
                            <th class="py-3 px-4">Penulis</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-xs">
                        <?php if ($query_berita && mysqli_num_rows($query_berita) > 0): ?>
                            <?php while ($b = mysqli_fetch_assoc($query_berita)): ?>
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-3.5 px-4 text-slate-400 font-mono">
                                        <?php echo date('d-m-Y H:i', strtotime($b['tanggal'])); ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-white"><?php echo htmlspecialchars($b['judul']); ?></div>
                                        <span class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded font-mono mt-1 inline-block">
                                            <?php echo htmlspecialchars($b['kategori']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300">
                                        <?php echo htmlspecialchars($b['penulis']); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="berita.php?action=edit&amp;id=<?php echo $b['id']; ?>" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 flex items-center justify-center transition" title="Edit">
                                                <i class='bx bx-edit text-base'></i>
                                            </a>
                                            <a href="berita.php?action=hapus&amp;id=<?php echo $b['id']; ?>" onclick="return confirm('Yakin ingin menghapus berita ini?');" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center transition" title="Hapus">
                                                <i class='bx bx-trash text-base'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 font-sans">Belum ada berita atau panduan edukasi yang dipublikasikan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="text-center py-6 border-t border-slate-800/80 text-xs text-slate-500">
            AGROINTELLI &copy; 2026 | Smart Precision Farming System - Kakao Lampung
        </footer>
    </div>

</body>
</html>