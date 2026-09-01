<?php
// ======================================================================
// 1. CONFIGURATION, KONEKSI & SELECTOR LAHAN
// ======================================================================
date_default_timezone_set('Asia/Jakarta');

if (!isset($db) || !$db) {
    @include '../koneksi.php';
    if (!isset($db)) $db = null;
}
$conn = $db;

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;

// Ambil daftar seluruh lahan milik petani untuk dropdown select
$daftar_lahan = [];
if ($conn) {
    $q_lahan = mysqli_query($conn, "SELECT id, nama_lahan FROM lahan WHERE id_pengguna = $id_pengguna ORDER BY id DESC");
    if ($q_lahan) {
        while ($row = mysqli_fetch_assoc($q_lahan)) {
            $daftar_lahan[] = $row;
        }
    }
}

// Ambil ID Lahan yang sedang aktif dipilih
$selected_lahan_id = isset($_GET['id_lahan']) ? (int)$_GET['id_lahan'] : (!empty($daftar_lahan) ? $daftar_lahan[0]['id'] : 0);

// AKTIFKAN INI (true) UNTUK SIDANG AGAR RESPON AI SANGAT CEPAT (KOMPRESI WAKTU)
$SIDANG_MODE = true; 

// Logika Interval: 1 Menit (Sidang) vs 14 Hari (Normal)
$interval = ($SIDANG_MODE) ? "1 MINUTE" : "14 DAY";
$status_data = ($SIDANG_MODE) ? "Mode Sidang (Akselerasi 1 Menit)" : "Otomatis (14 Hari Terakhir)";

// Standard Ideal Kakao
$idealSuhu = 27.5; 
$idealLembab = 75; 
$idealPh = 6.0;

// ======================================================================
// 2. DATA TERAKHIR PEMUPUKAN BERDASARKAN LAHAN YANG DIPILIH
// ======================================================================
$queryHistory = mysqli_query($conn, "SELECT * FROM laporan_pemupukan WHERE id_lahan = $selected_lahan_id ORDER BY tanggal DESC LIMIT 1");
$dataHistory = mysqli_fetch_assoc($queryHistory);

if ($dataHistory) {
    $tglTerakhirPupuk = $dataHistory['tanggal'];
    $pupukTerakhir = $dataHistory['jenis_pupuk'] ?? '-';
    // SESUAIKAN DI SINI: dari 'dosis' menjadi 'jumlah_pupuk'
    $dosisTerakhir = $dataHistory['jumlah_pupuk'] ?? '0'; 
    // SESUAIKAN DI SINI: dari 'cara_aplikasi' menjadi 'metode'
    $caraTerakhir = $dataHistory['metode'] ?? '-'; 
} else {
    $tglTerakhirPupuk = date('Y-m-d');
    $pupukTerakhir = "N/A"; $dosisTerakhir = "0"; $caraTerakhir = "-";
}

$tglObj = new DateTime($tglTerakhirPupuk);
$tglObj->modify('+3 months'); 
$estimasiBerikutnya = $tglObj->format('Y-m-d');
$tglTampilEstimasi = $tglObj->format('d F Y');

$hariIni = new DateTime();
$isLate = ($hariIni > $tglObj); 

$rencanaEksekusi = ($isLate) ? $hariIni->format('d F Y') : $tglTampilEstimasi;
// ======================================================================
// 3. PENGAMBILAN DATA SENSOR BERDASARKAN FILTER LAHAN AKTIF
// ======================================================================
$N = 45; $P = 20; $K = 35;
$ph = 5.5; $suhu = 31; $kelembaban = 55;

