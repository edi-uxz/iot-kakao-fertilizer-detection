<?php
// user/pages/lahan.php

// Pastikan variabel jembatan database $db tersedia
if (!isset($db) || !$db) {
    @include 'koneksi.php'; 
}

// Mengunci data berdasarkan session user yang aktif login
$id_pengguna = $_SESSION['id_pengguna'];
$sukses = $error = "";

// PROSES SIMPAN LAHAN BARU SINKRON DATABASE ASLI
if (isset($_POST['tambah_lahan'])) {
    $nama_lahan      = mysqli_real_escape_string($db, $_POST['nama_lahan']);
    $panjang_atas   = (float)$_POST['panjang_atas'];
    $panjang_bawah  = (float)$_POST['panjang_bawah'];
    $sisi_kiri      = (float)$_POST['sisi_kiri'];
    $sisi_kanan     = (float)$_POST['sisi_kanan'];
    $kondisi        = mysqli_real_escape_string($db, $_POST['kondisi']);
    $jarak_pemukiman= mysqli_real_escape_string($db, $_POST['jarak_pemukiman']); 
    
    // FORMAT TOKEN SIMPEL: Cek jumlah lahan user saat ini untuk membuat penomoran urut (lahan1, lahan2, dst.)
    $q_hitung = mysqli_query($db, "SELECT COUNT(*) as total FROM lahan WHERE id_pengguna = $id_pengguna");
    $row_hitung = mysqli_fetch_assoc($q_hitung);
    $nomor_lahan_baru = $row_hitung['total'] + 1;
    
    // Menghasilkan token simpel seperti: "lahan1", "lahan2", dst.
    $api_token = "lahan" . $nomor_lahan_baru; 

    // Menghitung Luas Lahan Pendekatan Trapesium
    $tinggi_pendekatan = ($sisi_kiri + $sisi_kanan) / 2;
    $luas_lahan = (($panjang_atas + $panjang_bawah) / 2) * $tinggi_pendekatan;

    // Logika Kalkulasi Jumlah & Rekomendasi Sensor Berdasarkan Luas dan Kontur
    $faktor_kontur = ($kondisi == 'bukit' || $kondisi == 'miring') ? 250 : 400;
    $jumlah_sensor = ceil($luas_lahan / $faktor_kontur);
    $rekomendasi_sensor = $jumlah_sensor * 2; 

    if (!empty($nama_lahan) && $luas_lahan > 0) {
        $q_insert = "INSERT INTO lahan (id_pengguna, nama_lahan, luas_lahan, kondisi, panjang_atas, panjang_bawah, sisi_kiri, sisi_kanan, jumlah_sensor, rekomendasi_sensor, jenis_tanaman, lokasi, api_token) 
                     VALUES ($id_pengguna, '$nama_lahan', $luas_lahan, '$kondisi', $panjang_atas, $panjang_bawah, $sisi_kiri, $sisi_kanan, $jumlah_sensor, $rekomendasi_sensor, 'Kakao', '$jarak_pemukiman', '$api_token')";
        
        if (mysqli_query($db, $q_insert)) {
            $sukses = "Lahan baru berhasil didaftarkan dan token IoT simpel telah diterbitkan!";
        } else {
            $error = "Gagal menyimpan data ke database: " . mysqli_error($db);
        }
    } else {
        $error = "Semua bidang formulir wajib diisi dengan benar.";
    }
}

// MEMASTIKAN HANYA MENGAMBIL DAFTAR LAHAN MILIK USER YANG SEDANG LOGIN
$daftar_lahan = [];
$q_lahan = mysqli_query($db, "SELECT * FROM lahan WHERE id_pengguna = $id_pengguna ORDER BY id DESC");
if ($q_lahan) {
    while ($row = mysqli_fetch_assoc($q_lahan)) {
        $daftar_lahan[] = $row;
    }
}
?>

