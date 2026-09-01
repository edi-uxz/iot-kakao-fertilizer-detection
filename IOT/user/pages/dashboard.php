<?php
// Pastikan session dimulai untuk membaca & mengubah lahan yang aktif dipilih user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================================================
// 1. KONEKSI & LOGIKA DATA (Mendukung Perpindahan Lahan Dinamis)
// ==============================================================================
if (!isset($db) || !$db) {
    @include 'koneksi.php'; 
}

// Menentukan ID Pengguna (Contoh default ID pengguna: 3)
$id_pengguna_login = isset($_SESSION['id_pengguna']) ? intval($_SESSION['id_pengguna']) : 3;

// A. Ambil semua daftar lahan milik pengguna untuk isi menu dropdown select
$daftar_lahan = [];
if ($db) {
    $q_lahan = mysqli_query($db, "SELECT id, nama_lahan FROM lahan WHERE id_pengguna = '$id_pengguna_login' ORDER BY id ASC");
    while ($row_lahan = mysqli_fetch_assoc($q_lahan)) {
        $daftar_lahan[] = $row_lahan;
    }
}

// B. Logika menangkap perpindahan lahan dari menu dropdown (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_lahan'])) {
    $selected_lahan_id = intval($_POST['pilih_lahan']);
    $_SESSION['id_lahan'] = $selected_lahan_id; // Simpan di session agar halaman lain ikut sinkron
} else {
    // Jika tidak ada POST, ambil dari session, atau default ke lahan pertama (0)
    $selected_lahan_id = isset($_SESSION['id_lahan']) ? intval($_SESSION['id_lahan']) : 0;
}

// Jika session masih kosong dan user punya lahan, otomatis pilih lahan pertama
if ($selected_lahan_id == 0 && !empty($daftar_lahan)) {
    $selected_lahan_id = intval($daftar_lahan[0]['id']);
    $_SESSION['id_lahan'] = $selected_lahan_id;
}

// ==============================================================================
// 2. DATA MASTER KONDISI IDEAL MULTI-TANAMAN (The Brain Memory)
// ==============================================================================
// Ambang batas minimum ideal unsur hara makro (mg/kg) per jenis komoditas
$master_tanaman = [
    'Kakao' => [
        'nama' => 'Tanaman Kakao (Cacao)',
        'n_ideal' => 40, 'p_ideal' => 35, 'k_ideal' => 45,
        'rekomendasi_defisit' => 'Kandungan hara Kakao rendah. AI merekomendasikan penambahan NPK 15-15-15 dan pupuk organik untuk merangsang pertumbuhan buah.',
        'dosis_defisit' => ['n' => 45, 'p' => 40, 'k' => 50, 'ca' => 15],
        'dosis_optimal' => ['n' => 15, 'p' => 15, 'k' => 20, 'ca' => 10]
    ],
    'Kopi' => [
        'nama' => 'Tanaman Kopi (Coffee)',
        'n_ideal' => 50, 'p_ideal' => 30, 'k_ideal' => 55,
        'rekomendasi_defisit' => 'Kopi membutuhkan Kalium tinggi untuk pengisian buah. AI merekomendasikan penambahan pupuk KCl atau NPK tinggi unsur K.',
        'dosis_defisit' => ['n' => 40, 'p' => 30, 'k' => 60, 'ca' => 12],
        'dosis_optimal' => ['n' => 15, 'p' => 10, 'k' => 25, 'ca' => 8]
    ],
    'Jagung' => [
        'nama' => 'Tanaman Jagung (Maize)',
        'n_ideal' => 60, 'p_ideal' => 40, 'k_ideal' => 40,
        'rekomendasi_defisit' => 'Jagung sangat membutuhkan asupan Nitrogen. AI merekomendasikan penambahan pupuk Urea atau ZA segera untuk mencegah daun menguning.',
        'dosis_defisit' => ['n' => 65, 'p' => 35, 'k' => 35, 'ca' => 8],
        'dosis_optimal' => ['n' => 20, 'p' => 15, 'k' => 15, 'ca' => 5]
    ]
];

