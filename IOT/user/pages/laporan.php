<?php
// Pastikan session sudah dimulai di index.php atau di sini untuk menyimpan lahan aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================================================
// 1. LOGIKA DATA, ACTIONS, & AMBIL DAFTAR LAHAN
// ==============================================================================
$success_message = "";

// Anggap id_pengguna yang login disimpan di session, misal $_SESSION['id_pengguna']. 
// Jika belum ada session, kita sesuaikan dengan ID pengguna default (contoh: 3 berdasarkan data SQL kamu)
$id_pengguna_login = isset($_SESSION['id_pengguna']) ? intval($_SESSION['id_pengguna']) : 3;

// Ambil semua daftar lahan milik pengguna yang sedang login untuk menu dropdown
$daftar_lahan = [];
if ($db) {
    $q_lahan = mysqli_query($db, "SELECT id, nama_lahan FROM lahan WHERE id_pengguna = '$id_pengguna_login' ORDER BY id ASC");
    while ($row_lahan = mysqli_fetch_assoc($q_lahan)) {
        $daftar_lahan[] = $row_lahan;
    }
}

// Menentukan lahan mana yang sedang aktif dipilih
// 1. Cek apakah ada perubahan lahan dari dropdown (POST)
// 2. Cek apakah ada dari URL (GET)
// 3. Cek apakah ada di session
// 4. Jika semua kosong, default ke lahan pertama milik pengguna tersebut
if (isset($_POST['pilih_lahan'])) {
    $selected_lahan_id = intval($_POST['pilih_lahan']);
    $_SESSION['id_lahan'] = $selected_lahan_id;
} else {
    $selected_lahan_id = isset($_SESSION['id_lahan']) ? intval($_SESSION['id_lahan']) : (isset($_GET['id_lahan']) ? intval($_GET['id_lahan']) : 0);
}

if ($selected_lahan_id == 0 && !empty($daftar_lahan)) {
    $selected_lahan_id = intval($daftar_lahan[0]['id']);
    $_SESSION['id_lahan'] = $selected_lahan_id;
}

// Eksekusi Simpan Pemupukan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pemupukan'])) {
    $tanggal      = mysqli_real_escape_string($db, $_POST['tanggal']);
    $jenis_pupuk  = mysqli_real_escape_string($db, $_POST['jenis_pupuk']);
    $jumlah_pupuk = mysqli_real_escape_string($db, $_POST['jumlah_pupuk']);
    $metode       = mysqli_real_escape_string($db, $_POST['metode']);
    $catatan      = mysqli_real_escape_string($db, $_POST['catatan']);

    if ($db && !empty($jenis_pupuk) && $selected_lahan_id > 0) {
        $insert = mysqli_query($db, "INSERT INTO laporan_pemupukan (id_lahan, tanggal, jenis_pupuk, jumlah_pupuk, metode, catatan) 
                                     VALUES ('$selected_lahan_id', '$tanggal', '$jenis_pupuk', '$jumlah_pupuk', '$metode', '$catatan')");
        if ($insert) {
            $success_message = "Laporan berhasil diarsipkan ke database berdasarkan lahan terpilih.";
        }
    }
}