if ($selected_lahan_id > 0) {
    // Hilangkan klausa "id_lahan = $selected_lahan_id AND" untuk sementara waktu
    $queryUnsur = mysqli_query($conn, "SELECT AVG(nitrogen) AS n, AVG(fosfor) AS p, AVG(kalium) AS k FROM data_sensor WHERE waktu >= NOW() - INTERVAL $interval");
    $queryLingkungan = mysqli_query($conn, "SELECT AVG(ph_tanah) AS ph, AVG(suhu) AS suhu, AVG(kelembapan) AS kelembapan FROM sensor_data WHERE tanggal >= NOW() - INTERVAL $interval");

    if ($queryUnsur) {
        $unsur = mysqli_fetch_assoc($queryUnsur);
        $N = (!empty($unsur['n'])) ? round($unsur['n'], 2) : 45;
        $P = (!empty($unsur['p'])) ? round($unsur['p'], 2) : 20;
        $K = (!empty($unsur['k'])) ? round($unsur['k'], 2) : 35;
    }
    
    if ($queryLingkungan) {
        $lingkungan = mysqli_fetch_assoc($queryLingkungan);
        $ph = round($lingkungan['ph'] ?? 5.5, 2);
        $suhu = round($lingkungan['suhu'] ?? 31, 2);
        $kelembaban = round($lingkungan['kelembapan'] ?? 55, 2);
    }
}

// ======================================================================
// 4. OVERRIDE MANUAL & LOGIKA AI
// ======================================================================
if (isset($_POST['submit_manual'])) {
    $ph = floatval($_POST['manual_ph']);
    $suhu = floatval($_POST['manual_suhu']);
    $kelembaban = floatval($_POST['manual_kelembapan']);
    $status_data = "Override Manual (Simulasi)";
}

$devSuhu = abs($suhu - $idealSuhu);
$devPh = abs($ph - $idealPh);
$skorStres = (min(1, $devSuhu / 10) * 0.4) + (min(1, $devPh / 2) * 0.6);
$intensitasDosis = 1 + ($skorStres * 0.5); 

$targetN = 120 * $intensitasDosis; 
$targetP = 60 * $intensitasDosis;
$targetK = 150 * $intensitasDosis;

$needN = max(0, $targetN - $N);
$needP = max(0, $targetP - $P);
$needK = max(0, $targetK - $K);

$rekomendasi = [];
if ($needN > 0) $rekomendasi[] = ["label" => "Urea", "val" => round($needN / 0.46, 1), "unit" => "kg/ha", "color" => "#4ade80", "cara" => "Benamkan di rorak sedalam 10cm"];
if ($needP > 0) $rekomendasi[] = ["label" => "SP-36", "val" => round($needP / 0.36, 1), "unit" => "kg/ha", "color" => "#60a5fa", "cara" => "Tabur melingkar di bawah tajuk"];
if ($needK > 0) $rekomendasi[] = ["label" => "KCl", "val" => round($needK / 0.60, 1), "unit" => "kg/ha", "color" => "#fb923c", "cara" => "Aplikasi merata di sekitar piringan"];

$jamPemupukan = ($suhu > 30) ? "Sore (16:30)" : "Pagi (07:00)";
$caraTerbaik = ($ph < 5.5) ? "Tanah Asam! Gunakan Dolomit." : "Kondisi tanah siap serap.";

// Teks Analisis untuk tampilan
$analisis_ph = ($ph < 6.0) ? "pH <b>$ph</b> di bawah standar ideal ($idealPh). AI mewajibkan campuran <b>Kapur Dolomit</b>." : "pH <b>$ph</b> stabil. Tanah dalam kondisi optimal untuk penyerapan unsur hara.";
$analisis_jamur = ($kelembaban > 80) ? "Kelembapan tinggi (<b>$kelembaban%</b>) memicu risiko jamur. Dosis N dikurangi, P & K diperkuat." : "Kelembapan normal. Fokus pada pemenuhan target nutrisi tanaman.";
$analisis_skor = "Tingkat stres tanaman: <b>".round($skorStres*100, 1)."%</b>. Akurasi dosis ditingkatkan <b>".round(($intensitasDosis-1)*100, 1)."%</b>.";