// ==============================================================================
// 3. MESIN INFERENSI MULTI-TANAMAN (The Brain Engine)
// ==============================================================================
function getMultiPlantInferensi($db, $lahan_id, $master_tanaman) {
    $nitrogen = $fosfor = $kalium = $label = [];
    $lastUpdate = '-';
    $tmp = [];
    
    // Default Fallback jika data belum/tidak ditemukan
    $jenis_tanaman = 'Kakao'; 
    $kondisi = "🕒 Menunggu Data";
    $rekom = "Periksa koneksi Node MCU / ESP8266 Lahan Anda.";
    $n_rekom = $p_rekom = $k_rekom = $ca_rekom = 0;

    if ($db && $lahan_id > 0) {
        // A. Cek Komoditas Lahan di Database
        $q_lahan = mysqli_query($db, "SELECT jenis_tanaman FROM lahan WHERE id = '$lahan_id' LIMIT 1");
        if ($row_lahan = mysqli_fetch_assoc($q_lahan)) {
            $jenis_db = $row_lahan['jenis_tanaman'];
            // Jika di database bernilai '0', atau tanaman kustom/belum terdaftar, kita arahkan ke Kakao sebagai basis utama
            if ($jenis_db !== '0' && array_key_exists($jenis_db, $master_tanaman)) {
                $jenis_tanaman = $jenis_db;
            }
        }

        // B. Ambil Telemetry Sensor (7 Data Terakhir) - Disinkronkan dengan nama tabel Anda 'data_sensor'
        $q = mysqli_query($db, "SELECT * FROM data_sensor WHERE id_lahan = '$lahan_id' ORDER BY id DESC LIMIT 7");
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                $tmp[] = $r;
                if ($lastUpdate === '-') $lastUpdate = date('H:i:s', strtotime($r['waktu']));
            }
        }
        
        $data_db = array_reverse($tmp);
        foreach ($data_db as $r) {
            $label[]    = date('H:i', strtotime($r['waktu']));
            $nitrogen[] = isset($r['nitrogen']) ? (float)$r['nitrogen'] : 0; 
            $fosfor[]   = isset($r['fosfor']) ? (float)$r['fosfor'] : 0; 
            $kalium[]   = isset($r['kalium']) ? (float)$r['kalium'] : 0; 
        }
    }

    $n_terbaru = !empty($nitrogen) ? end($nitrogen) : 0;
    $p_terbaru = !empty($fosfor) ? end($fosfor) : 0;
    $k_terbaru = !empty($kalium) ? end($kalium) : 0;

    // C. Evaluasi Logika Komparasi Dinamis (Realtime Sensor vs Aturan Komoditas)
    if ($lahan_id == 0) {
        $kondisi = "🕒 Pilih Lahan";
        $rekom = "Silakan tentukan atau buat petak lahan terlebih dahulu.";
    } else if ($n_terbaru > 0 || $p_terbaru > 0 || $k_terbaru > 0) {
        
        $rule = $master_tanaman[$jenis_tanaman];
        
        // Dibandingkan secara dinamis dengan rule ideal masing-masing tanaman
        if ($n_terbaru < $rule['n_ideal'] || $p_terbaru < $rule['p_ideal'] || $k_terbaru < $rule['k_ideal']) {
            $kondisi = "⚠️ Defisit " . $jenis_tanaman;
            $rekom = $rule['rekomendasi_defisit'];
            $n_rekom = $rule['dosis_defisit']['n'];
            $p_rekom = $rule['dosis_defisit']['p'];
            $k_rekom = $rule['dosis_defisit']['k'];
            $ca_rekom = $rule['dosis_defisit']['ca'];
        } else {
            $kondisi = "✅ " . $jenis_tanaman . " Optimal";
            $rekom = "Kondisi hara tanah memenuhi ambang batas ideal untuk " . $rule['nama'] . ". Lanjutkan pengawasan berkala.";
            $n_rekom = $rule['dosis_optimal']['n'];
            $p_rekom = $rule['dosis_optimal']['p'];
            $k_rekom = $rule['dosis_optimal']['k'];
            $ca_rekom = $rule['dosis_optimal']['ca'];
        }
    }

    return [
        'labels' => $label, 'nitrogen' => $nitrogen, 'fosfor' => $fosfor, 'kalium' => $kalium,
        'nTerbaru' => $n_terbaru, 'pTerbaru' => $p_terbaru, 'kTerbaru' => $k_terbaru, 
        'lastUpdate' => $lastUpdate, 'kondisi' => $kondisi, 'rekomendasi' => $rekom, 
        'n' => $n_rekom, 'p' => $p_rekom, 'k' => $k_rekom, 'ca' => $ca_rekom,
        'tanaman_terdeteksi' => $jenis_tanaman
    ];
}

