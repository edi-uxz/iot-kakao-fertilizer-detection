<?php
// ======================================================
// 1. KONEKSI & LOGIKA DATA AWAL (PHP)
// ======================================================
date_default_timezone_set('Asia/Jakarta');

if (!isset($db) || !$db) {
    @include 'koneksi.php';
}
$conn = $db ?? null;

$id_pengguna = $_SESSION['id_pengguna'] ?? 0;

// Ambil semua daftar lahan milik petani untuk Dropdown
$daftar_lahan = [];
if ($conn) {
    $q_lahan = mysqli_query($conn, "SELECT id, nama_lahan FROM lahan WHERE id_pengguna = $id_pengguna ORDER BY id DESC");
    if ($q_lahan) {
        while ($row = mysqli_fetch_assoc($q_lahan)) {
            $daftar_lahan[] = $row;
        }
    }
}

// Tentukan ID Lahan yang aktif (Default: lahan terbaru atau 0 jika belum punya lahan)
$selected_lahan_id = isset($_GET['id_lahan']) ? (int)$_GET['id_lahan'] : (!empty($daftar_lahan) ? $daftar_lahan[0]['id'] : 0);

$sensorData = []; 
$labels = []; 
$suhu = []; 
$kelembapan = []; 
$ph = [];
$is_online = false; 

if ($conn && $selected_lahan_id > 0) {
    // Ambil 15 data terbaru yang dikirim oleh ESP8266 berdasarkan id_lahan yang dipilih
    // Catatan: Pastikan tabel sensor_data kamu memiliki kolom id_lahan
    $qSensor = mysqli_query($conn, "SELECT tanggal, suhu, kelembapan, ph_tanah FROM sensor_data WHERE id_lahan = $selected_lahan_id ORDER BY id DESC LIMIT 15");
    
    $temp = [];
    if ($qSensor) {
        while ($row = mysqli_fetch_assoc($qSensor)) { 
            $temp[] = $row; 
        }
    }
    
    if (!empty($temp)) {
        $last_data_time = strtotime($temp[0]['tanggal']); 
        $current_time = time();
        $diff = $current_time - $last_data_time;

        // Jika data masuk kurang dari 45 detik yang lalu, anggap alat online
        if ($diff >= 0 && $diff <= 45) {
            $is_online = true;
        }
    }

    // Balik urutan data agar sesuai dengan timeline grafik (kiri ke kanan)
    $sensorData = array_reverse($temp);
    foreach ($sensorData as $row) {
        $labels[] = date('H:i', strtotime($row['tanggal']));
        $suhu[] = (float)$row['suhu'];
        $kelembapan[] = (float)$row['kelembapan'];
        $ph[] = (float)$row['ph_tanah'];
    }
}

// Proteksi Chart.js jika data kosong
if (empty($labels)) {
    $labels = ['--:--']; $suhu = [0]; $kelembapan = [0]; $ph = [0];
}
?>