// Fetch Riwayat berdasarkan lahan yang aktif terpilih
$data_laporan = [];
$nama_lahan_aktif = "Lahan Tidak Diketahui";
if ($db && $selected_lahan_id > 0) {
    // Ambil nama lahan aktif
    $q_nama = mysqli_query($db, "SELECT nama_lahan FROM lahan WHERE id = '$selected_lahan_id' LIMIT 1");
    if ($row_nama = mysqli_fetch_assoc($q_nama)) {
        $nama_lahan_aktif = $row_nama['nama_lahan'];
    }

    // Ambil data laporan pemupukan
    $q = mysqli_query($db, "SELECT * FROM laporan_pemupukan WHERE id_lahan = '$selected_lahan_id' ORDER BY tanggal DESC");
    while ($row = mysqli_fetch_assoc($q)) { 
        $data_laporan[] = $row; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AgroIntelli | Histori Pemupukan</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f1a; --card: rgba(255, 255, 255, 0.03); --border: rgba(255, 255, 255, 0.08);
            --green: #4ade80; --blue: #60a5fa; --orange: #fb923c; --text: #f8fafc; --muted: #94a3b8;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 20px; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Top Bar Selector Lahan */
        .lahan-selector-bar {
            background: var(--card); border: 1px solid var(--border); border-radius: 20px;
            padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between;
            align-items: center; backdrop-filter: blur(10px);
        }
        .lahan-info { display: flex; align-items: center; gap: 12px; }
        .lahan-info i { font-size: 1.6rem; color: var(--green); }
        .lahan-info h1 { font-size: 1.15rem; margin: 0; font-weight: 700; }
        .select-lahan-dropdown {
            background: rgba(0, 0, 0, 0.4); border: 1px solid var(--border);
            color: #fff; padding: 10px 18px; border-radius: 12px; font-family: inherit;
            font-weight: 600; cursor: pointer; outline: none; transition: 0.3s;
        }
        .select-lahan-dropdown:focus { border-color: var(--green); }

        .grid-layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-bottom: 30px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 24px; padding: 25px; backdrop-filter: blur(10px); }
        
        h2 { font-size: 1.25rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; font-weight: 700; }

        /* Form Elements */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .input-group label { display: block; font-size: 0.75rem; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; font-weight: 600; }
        input, select, textarea { 
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--border); 
            color: #fff; padding: 12px; border-radius: 12px; font-family: inherit; box-sizing: border-box;
        }
        input:focus { border-color: var(--green); outline: none; }

        .btn { 
            display: inline-flex; align-items: center; gap: 8px; padding: 14px 24px; border-radius: 14px; 
            font-weight: 700; cursor: pointer; border: none; transition: 0.3s; font-size: 0.9rem;
        }
        .btn-primary { background: var(--green); color: #064e3b; width: 100%; justify-content: center; }
        .btn-secondary { background: var(--card); color: #fff; border: 1px solid var(--border); }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Table Styling */
        .table-wrap { width: 100%; overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 15px; color: var(--green); font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.85rem; color: var(--muted); }
        tr:hover td { background: rgba(255,255,255,0.01); color: var(--text); }

        .status-tag { background: rgba(74, 222, 128, 0.1); color: var(--green); padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .alert { background: rgba(74, 222, 128, 0.15); border: 1px solid var(--green); color: var(--green); padding: 15px; border-radius: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        @media (max-width: 850px) { 
            .grid-layout { grid-template-columns: 1fr; } 
            .lahan-selector-bar { flex-direction: column; gap: 15px; align-items: flex-start; }
            .select-lahan-dropdown { width: 100%; }
        }
        
        /* Print Overrides */
        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .btn, .form-section, .info-section, .alert, .lahan-selector-bar form { display: none !important; }
            .card { border: none; background: none; }
            table { border: 1px solid #eee; }
            th { color: #000; border-bottom: 2px solid #000; }
            td { color: #333; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="lahan-selector-bar">
        <div class="lahan-info">
            <i class='bx bx-landscape'></i>
            <div>
                <h1>Monitoring: <span style="color: var(--blue);"><?= htmlspecialchars($nama_lahan_aktif) ?></span></h1>
                <small style="color: var(--muted)">Kelola riwayat & prediksi pemupukan per petak</small>
            </div>
        </div>
        <form method="POST" id="formPilihLahan">
            <select name="pilih_lahan" class="select-lahan-dropdown" onchange="document.getElementById('formPilihLahan').submit();">
                <?php if ($daftar_lahan): foreach($daftar_lahan as $lh): ?>
                    <option value="<?= $lh['id'] ?>" <?= $lh['id'] == $selected_lahan_id ? 'selected' : '' ?>>
                        🌳 <?= htmlspecialchars($lh['nama_lahan']) ?>
                    </option>
                <?php endforeach; else: ?>
                    <option value="0">Belum Ada Lahan Terdaftar</option>
                <?php endif; ?>
            </select>
        </form>
    </div>

    <div class="grid-layout">
        <div class="card form-section">
            <h2><i class='bx bx-plus-circle' style="color:var(--green)"></i> Input Aktivitas (<?= htmlspecialchars($nama_lahan_aktif) ?>)</h2>
            
            <?php if ($success_message): ?>
                <div class="alert"><i class='bx bxs-check-shield'></i> <?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="input-group">
                        <label>Waktu Pelaksanaan</label>
                        <input type="datetime-local" name="tanggal" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Jenis Material</label>
                        <input type="text" name="jenis_pupuk" placeholder="Contoh: NPK Phonska" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>Dosis (kg/gr)</label>
                        <input type="text" name="jumlah_pupuk" placeholder="Misal: 250gr / pohon">
                    </div>
                    <div class="input-group">
                        <label>Metode Aplikasi</label>
                        <select name="metode">
                            <option value="Tabur">Sistem Tabur</option>
                            <option value="Kocor">Sistem Kocor (Liquid)</option>
                            <option value="Tanam">Sistem Tanam (Pocket)</option>
                            <option value="Spray">Foliar Spray</option>
                        </select>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 20px;">
                    <label>Catatan Lapangan</label>
                    <textarea name="catatan" rows="3" placeholder="Bagaimana kondisi tanaman saat dipupuk?"></textarea>
                </div>

                <button type="submit" name="simpan_pemupukan" class="btn btn-primary" <?= empty($daftar_lahan) ? 'disabled' : '' ?>>
                    <i class='bx bx-save'></i> ARSIPKAN DATA PEMUPUKAN
                </button>
            </form>
        </div>

        <div class="card info-section" style="background: linear-gradient(145deg, rgba(96,165,250,0.05), transparent);">
            <h2><i class='bx bx-analyse' style="color:var(--blue)"></i> Analisis Data</h2>
            <p style="font-size:0.85rem; color:var(--muted)">Data historis ini disinkronisasikan dengan mesin inferensi AI untuk menentukan interval pemupukan berikutnya.</p>
            
            <div style="margin-top: 30px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-size:0.8rem; color:var(--muted)">Total Entri Laporan (<?= htmlspecialchars($nama_lahan_aktif) ?>)</span>
                    <span style="color:var(--green); font-weight:700"><?= count($data_laporan) ?></span>
                </div>
                <div style="width:100%; height:6px; background:rgba(255,255,255,0.05); border-radius:10px; overflow:hidden;">
                    <div style="width:<?= min(100, count($data_laporan)*10) ?>%; height:100%; background:var(--green);"></div>
                </div>
            </div>

            <div style="margin-top:40px; padding:20px; border-radius:15px; background:rgba(251,146,60,0.05); border-left:4px solid var(--orange)">
                <small style="color:var(--orange); font-weight:700">TIPS KAKAO:</small>
                <p style="margin:5px 0 0; font-size:0.8rem; color:var(--muted)">Pemupukan terbaik dilakukan saat tanah lembap (setelah hujan ringan) untuk mempercepat absorpsi akar.</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
            <h2><i class='bx bx-time-five' style="color:var(--orange)"></i> Log Aktivitas Pemupukan</h2>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class='bx bx-printer'></i> EXPORT PDF / CETAK
            </button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal & Jam</th>
                        <th>Jenis Pupuk</th>
                        <th>Dosis</th>
                        <th>Metode</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($data_laporan): foreach($data_laporan as $index => $row): ?>
                    <tr>
                        <td>#<?= count($data_laporan) - $index ?></td>
                        <td>
                            <div style="color:var(--text); font-weight:600"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                            <small><?= date('H:i', strtotime($row['tanggal'])) ?> WIB</small>
                        </td>
                        <td><span style="color:var(--blue); font-weight:600"><?= htmlspecialchars($row['jenis_pupuk']) ?></span></td>
                        <td><?= htmlspecialchars($row['jumlah_pupuk']) ?></td>
                        <td><span class="status-tag"><?= htmlspecialchars($row['metode']) ?></span></td>
                        <td style="max-width:300px; font-size:0.8rem"><?= htmlspecialchars($row['catatan']) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:60px">
                            <i class='bx bx-data' style="font-size:3rem; opacity:0.2"></i>
                            <p>Belum ada data pemupukan tersimpan untuk lahan ini.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>