// Endpoint AJAX Sync Realtime (Menghubungkan JS ke Data AI Terkini)
if (isset($_GET['ajax_sync'])) {
    // Bersihkan buffer output agar tidak ada tag HTML yang ikut terkirim ke JSON
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(getMultiPlantInferensi($db, $selected_lahan_id, $master_tanaman));
    exit;
}

$initialData = getMultiPlantInferensi($db, $selected_lahan_id, $master_tanaman);

// D. Ambil Informasi Judul Lahan Aktif & Jenis Tanaman Terkait
$nama_lahan_aktif = "Belum Memilih Lahan";
$tanaman_lahan_aktif = "-";
if ($db && $selected_lahan_id > 0) {
    $q_aktif = mysqli_query($db, "SELECT nama_lahan, jenis_tanaman FROM lahan WHERE id = '$selected_lahan_id' LIMIT 1");
    if ($row_aktif = mysqli_fetch_assoc($q_aktif)) {
        $nama_lahan_aktif = $row_aktif['nama_lahan'];
        $tanaman_lahan_aktif = $row_aktif['jenis_tanaman'] == '0' ? 'Kakao' : $row_aktif['jenis_tanaman'];
    }
}

// E. Ambil Riwayat Laporan Pemupukan Khusus Lahan Ini Saja
$riwayat = [];
if ($db && $selected_lahan_id > 0) {
    $q_riwayat = mysqli_query($db, "SELECT * FROM laporan_pemupukan WHERE id_lahan = '$selected_lahan_id' ORDER BY id DESC LIMIT 5");
    if ($q_riwayat) {
        while ($row = mysqli_fetch_assoc($q_riwayat)) { $riwayat[] = $row; }
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dashboard-wrapper { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px; width: 100%; }
    .bento-card { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px; padding: 24px; backdrop-filter: blur(10px); }
    
    /* Lahan Selector Bar Header Style */
    .lahan-selector-bar {
        background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px;
        padding: 20px 24px; margin-bottom: 20px; display: flex; justify-content: space-between;
        align-items: center; flex-wrap: wrap; gap: 15px; backdrop-filter: blur(10px);
    }
    .select-lahan-dropdown {
        background: rgba(0, 0, 0, 0.5); border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff; padding: 12px 20px; border-radius: 14px; font-family: inherit;
        font-weight: 600; cursor: pointer; outline: none; transition: 0.3s;
    }
    .select-lahan-dropdown:focus { border-color: #2ecc71; }

    .sensor-widget { grid-column: span 4; display: flex; align-items: center; gap: 15px; }
    .icon-circle { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .nutrisi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }
    .nutrisi-item { background: rgba(255,255,255,0.03); padding: 15px 10px; border-radius: 15px; text-align: center; }
    .custom-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .custom-table th { text-align: left; color: #888; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .custom-table td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.02); color: #ddd; }
    @media (max-width: 768px) { .sensor-widget, .span-chart, .span-full, .lahan-selector-bar { grid-column: span 12 !important; flex-direction: column; align-items: flex-start; } .select-lahan-dropdown { width: 100%; } }
</style>

<div class="lahan-selector-bar">
    <div>
        <small style="color: #888; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Ganti Pemantauan Petak</small>
        <h2 style="color: #fff; margin: 3px 0 0 0; font-weight: 800;">
            <i class="fa-solid fa-layer-group" style="color:#60a5fa; margin-right:6px;"></i> 
            Lahan Aktif: <span style="color: #60a5fa;"><?= htmlspecialchars($nama_lahan_aktif) ?></span> 
            <span style="font-size: 1rem; color: #888; font-weight: 500;">(Komoditas: <?= htmlspecialchars($tanaman_lahan_aktif) ?>)</span>
        </h2>
    </div>
    
    <form method="POST" id="formSwitchLahan">
        <select name="pilih_lahan" class="select-lahan-dropdown" onchange="document.getElementById('formSwitchLahan').submit();">
            <?php if (!empty($daftar_lahan)): foreach($daftar_lahan as $lh): ?>
                <option value="<?= $lh['id'] ?>" <?= $lh['id'] == $selected_lahan_id ? 'selected' : '' ?>>
                    🌳 <?= htmlspecialchars($lh['nama_lahan']) ?>
                </option>
            <?php endforeach; else: ?>
                <option value="0">Belum Ada Lahan Terdaftar</option>
            <?php endif; ?>
        </select>
    </form>
</div>

<div class="bento-card" style="margin-bottom: 20px; background: linear-gradient(135deg, rgba(46, 204, 113, 0.05), transparent); border-color: rgba(255, 255, 255, 0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="color: #fff; margin:0; font-weight: 700;">Ringkasan Analisis Telemetry</h3>
        </div>
        <div style="text-align: right;">
            <h2 style="color: #fff; margin: 0; font-weight: 800;">Status AI: <span id="status-lahan" style="color: #2ecc71;"><?= $initialData['kondisi'] ?></span></h2>
            <p style="color: #666; font-size: 0.8rem; margin: 3px 0 0 0;">Sync IoT terakhir: <span id="last-update" class="font-mono"><?= $initialData['lastUpdate'] ?></span></p>
        </div>
    </div>
</div>

<div class="dashboard-wrapper">
    <div class="bento-card sensor-widget">
        <div class="icon-circle" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;"><i class="fa-solid fa-leaf"></i></div>
        <div><small style="color: #888;">Live Nitrogen (N)</small><br><span id="val-n-live" style="font-size: 1.5rem; font-weight: 800; color: #fff;"><?= $initialData['nTerbaru'] ?></span> <small style="color: #555;">mg/kg</small></div>
    </div>
    <div class="bento-card sensor-widget">
        <div class="icon-circle" style="background: rgba(52, 152, 219, 0.1); color: #3498db;"><i class="fa-solid fa-flask"></i></div>
        <div><small style="color: #888;">Live Fosfor (P)</small><br><span id="val-p-live" style="font-size: 1.5rem; font-weight: 800; color: #fff;"><?= $initialData['pTerbaru'] ?></span> <small style="color: #555;">mg/kg</small></div>
    </div>
    <div class="bento-card sensor-widget">
        <div class="icon-circle" style="background: rgba(230, 126, 34, 0.1); color: #e67e22;"><i class="fa-solid fa-bolt"></i></div>
        <div><small style="color: #888;">Live Kalium (K)</small><br><span id="val-k-live" style="font-size: 1.5rem; font-weight: 800; color: #fff;"><?= $initialData['kTerbaru'] ?></span> <small style="color: #555;">mg/kg</small></div>
    </div>

    <div class="bento-card span-chart" style="grid-column: span 8;">
        <h3 style="color: #fff; font-size: 1rem; font-weight: 600;"><i class="fa-solid fa-chart-line" style="color: #2ecc71; margin-right: 8px;"></i> Kandungan Telemetry Unsur Hara Makro</h3>
        <div style="height: 250px; margin-top: 15px;"><canvas id="chartSensor"></canvas></div>
    </div>

    <div class="bento-card" style="grid-column: span 4;">
        <h3 style="color: #fff; font-size: 1rem; font-weight: 600;"><i class="fa-solid fa-wand-magic-sparkles" style="color: #60a5fa; margin-right: 8px;"></i> Hasil Analisis Kebutuhan</h3>
        <p id="txt-rekomendasi" style="font-size: 0.8rem; color: #94a3b8; height: 50px; margin-top: 10px; line-height: 1.5;"><?= $initialData['rekomendasi'] ?></p>
        
        <div style="color:#888; font-size:0.75rem; font-weight:700; text-transform:uppercase; margin-top:15px;">Dosis Target (kg/ha)</div>
        <div class="nutrisi-grid">
            <div class="nutrisi-item"><b id="val-n" style="color:#2ecc71; font-size: 1.1rem; font-weight: 800;"><?= $initialData['n'] ?></b><br><small style="color:#888;">N</small></div>
            <div class="nutrisi-item"><b id="val-p" style="color:#3498db; font-size: 1.1rem; font-weight: 800;"><?= $initialData['p'] ?></b><br><small style="color:#888;">P</small></div>
            <div class="nutrisi-item"><b id="val-k" style="color:#e67e22; font-size: 1.1rem; font-weight: 800;"><?= $initialData['k'] ?></b><br><small style="color:#888;">K</small></div>
            <div class="nutrisi-item"><b id="val-ca" style="color:#9b59b6; font-size: 1.1rem; font-weight: 800;"><?= $initialData['ca'] ?></b><br><small style="color:#888;">Ca</small></div>
        </div>
    </div>

    <div class="bento-card" style="grid-column: span 12;">
        <h3 style="color: #fff; font-size: 1rem; margin-bottom: 15px; font-weight: 600;"><i class="fa-solid fa-history" style="color: #e67e22; margin-right: 8px;"></i> Arsip Aktivitas Pemupukan Terakhir</h3>
        <table class="custom-table">
            <thead><tr><th>Tanggal Pemupukan</th><th>Formula Material</th><th>Dosis/Volume</th><th>Metode</th></tr></thead>
            <tbody>
                <?php if(!empty($riwayat)): foreach($riwayat as $r): ?>
                <tr>
                    <td class="font-mono"><?= date('d M Y, H:i', strtotime($r['tanggal'])) ?> WIB</td>
                    <td style="color: #60a5fa; font-weight: 600;"><?= htmlspecialchars($r['jenis_pupuk']) ?></td>
                    <td class="font-mono"><?= htmlspecialchars($r['jumlah_pupuk']) ?></td>
                    <td><span style="background:rgba(46,204,113,0.1); color:#2ecc71; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;"><?= htmlspecialchars($r['metode']) ?></span></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4" style="text-align:center; color:#555; padding: 40px 0;"><i class="fa-solid fa-folder-open" style="font-size:1.5rem; display:block; margin-bottom:5px;"></i> Belum ada rekaman laporan untuk petak ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('chartSensor').getContext('2d');
    
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($initialData['labels']) ?>,
            datasets: [
                {
                    label: 'Nitrogen (N)',
                    data: <?= json_encode($initialData['nitrogen']) ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.02)',
                    fill: true, tension: 0.3, borderWidth: 3
                },
                {
                    label: 'Fosfor (P)',
                    data: <?= json_encode($initialData['fosfor']) ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.02)',
                    fill: true, tension: 0.3, borderWidth: 3
                },
                {
                    label: 'Kalium (K)',
                    data: <?= json_encode($initialData['kalium']) ?>,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230, 126, 34, 0.02)',
                    fill: true, tension: 0.3, borderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#888', font: { size: 11, family: 'Plus Jakarta Sans' } } } },
            scales: {
                y: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: { color: '#888', font: { family: 'Plus Jakarta Sans' } }
                },
                x: { grid: { display: false }, ticks: { color: '#888', font: { family: 'Plus Jakarta Sans' } } }
            }
        }
    });

    // Fungsi Pengambilan Data Real-Time Berbasis Multi-Tanaman Melalui Jalur AJAX
    function syncDashboard() {
        // Ambil path tanpa menyertakan query parameter GET lain yang bisa membingungkan router halaman
        const cleanPath = window.location.protocol + "//" + window.location.host + window.location.pathname;
        
        fetch(cleanPath + '?ajax_sync=1')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                // Perbarui Angka Live Widget Sensor
                document.getElementById('val-n-live').innerText = data.nTerbaru;
                document.getElementById('val-p-live').innerText = data.pTerbaru;
                document.getElementById('val-k-live').innerText = data.kTerbaru;
                document.getElementById('last-update').innerText = data.lastUpdate;
                document.getElementById('status-lahan').innerText = data.kondisi;
                
                // Perbarui Teks Rekomendasi & Box Angka Target Dosis Pupuk
                document.getElementById('txt-rekomendasi').innerText = data.rekomendasi;
                document.getElementById('val-n').innerText = data.n;
                document.getElementById('val-p').innerText = data.p;
                document.getElementById('val-k').innerText = data.k;
                document.getElementById('val-ca').innerText = data.ca;

                // Sinkronisasi Node Grafik Tren Chart.js
                myChart.data.labels = data.labels;
                myChart.data.datasets[0].data = data.nitrogen;
                myChart.data.datasets[1].data = data.fosfor;
                myChart.data.datasets[2].data = data.kalium;
                myChart.update();
            })
            .catch(error => console.log('Pooling Error:', error));
    }

    // Aktifkan interval pooling data 5 detik hanya jika lahan sudah terpilih sah
    if (<?= $selected_lahan_id ?> > 0) {
        setInterval(syncDashboard, 5000);
    }
});
</script>