<style>
    .live-container { display: flex; flex-direction: column; gap: 15px; width: 100%; }
    
    /* Style komponen selector lahan */
    .lahan-selector-wrapper {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 15px 20px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }
    .select-lahan-control {
        background: #0f1c18;
        border: 1px solid rgba(46, 204, 113, 0.3);
        color: #fff;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        outline: none;
        cursor: pointer;
        min-width: 200px;
        transition: 0.3s;
    }
    .select-lahan-control:focus {
        border-color: var(--accent-green);
        box-shadow: 0 0 10px rgba(46, 204, 113, 0.2);
    }

    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .kpi-card { 
        background: rgba(255, 255, 255, 0.03); 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        border-radius: 18px; padding: 15px 10px; text-align: center; backdrop-filter: blur(10px); 
    }
    .kpi-card small { display: block; color: #94a3b8; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px; }
    .kpi-card .value { font-size: 1.2rem; font-weight: 800; color: #fff; }
    .kpi-card .unit { font-size: 0.75rem; font-weight: 400; color: #64748b; }

    .content-box { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 22px; padding: 18px; }
    .content-box h3 { font-size: 0.9rem; margin-bottom: 15px; color: #fff; display: flex; align-items: center; gap: 8px; }
    
    .scroll-area { width: 100%; overflow-x: auto; }
    .mobile-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .mobile-table th { text-align: left; padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #4ade80; }
    .mobile-table td { padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.02); color: #94a3b8; }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(74, 222, 128, 0); }
        100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
    }

    @media (max-width: 600px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .kpi-card { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; }
        .lahan-selector-wrapper { flex-direction: column; align-items: stretch; }
        .select-lahan-control { width: 100%; }
    }
</style>

<div class="live-container">
    
    <div class="lahan-selector-wrapper">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class='bx bx-map-alt' style="font-size: 1.4rem; color: #4ade80;"></i>
            <div>
                <h4 style="font-size: 0.9rem; color: #fff; font-weight: 600;">Lokasi Monitor Lahan</h4>
                <p style="font-size: 0.75rem; color: #64748b;">Pilih area perkebunan Kakao yang ingin diamati</p>
            </div>
        </div>
        <div>
            <select id="lahanSelect" class="select-lahan-control" onchange="switchLahan(this.value)">
                <?php if (empty($daftar_lahan)): ?>
                    <option value="0">-- Belum Ada Lahan --</option>
                <?php else: foreach ($daftar_lahan as $lahan): ?>
                    <option value="<?= $lahan['id']; ?>" <?= ($lahan['id'] == $selected_lahan_id) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($lahan['nama_lahan']); ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #fff;">Telemetri Sensor <span style="color:#4ade80;">Live</span></h2>
        
        <div id="status-container">
            <?php if ($is_online): ?>
                <div style="display: flex; align-items: center; gap: 8px; background: rgba(74, 222, 128, 0.1); padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2);">
                    <div style="width: 7px; height: 7px; background: #4ade80; border-radius: 50%; animation: pulse-green 2s infinite;"></div> 
                    ESP8266 Terhubung
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; gap: 8px; background: rgba(239, 68, 68, 0.1); padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <div style="width: 7px; height: 7px; background: #ef4444; border-radius: 50%;"></div> 
                    ESP8266 Terputus
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="border-bottom: 3px solid #fb923c;">
            <small>Suhu</small>
            <div class="value"><span id="val-suhu"><?= end($suhu) ?></span> <span class="unit">°C</span></div>
        </div>
        <div class="kpi-card" style="border-bottom: 3px solid #60a5fa;">
            <small>Lembap</small>
            <div class="value"><span id="val-lembap"><?= end($kelembapan) ?></span> <span class="unit">%</span></div>
        </div>
        <div class="kpi-card" style="border-bottom: 3px solid #c084fc;">
            <small>pH Tanah</small>
            <div class="value"><span id="val-ph"><?= end($ph) ?></span></div>
        </div>
    </div>

    <div class="content-box">
        <h3><i class="bx bx-chart" style="color:#60a5fa;"></i> Grafik Real-time</h3>
        <div style="height: 200px; width: 100%;">
            <canvas id="chartLive"></canvas>
        </div>
    </div>

    <div class="content-box">
        <h3><i class="bx bx-list-ul" style="color:#4ade80;"></i> Log Data Terakhir</h3>
        <div class="scroll-area">
            <table class="mobile-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu</th>
                        <th>Hmd</th>
                        <th>pH</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if(!empty($temp) && $selected_lahan_id > 0): foreach ($temp as $row): ?>
                    <tr>
                        <td><?= date('H:i:s', strtotime($row['tanggal'])) ?></td>
                        <td style="color:#fb923c"><?= $row['suhu'] ?>°C</td>
                        <td style="color:#60a5fa"><?= $row['kelembapan'] ?>%</td>
                        <td style="color:#c084fc"><?= $row['ph_tanah'] ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align:center;">Belum ada data telemetry untuk lahan ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Ambil variabel ID Lahan aktif saat ini ke JavaScript
let currentLahanId = <?= $selected_lahan_id ?>;

// Fungsi pemicu saat petani merubah opsi dropdown lahan
function switchLahan(idLahan) {
    currentLahanId = idLahan;
    // Lakukan instant fetch data baru agar petani tidak menunggu interval 3 detik berjalan
    fetchSensorUpdate();
}

document.addEventListener("DOMContentLoaded", function() {
    const ctxLive = document.getElementById('chartLive').getContext('2d');
    
    // 1. Inisialisasi Chart dengan data awal PHP
    const liveChart = new Chart(ctxLive, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Suhu',
                    data: <?= json_encode($suhu) ?>,
                    borderColor: '#fb923c',
                    backgroundColor: 'rgba(251, 146, 60, 0.1)',
                    borderWidth: 2, tension: 0.4, pointRadius: 2, fill: true
                },
                {
                    label: 'Lembap',
                    data: <?= json_encode($kelembapan) ?>,
                    borderColor: '#60a5fa',
                    backgroundColor: 'rgba(96, 165, 250, 0.1)',
                    borderWidth: 2, tension: 0.4, pointRadius: 2, fill: true
                },
                {
                    label: 'pH Tanah',
                    data: <?= json_encode($ph) ?>,
                    borderColor: '#c084fc',
                    backgroundColor: 'rgba(192, 132, 252, 0.1)',
                    borderWidth: 2, tension: 0.4, pointRadius: 2, fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#64748b', font: { size: 9 } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 9 } } }
            }
        }
    });

    // 2. Fungsi Utama Update Data via API dengan Filter id_lahan
    function fetchSensorUpdate() {
        if(currentLahanId == 0) return;

        // Mengirimkan parameter id_lahan ke API backend pembaca sensor
        fetch(`api/sensor_live.php?id_lahan=${currentLahanId}`)
            .then(res => res.json())
            .then(data => {
                if (data.sensor && data.sensor.length > 0) {
                    const latest = data.sensor[data.sensor.length - 1];

                    // Update Angka KPI
                    document.getElementById('val-suhu').innerText = latest.suhu;
                    document.getElementById('val-lembap').innerText = latest.kelembapan;
                    document.getElementById('val-ph').innerText = latest.ph;

                    // Update Tabel (Urutan DESC)
                    let rows = '';
                    const logData = [...data.sensor].reverse();
                    logData.forEach(row => {
                        rows += `<tr>
                            <td>${row.waktu}</td>
                            <td style="color:#fb923c">${row.suhu}°C</td>
                            <td style="color:#60a5fa">${row.kelembapan}%</td>
                            <td style="color:#c084fc">${row.ph}</td>
                        </tr>`;
                    });
                    document.getElementById('tableBody').innerHTML = rows;

                    // Update Grafik
                    liveChart.data.labels = data.sensor.map(d => d.waktu);
                    liveChart.data.datasets[0].data = data.sensor.map(d => d.suhu);
                    liveChart.data.datasets[1].data = data.sensor.map(d => d.kelembapan);
                    liveChart.data.datasets[2].data = data.sensor.map(d => d.ph);
                    liveChart.update('none');

                    // Update Status Koneksi Perangkat IoT
                    const diff = Math.floor(Date.now() / 1000) - latest.timestamp;
                    const statusContainer = document.getElementById('status-container');
                    if (diff <= 45) {
                        statusContainer.innerHTML = `<div style="display: flex; align-items: center; gap: 8px; background: rgba(74, 222, 128, 0.1); padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2);"><div style="width: 7px; height: 7px; background: #4ade80; border-radius: 50%; animation: pulse-green 2s infinite;"></div> ESP8266 Terhubung </div>`;
                    } else {
                        statusContainer.innerHTML = `<div style="display: flex; align-items: center; gap: 8px; background: rgba(239, 68, 68, 0.1); padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);"><div style="width: 7px; height: 7px; background: #ef4444; border-radius: 50%;"></div> ESP8266 Terputus </div>`;
                    }
                } else {
                    // Jika data sensor di lahan terpilih kosong
                    document.getElementById('val-suhu').innerText = "0";
                    document.getElementById('val-lembap').innerText = "0";
                    document.getElementById('val-ph').innerText = "0";
                    document.getElementById('tableBody').innerHTML = '<tr><td colspan="4" style="text-align:center;">Belum ada data telemetry untuk lahan ini.</td></tr>';
                    
                    liveChart.data.labels = ['--:--'];
                    liveChart.data.datasets.forEach(dataset => dataset.data = [0]);
                    liveChart.update('none');
                    
                    document.getElementById('status-container').innerHTML = `<div style="display: flex; align-items: center; gap: 8px; background: rgba(239, 68, 68, 0.1); padding: 5px 12px; border-radius: 50px; font-size: 0.7rem; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);"><div style="width: 7px; height: 7px; background: #ef4444; border-radius: 50%;"></div> Perangkat Belum Aktif </div>`;
                }
            })
            .catch(e => console.log("API Sync Error"));
    }

    window.fetchSensorUpdate = fetchSensorUpdate;

    // Jalankan pooling update otomatis setiap 3 detik
    setInterval(fetchSensorUpdate, 3000);
});
</script>