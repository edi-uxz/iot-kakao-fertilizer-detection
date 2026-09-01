<?php
// ==============================================================================
// LOGIKA PHP (TIDAK DIUBAH - SESUAI PERMINTAAN)
// ==============================================================================
if (!isset($db) || !$db) { die("Koneksi database gagal."); }
$conn = $db; 

$id_pupuk = ''; $nama_pupuk = ''; $kandungan = ''; $deskripsi = ''; $dosis_anjuran = ''; $pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pupuk'])) {
    $id_pupuk = mysqli_real_escape_string($conn, $_POST['id_pupuk'] ?? '');
    $nama_pupuk = mysqli_real_escape_string($conn, $_POST['nama_pupuk'] ?? '');
    $kandungan = mysqli_real_escape_string($conn, $_POST['kandungan'] ?? '');
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $dosis_anjuran = mysqli_real_escape_string($conn, $_POST['dosis_anjuran'] ?? '');

    if ($id_pupuk) {
        $sql = "UPDATE pupuk SET nama_pupuk='$nama_pupuk', kandungan='$kandungan', deskripsi='$deskripsi', dosis_anjuran='$dosis_anjuran' WHERE id_pupuk='$id_pupuk'";
        $aksi = "Diperbarui";
    } else {
        $sql = "INSERT INTO pupuk (nama_pupuk, kandungan, deskripsi, dosis_anjuran) VALUES ('$nama_pupuk', '$kandungan', '$deskripsi', '$dosis_anjuran')";
        $aksi = "Ditambahkan";
    }
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='index.php?p=pengaturan_pupuk&status=success&aksi=$aksi';</script>";
        exit(); 
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $aksi_notif = $_GET['aksi'] ?? 'disimpan';
    $pesan = "<div class='glass-alert alert-success'>✅ Data berhasil $aksi_notif!</div>";
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['id']);
    if (mysqli_query($conn, "DELETE FROM pupuk WHERE id_pupuk = '$id_hapus'")) {
        echo "<script>window.location.href='index.php?p=pengaturan_pupuk&status=success&aksi=dihapus';</script>";
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_edit = mysqli_real_escape_string($conn, $_GET['id']);
    $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pupuk WHERE id_pupuk = '$id_edit'"));
    if ($data_edit) {
        $id_pupuk = $data_edit['id_pupuk']; $nama_pupuk = $data_edit['nama_pupuk'];
        $kandungan = $data_edit['kandungan']; $deskripsi = $data_edit['deskripsi'];
        $dosis_anjuran = $data_edit['dosis_anjuran'];
    }
}