// API Cuaca
$hujan = 0;
$url = "https://api.open-meteo.com/v1/forecast?latitude=-5.52&longitude=104.65&daily=precipitation_probability_max&timezone=Asia%2FJakarta";
$response = @file_get_contents($url);
if ($response) {
    $dataW = json_decode($response, true);
    $hujan = $dataW['daily']['precipitation_probability_max'][0] ?? 0;
}
$statusCuaca = ($hujan > 60) ? "Tunda (Hujan $hujan%)" : "Aman (Hujan $hujan%)";

// JIKA INI ADALAH REQUEST AJAX
if (isset($_GET['ajax'])) {
    echo json_encode([
        'suhu' => $suhu, 'kelembaban' => $kelembaban, 'ph' => $ph,
        'rekomendasi' => $rekomendasi,
        'jamPemupukan' => $jamPemupukan,
        'statusCuaca' => $statusCuaca,
        'hujan' => $hujan,
        'caraTerbaik' => $caraTerbaik,
        'isLate' => $isLate,
        'analisis_ph' => $analisis_ph,
        'analisis_jamur' => $analisis_jamur,
        'analisis_skor' => $analisis_skor,
        'rencanaEksekusi' => $rencanaEksekusi,
        'interval_txt' => ($interval == "1 MINUTE" ? "1 Menit Terakhir" : "14 Hari Terakhir")
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Prediction Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --bg: #070a13; --card-bg: rgba(255, 255, 255, 0.04); --border: rgba(255, 255, 255, 0.08);
            --green: #4ade80; --blue: #60a5fa; --orange: #fb923c; --red: #ef4444;
            --text: #f8fafc; --muted: #94a3b8;
        }
        body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 15px; }
        .container { max-width: 1100px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .status-pill { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; display: flex; align-items: center; gap: 8px; border: 1px solid var(--border); }
        .status-pill.sidang { background: rgba(251, 146, 60, 0.1); color: var(--orange); border-color: var(--orange); }
        
        /* Style Komponen Selector */
        .lahan-bar-wrapper {
            background: var(--card-bg); border: 1px solid var(--border);
            padding: 15px 24px; border-radius: 22px; margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap;
        }
        .selector-control {
            background: #090e1a; border: 1px solid rgba(74, 222, 128, 0.3);
            color: #fff; padding: 10px 16px; border-radius: 12px; font-size: 0.85rem;
            font-weight: 600; outline: none; cursor: pointer; min-width: 220px; transition: 0.3s;
        }
        .selector-control:focus { border-color: var(--green); box-shadow: 0 0 10px rgba(74, 222, 128, 0.15); }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 20px; }
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 24px; padding: 24px; backdrop-filter: blur(12px); }
        .card-title { font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 20px; display: block; letter-spacing: 1px; }
        .manual-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 10px; }
        input { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; padding: 10px; border-radius: 10px; width: 100%; box-sizing: border-box; }
        button { flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; }
        .rec-item { background: rgba(255,255,255,0.03); margin-bottom: 12px; padding: 16px; border-radius: 18px; border-left: 4px solid var(--green); transition: all 0.3s; }
        .alert-late { background: rgba(239, 68, 68, 0.1); border: 1px solid var(--red); padding: 15px; border-radius: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        
        .dss-container { margin-top: 20px; display: flex; flex-direction: column; gap: 12px; }
        .dss-step { display: flex; gap: 15px; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 16px; border: 1px solid var(--border); }
        .step-icon { width: 40px; height: 40px; background: rgba(96, 165, 250, 0.1); color: var(--blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .step-content b { color: var(--blue); }
        .step-label { font-size: 0.6rem; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 4px; display: block; }
        .step-text { font-size: 0.8rem; line-height: 1.4; color: #cbd5e1; }
        .final-decision { margin-top: 10px; padding: 15px; background: linear-gradient(90deg, rgba(74, 222, 128, 0.1), transparent); border-left: 3px solid var(--green); border-radius: 0 16px 16px 0; }

        @media (max-width: 850px) { .dashboard-grid { grid-template-columns: 1fr; .lahan-bar-wrapper { flex-direction: column; align-items: stretch; } .selector-control { width: 100%; } } }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h2 style="font-weight: 800;"><i class='bx bx-bolt-circle'></i> AI Real-Time Analysis</h2>
        <div class="status-pill <?= $SIDANG_MODE ? 'sidang' : 'active' ?>">
            <i class='bx bxs-zap <?= $SIDANG_MODE ? 'bx-flashing' : '' ?>'></i> <?= $status_data ?>
        </div>
    </header>

    <div class="lahan-bar-wrapper">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class='bx bx-landscape' style='font-size: 1.5rem; color: var(--green);'></i>
            <div>
                <h4 style="margin: 0; font-size: 0.9rem; color: #fff;">Sistem Keputusan Lahan</h4>
                <p style="margin: 3px 0 0 0; font-size: 0.75rem; color: var(--muted);">Analisis kebutuhan pupuk AI per petak tanah</p>
            </div>
        </div>
        <div>
            <select id="lahanPredictSelect" class="selector-control" onchange="changeActiveLahan(this.value)">
                <?php if (empty($daftar_lahan)): ?>
                    <option value="0">-- Tidak Ada Lahan --</option>
                <?php else: foreach ($daftar_lahan as $lahan): ?>
                    <option value="<?= $lahan['id']; ?>" <?= ($lahan['id'] == $selected_lahan_id) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($lahan['nama_lahan']); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
    </div>

    <div id="alert-container">
        <?php if ($isLate && $selected_lahan_id > 0): ?>
        <div class="alert-late">
            <i class='bx bxs-error-circle bx-tada'></i>
            <div style="font-size: 0.8rem;">
                <strong style="color: var(--red);">Terdeteksi Keterlambatan!</strong><br>
                User belum memupuk sejak <?= $tglTampilEstimasi ?>. Tanggal eksekusi diperbarui ke hari ini.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-grid">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <span class="card-title">Live Sensor Monitoring</span>
                <div style="height: 230px;"><canvas id="radarHara"></canvas></div>
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 0.8rem; color: var(--muted);">Status Tanah:</span>
                        <span id="txt-caraTerbaik" style="font-size: 0.8rem; color: var(--green); font-weight: 600;"><?= $caraTerbaik ?></span>
                    </div>
                    <div style="background: rgba(74, 222, 128, 0.05); padding: 10px; border-radius: 12px; border: 1px solid rgba(74, 222, 128, 0.1);">
                        <div style="font-size: 0.65rem; color: var(--green); font-weight: 700; margin-bottom: 4px; text-transform: uppercase;">Referensi Ideal Kakao:</div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); font-size: 0.75rem; color: var(--muted);">
                            <span>Suhu: <strong><?= $idealSuhu ?>°C</strong></span>
                            <span>Lembap: <strong><?= $idealLembab ?>%</strong></span>
                            <span>pH: <strong><?= $idealPh ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <span class="card-title">Emergency Override</span>
                <form method="POST" class="manual-form">
                    <div><input type="number" step="0.1" name="manual_ph" id="inp-ph" value="<?= $ph ?>" placeholder="pH"></div>
                    <div><input type="number" step="0.1" name="manual_suhu" id="inp-suhu" value="<?= $suhu ?>" placeholder="Suhu"></div>
                    <div><input type="number" step="1" name="manual_kelembapan" id="inp-lembap" value="<?= $kelembaban ?>" placeholder="Lembab"></div>
                    <div style="display:flex; gap:10px; grid-column: 1/-1; margin-top:10px;">
                        <button type="submit" name="submit_manual" style="background: var(--green); color: #064e3b;">Update AI</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid var(--green);">
            <span class="card-title">Hasil Keputusan & Analisis DSS</span>
            
            <div id="rekomendasi-list">
                <?php if($selected_lahan_id > 0 && !empty($rekomendasi)): foreach($rekomendasi as $r): ?>
                    <div class="rec-item" style="border-left-color: <?= $r['color'] ?>;">
                        <div style="font-size: 0.65rem; color: var(--muted); text-transform: uppercase;"><?= $r['label'] ?></div>
                        <div class="val-display" style="font-size: 1.5rem; font-weight: 800; margin: 5px 0Hash;"><?= $r['val'] ?> <small style="font-weight:400;"><?= $r['unit'] ?></small></div>
                        <div style="font-size: 0.75rem; color: #94a3b8;"><i class='bx bx-info-circle'></i> <?= $r['cara'] ?></div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="rec-item" style="border-left-color: var(--blue);">
                        <div style="font-size: 0.8rem; color: #fff; text-align: center;">Nutrisi tercukupi / Data belum masuk.</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dss-container">
                <div class="dss-step">
                    <div class="step-icon"><i class='bx bx-data'></i></div>
                    <div class="step-content">
                        <span class="step-label">Langkah 1: Pengumpulan Data</span>
                        <div class="step-text" id="dss-1">Menarik tren <b><?= ($interval == "1 MINUTE" ? "1 Menit" : "14 Hari") ?></b> terakhir dari database sensor lapangan.</div>
                    </div>
                </div>

                <div class="dss-step">
                    <div class="step-icon"><i class='bx bx-git-branch'></i></div>
                    <div class="step-content">
                        <span class="step-label">Langkah 2: Logika Agronomi Kakao</span>
                        <div class="step-text" id="dss-2"><?= $analisis_ph ?></div>
                    </div>
                </div>

                <div class="final-decision">
                    <span class="step-label" style="color: var(--green);">Langkah 3: Kesimpulan AI</span>
                    <div class="step-text" id="dss-3" style="color: var(--text); font-weight: 500;">
                        <?= $analisis_jamur ?> <?= $analisis_skor ?>
                    </div>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.02); padding: 20px; border-radius: 20px; margin-top: 20px; border: 1px dashed var(--border);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <i class='bx bx-history' style="color: var(--green); font-size: 1.2rem;"></i>
                    <span style="font-weight: 700; font-size: 0.85rem;">Status Manajemen Kebun</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="font-size: 0.6rem; color: var(--muted); text-transform: uppercase;">Waktu Aplikasi</div>
                        <div id="txt-jamPupuk" style="font-size: 0.9rem; font-weight: 600;"><?= $jamPemupukan ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: var(--muted); text-transform: uppercase;">Status Cuaca</div>
                        <div id="txt-statusCuaca" style="font-size: 0.9rem; font-weight: 600; color: <?= ($hujan > 60) ? 'var(--red)' : 'var(--green)' ?>;"><?= $statusCuaca ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: var(--muted); text-transform: uppercase;">Status Jadwal</div>
                        <div id="txt-statusJadwal" style="font-size: 0.9rem; font-weight: 700; color: <?= $isLate ? 'var(--red)' : 'var(--green)' ?>;"><?= $isLate ? 'TERLAMBAT' : 'TEPAT WAKTU' ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; color: var(--orange); text-transform: uppercase;">Rencana Berikutnya</div>
                        <div id="txt-rencanaBerikutnya" style="font-size: 0.9rem; font-weight: 700; color: var(--orange);"><?= $rencanaEksekusi ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Ambil parameter id lahan aktif sekarang ke JS global
    let currentLahanId = <?= $selected_lahan_id ?>;

    function changeActiveLahan(idLahan) {
        currentLahanId = idLahan;
        updateDashboard(); // Instant update begitu dropdown diganti
    }

    const ctx = document.getElementById('radarHara').getContext('2d');
    const radarChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Suhu', 'Lembab', 'pH'],
            datasets: [
                { label: 'Target Ideal', data: [<?= $idealSuhu ?>, <?= $idealLembab ?>, <?= $idealPh ?>], borderColor: '#4ade80', backgroundColor: 'rgba(74, 222, 128, 0.1)', pointRadius: 0 },
                { label: 'Tren Sensor', data: [<?= $suhu ?>, <?= $kelembaban ?>, <?= $ph ?>], borderColor: '#60a5fa', backgroundColor: 'rgba(96, 165, 250, 0.2)' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            animation: { duration: 500 },
            scales: { r: { angleLines: { display: false }, grid: { color: 'rgba(255,255,255,0.05)' }, suggestedMin: 0, suggestedMax: 100, ticks: { display: false } } },
            plugins: { legend: { labels: { color: '#94a3b8', font: { size: 10 } } } }
        }
    });

    function updateDashboard() {
        if (currentLahanId == 0) return;

        // Menyisipkan parameter query dynamic id_lahan & ajax
        fetch(window.location.pathname + `?id_lahan=${currentLahanId}&ajax=1`)
            .then(res => res.json())
            .then(data => {
                // 1. Update Diagram Radar
                radarChart.data.datasets[1].data = [data.suhu, data.kelembaban, data.ph];
                radarChart.update();

                // 2. Sinkronkan angka input simulasi manual ke sensor baru
                document.getElementById('inp-ph').value = data.ph;
                document.getElementById('inp-suhu').value = data.suhu;
                document.getElementById('inp-lembap').value = data.kelembaban;

                // 3. Update Rekomendasi Pupuk
                let recHtml = '';
                if(data.rekomendasi.length > 0) {
                    data.rekomendasi.forEach(r => {
                        recHtml += `
                            <div class="rec-item" style="border-left-color: ${r.color};">
                                <div style="font-size: 0.65rem; color: var(--muted); text-transform: uppercase;">${r.label}</div>
                                <div style="font-size: 1.5rem; font-weight: 800; margin: 5px 0;">${r.val} <small style="font-weight:400;">${r.unit}</small></div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><i class='bx bx-info-circle'></i> ${r.cara}</div>
                            </div>`;
                    });
                } else {
                    recHtml = `<div class="rec-item" style="border-left-color: var(--blue);"><div style="font-size: 0.8rem; color: #fff; text-align: center;">Nutrisi tercukupi / Data telemetry kosong.</div></div>`;
                }
                document.getElementById('rekomendasi-list').innerHTML = recHtml;

                // 4. Update Logika Stepper DSS Tekstual
                document.getElementById('dss-1').innerHTML = `Menarik tren <b>${data.interval_txt}</b> terakhir dari database sensor lapangan.`;
                document.getElementById('dss-2').innerHTML = data.analisis_ph;
                document.getElementById('dss-3').innerHTML = data.analisis_jamur + " " + data.analisis_skor;
                
                document.getElementById('txt-caraTerbaik').innerText = data.caraTerbaik;
                document.getElementById('txt-jamPupuk').innerText = data.jamPemupukan;
                document.getElementById('txt-rencanaBerikutnya').innerText = data.rencanaEksekusi;
                
                // 5. Update Status Cuaca Pintar
                const sc = document.getElementById('txt-statusCuaca');
                sc.innerText = data.statusCuaca;
                sc.style.color = data.hujan > 60 ? 'var(--red)' : 'var(--green)';

                // 6. Update Peringatan Keterlambatan Real-Time
                const sj = document.getElementById('txt-statusJadwal');
                const alertContainer = document.getElementById('alert-container');
                if(data.isLate) {
                    sj.innerText = 'TERLAMBAT';
                    sj.style.color = 'var(--red)';
                    alertContainer.innerHTML = `
                    <div class="alert-late">
                        <i class='bx bxs-error-circle bx-tada'></i>
                        <div style="font-size: 0.8rem;">
                            <strong style="color: var(--red);">Terdeteksi Keterlambatan!</strong><br>
                            User belum memupuk rincian berkala. Tanggal eksekusi diperbarui ke hari ini.
                        </div>
                    </div>`;
                } else {
                    sj.innerText = 'TEPAT WAKTU';
                    sj.style.color = 'var(--green)';
                    alertContainer.innerHTML = '';
                }
            })
            .catch(e => console.error("Sync Error"));
    }

    // Jalankan pooling otomatisasi pembacaan setiap 5 detik mengikuti setup asal
    setInterval(updateDashboard, 5000);
</script>
</body>
</html>