<style>
    .grid-lahan { display: grid; grid-template-columns: repeat(12, 1fr); gap: 25px; }
    .form-box { grid-column: span 5; }
    .list-box { grid-column: span 7; }
    
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; color: #888; font-size: 0.85rem; margin-bottom: 8px; }
    .form-control { 
        width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); 
        padding: 12px 16px; border-radius: 12px; color: #fff; font-size: 0.9rem; transition: 0.3s;
    }
    .form-control:focus { border-color: var(--accent-green); outline: none; background: rgba(46, 204, 113, 0.02); }
    
    .btn-submit {
        width: 100%; background: linear-gradient(135deg, var(--accent-green), #27ae60); 
        color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s;
    }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
    
    .alert { padding: 15px; border-radius: 12px; font-size: 0.85rem; margin-bottom: 20px; }
    .alert-success { background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.2); color: #2ecc71; }
    .alert-error { background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); color: #e74c3c; }

    .lahan-card { 
        background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.04); 
        border-radius: 18px; padding: 20px; margin-bottom: 15px; 
    }
    .badge-geo { background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; color: #aaa; margin-left: 5px; }
    .ai-recom-box { 
        background: rgba(52, 152, 219, 0.05); border-left: 3px solid #3498db; 
        padding: 12px; border-radius: 4px 12px 12px 4px; margin-top: 15px; font-size: 0.8rem;
    }
    
    /* Wrapper Token & Tombol Salin */
    .token-container { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
    .token-box {
        background: rgba(241, 196, 15, 0.08); border: 1px dashed rgba(241, 196, 15, 0.3);
        padding: 8px 14px; border-radius: 8px; font-family: monospace; color: #f1c40f; font-size: 0.95rem; font-weight: bold; letter-spacing: 0.5px;
    }
    .btn-copy {
        background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff; padding: 8px 12px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-size: 0.75rem; transition: 0.2s;
    }
    .btn-copy:hover { background: rgba(241, 196, 15, 0.15); border-color: #f1c40f; color: #f1c40f; }

    @media (max-width: 992px) { .form-box, .list-box { grid-column: span 12; } }
</style>

<div style="margin-bottom: 25px;">
    <h2 style="color: #fff; font-weight: 800; font-size: 1.4rem;">Registrasi & Pemetaan Lahan</h2>
    <p style="color: #666; font-size: 0.85rem;">Daftarkan dimensi lahan Anda untuk mendapatkan analisis kebutuhan sensor serta API Token enkripsi perangkat IoT.</p>
</div>

<?php if($sukses): ?> <div class="alert alert-success"><i class='bx bx-check-circle'></i> <?= $sukses ?></div> <?php endif; ?>
<?php if($error): ?> <div class="alert alert-error"><i class='bx bx-error-circle'></i> <?= $error ?></div> <?php endif; ?>

<div class="grid-lahan">
    <!-- Form Registrasi Lahan Baru -->
    <div class="bento-card form-box">
        <h3 style="color: #fff; font-size: 1rem; margin-bottom: 20px;"><i class='bx bx-plus-circle' style="color: var(--accent-green);"></i> Petakan Lahan Baru</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Nama / Label Lahan</label>
                <input type="text" name="nama_lahan" class="form-control" placeholder="Contoh: Lahan Utama Sukorejo" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Panjang Atas (Meter)</label>
                    <input type="number" step="0.1" name="panjang_atas" class="form-control" placeholder="0.0" required>
                </div>
                <div class="form-group">
                    <label>Panjang Bawah (Meter)</label>
                    <input type="number" step="0.1" name="panjang_bawah" class="form-control" placeholder="0.0" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Sisi Kiri (Meter)</label>
                    <input type="number" step="0.1" name="sisi_kiri" class="form-control" placeholder="0.0" required>
                </div>
                <div class="form-group">
                    <label>Sisi Kanan (Meter)</label>
                    <input type="number" step="0.1" name="sisi_kanan" class="form-control" placeholder="0.0" required>
                </div>
            </div>

            <div class="form-group">
                <label>Kondisi Geografis / Kontur Lahan</label>
                <select name="kondisi" class="form-control">
                    <option value="datar">Wilayah Datar / Landai</option>
                    <option value="miring">Area Lereng Miring</option>
                    <option value="bukit">Perbukitan / Atas Gunung</option>
                    <option value="dekat_kali">Dekat Aliran Sungai / Kali</option>
                </select>
            </div>

            <div class="form-group">
                <label>Sumber Energi Terdekat</label>
                <select name="jarak_pemukiman" class="form-control">
                    <option value="dekat">Dekat Pemukiman (Tersedia Jaringan Listrik AC)</option>
                    <option value="jauh">Jauh dari Pemukiman (Butuh Energi Surya / Mandiri)</option>
                </select>
            </div>

            <button type="submit" name="tambah_lahan" class="btn-submit"><i class='bx bx-analyse'></i> Simpan & Terbitkan Token</button>
        </form>
    </div>

    <!-- Panel Informasi & Token Perangkat Pintar -->
    <div class="bento-card list-box">
        <h3 style="color: #fff; font-size: 1rem; margin-bottom: 20px;"><i class='bx bx-map-alt' style="color: #3498db;"></i> Konfigurasi Hardware & Lahan Anda</h3>
        
        <?php if(empty($daftar_lahan)): ?>
            <p style="color: #555; font-size: 0.85rem; text-align: center; padding: 40px 0;">Anda belum memiliki riwayat pemetaan lahan.</p>
        <?php else: foreach($daftar_lahan as $l): 
            
            if($l['lokasi'] == 'jauh') {
                $energi = "Solar Panel 10 Watt + Pengisi Baterai TP4056 + Sel Baterai Lithium 18650.";
            } else {
                $energi = "Power Supply Adaptor 5V DC langsung dari stopkontak area terdekat.";
            }
            
            $pemasangan = "Standar. Tempatkan modul ESP8266 pada tiang PVC 1.5 meter di tengah area tanaman Kakao.";
            if($l['kondisi'] == 'miring' || $l['kondisi'] == 'bukit') {
                $pemasangan = "Sinyal radio rentan terhalang lekukan tanah. Wajib posisikan tiang antena mikrokontroler lebih tinggi (minimal 3 meter) di titik puncak elevasi tanah.";
            } elseif($l['kondisi'] == 'dekat_kali') {
                $pemasangan = "Tingkat kelembapan udara sangat tinggi dan rawan luapan air. Wajib gunakan Box panel luar ruangan IP65 (Waterproof tertutup karet) demi keamanan sirkuit ESP8266.";
            }
            
            // Definisikan token aman dari null data lama
            $clean_token = !empty($l['api_token']) ? htmlspecialchars($l['api_token']) : 'lahan_none';
        ?>
            <div class="lahan-card">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h4 style="color: #fff; font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($l['nama_lahan']) ?></h4>
                        <p style="color: #777; font-size: 0.8rem; margin-top: 4px;">
                            Batas Sisi: <span style="color: #aaa;"><?= $l['panjang_atas'] ?>m x <?= $l['panjang_bawah'] ?>m | Samping: <?= $l['sisi_kiri'] ?>m x <?= $l['sisi_kanan'] ?>m</span>
                        </p>
                        <p style="color: #666; font-size: 0.8rem; margin-top: 2px;">
                            Luas Kalkulasi: <span style="color: var(--accent-green); font-weight: 600;"><?= number_format($l['luas_lahan'], 1) ?> m²</span>
                        </p>
                    </div>
                    <div style="display: flex;">
                        <span class="badge-geo">📍 Kontur: <?= ucfirst($l['kondisi']) ?></span>
                    </div>
                </div>

                <div class="ai-recom-box">
                    <strong style="color: #3498db; display: block; margin-bottom: 5px;"><i class='bx bx-chip'></i> Integrasi Telemetri Perangkat IoT:</strong>
                    <table style="width:100%; border-collapse: collapse; margin-top: 5px; color: #bbb;">
                        
                        <!-- BARIS API TOKEN SIMPEL DENGAN TOMBOL SALIN -->
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                            <td style="padding: 8px 0; width: 35%; vertical-align: top; font-weight: 600; color: #fff;">API Device Token:</td>
                            <td style="padding: 8px 0;">
                                <div class="token-container">
                                    <div class="token-box" id="token_<?= $l['id'] ?>"><?= $clean_token ?></div>
                                    <button class="btn-copy" onclick="copyToken('<?= $clean_token ?>', this)">
                                        <i class='bx bx-copy'></i> <span>Salin</span>
                                    </button>
                                </div>
                                <span style="display:block; font-size:0.75rem; color:#f1c40f; margin-top:8px; line-height: 1.4;">
                                    💡 <b>Petunjuk Aktivasi Perangkat Awam:</b><br>
                                    Klik tombol <b>Salin</b> di atas. Sambungkan HP Anda ke Wi-Fi alat (<b>AgroIntelli-Device</b>), lalu tempel (<i>paste</i>) token ini langsung di kolom pengaturan Wi-Fi HP Anda.
                                </span>
                            </td>
                        </tr>
                        
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                            <td style="padding: 6px 0;">Kebutuhan Node:</td>
                            <td style="color: #fff; font-weight: 500;">⚡ Minimal <?= $l['jumlah_sensor'] ?> Perangkat IoT (Rekomendasi Utama: <?= $l['rekomendasi_sensor'] ?> titik)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                            <td style="padding: 6px 0;">Sistem Suplai Daya:</td>
                            <td style="color: #e67e22; font-size: 0.78rem;"><?= $energi ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; vertical-align: top;">Saran Peletakan Alat:</td>
                            <td style="color: #2ecc71; line-height: 1.4; font-size: 0.78rem;"><?= $pemasangan ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- JAVASCRIPT COPYSCRIPT INTERAKTIF -->
<script>
function copyToken(text, button) {
    navigator.clipboard.writeText(text).then(function() {
        // Mengubah teks tombol secara sementara saat berhasil menyalin
        const textSpan = button.querySelector('span');
        const icon = button.querySelector('i');
        
        button.style.background = "rgba(46, 204, 113, 0.2)";
        button.style.borderColor = "#2ecc71";
        button.style.color = "#2ecc71";
        textSpan.textContent = "Tersalin!";
        icon.className = 'bx bx-check';
        
        // Kembalikan ke desain awal setelah 2 detik
        setTimeout(function() {
            button.style.background = "rgba(255, 255, 255, 0.05)";
            button.style.borderColor = rgba(255, 255, 255, 0.1);
            button.style.color = "#fff";
            textSpan.textContent = "Salin";
            icon.className = 'bx bx-copy';
        }, 2000);
    }, function(err) {
        console.error('Gagal menyalin token: ', err);
    });
}
</script>