$query_data = mysqli_query($db, "SELECT * FROM pupuk ORDER BY nama_pupuk ASC");
?>

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --emerald: #2ecc71; --dark: #0b0f1a; --glass: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.08);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--dark); color: #f8fafc; margin: 0; padding: 20px; }
    
    .container { max-width: 1200px; margin: 0 auto; }
    
    header h1 { font-size: 1.4rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    .crud-grid { display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; }

    .glass-card { 
        background: var(--glass); border: 1px solid var(--border); border-radius: 16px; 
        padding: 20px; backdrop-filter: blur(10px); 
    }

    /* Form Compact */
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 0.7rem; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; font-weight: 700; }
    .form-input { 
        width: 100%; padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); 
        border-radius: 8px; color: #fff; font-size: 0.85rem; box-sizing: border-box; 
    }
    .form-input:focus { border-color: var(--emerald); outline: none; }

    /* Table Fix */
    .table-wrapper { width: 100%; overflow: hidden; border-radius: 12px; }
    .pupuk-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pupuk-table th { 
        background: rgba(46, 204, 113, 0.1); color: var(--emerald); 
        font-size: 0.75rem; text-transform: uppercase; padding: 12px; text-align: left; 
    }
    .pupuk-table td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 0.8rem; vertical-align: middle; }

    .col-nama { width: 20%; }
    .col-kandungan { width: 15%; }
    .col-desk { width: 45%; }
    .col-aksi { width: 10%; text-align: center !important; }

    .desk-box { max-height: 50px; overflow-y: auto; color: #94a3b8; line-height: 1.4; padding-right: 5px; }
    .desk-box::-webkit-scrollbar { width: 3px; }
    .desk-box::-webkit-scrollbar-thumb { background: var(--border); }

    /* Buttons */
    .btn-save { 
        background: linear-gradient(135deg, #2ecc71, #27ae60); color: #fff; border: none; 
        width: 100%; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; 
    }
    .action-btns { display: flex; gap: 6px; justify-content: center; }
    .btn-mini { 
        width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; 
        border-radius: 6px; text-decoration: none; font-size: 1rem; transition: 0.2s; 
    }
    .edit { background: rgba(241, 196, 15, 0.1); color: #f1c40f; }
    .trash { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

    .glass-alert { padding: 12px; border-radius: 10px; margin-bottom: 15px; font-size: 0.85rem; border-left: 4px solid; }
    .alert-success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border-color: #2ecc71; }

    @media (max-width: 1000px) { .crud-grid { grid-template-columns: 1fr; } }
</style>

<div class="container">
    <header>
        <h1><i class='bx bxs-component' style="color: var(--emerald);"></i> Pengaturan Nutrisi</h1>
    </header>

    <?= $pesan ?>

    <div class="crud-grid">
        <div class="glass-card">
            <h3 style="margin: 0 0 15px 0; font-size: 1rem; color: var(--emerald);"><?= ($id_pupuk) ? '📝 Mode Edit' : '➕ Tambah Pupuk' ?></h3>
            <form method="POST">
                <input type="hidden" name="id_pupuk" value="<?= $id_pupuk ?>">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_pupuk" class="form-input" placeholder="Contoh: Urea" required value="<?= $nama_pupuk ?>">
                </div>
                <div class="form-group">
                    <label>Kandungan (N-P-K)</label>
                    <input type="text" name="kandungan" class="form-input" placeholder="46-0-0" required value="<?= $kandungan ?>">
                </div>
                <div class="form-group">
                    <label>Dosis Anjuran</label>
                    <input type="text" name="dosis_anjuran" class="form-input" placeholder="100kg/ha" required value="<?= $dosis_anjuran ?>">
                </div>
                <div class="form-group">
                    <label>Deskripsi & Fungsi</label>
                    <textarea name="deskripsi" class="form-input" rows="3" required><?= $deskripsi ?></textarea>
                </div>
                <button type="submit" name="submit_pupuk" class="btn-save">
                    <i class='bx bx-check-double'></i> SIMPAN DATA
                </button>
                <?php if ($id_pupuk): ?>
                    <a href="index.php?p=pengaturan_pupuk" style="display:block; text-align:center; margin-top:10px; color:#64748b; text-decoration:none; font-size:0.75rem;">Urungkan Perubahan</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="glass-card" style="padding: 10px;">
            <div class="table-wrapper">
                <table class="pupuk-table">
                    <thead>
                        <tr>
                            <th class="col-nama">Nama Pupuk</th>
                            <th class="col-kandungan">Kandungan</th>
                            <th class="col-desk">Fungsi Utama</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query_data) > 0): ?>
                            <?php while ($r = mysqli_fetch_assoc($query_data)): 
                                // Ambil ID dengan aman (sesuaikan dengan nama kolom di database Anda)
                                $id_data = $r['id_pupuk'] ?? $r['id'] ?? ''; 
                            ?>
                            <tr>
                                <td style="color: #fff; font-weight: 600;">
                                    <?= htmlspecialchars($r['nama_pupuk'] ?? '') ?>
                                </td>
                                <td>
                                    <span style="background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:4px;">
                                        <?= htmlspecialchars($r['kandungan'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="desk-box">
                                        <b>Dosis: <?= htmlspecialchars($r['dosis_anjuran'] ?? 'Tidak ditentukan') ?></b><br>
                                        <?= htmlspecialchars($r['deskripsi'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="index.php?p=pengaturan_pupuk&action=edit&id=<?= $id_data ?>" class="btn-mini edit">
                                            <i class='bx bxs-edit-alt'></i>
                                        </a>
                                        <a href="index.php?p=pengaturan_pupuk&action=delete&id=<?= $id_data ?>" class="btn-mini trash" onclick="return confirm('Hapus data ini?')">
                                            <i class='bx bxs-trash'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:40px; color:#64748b;">Belum ada data referensi pupuk.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

