<?php
if (!isset($db) || !$db) {
    @include 'koneksi.php'; 
}

if (!$db) {
    $db = mysqli_connect("localhost", "root", "", "db_ai_kakao1");
}

if (!$db) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 1. Tangkap parameter api_token dan data sensor dari ESP
// Gunakan isset untuk mendeteksi keberadaan parameter, jika tidak ada set ke string kosong atau null
$api_token        = isset($_GET['api_token']) ? mysqli_real_escape_string($db, $_GET['api_token']) : '';
$suhu             = isset($_GET['suhu']) ? mysqli_real_escape_string($db, $_GET['suhu']) : null;
$kelembapan_udara = isset($_GET['kelembapan']) ? mysqli_real_escape_string($db, $_GET['kelembapan']) : null;
$ph_tanah         = isset($_GET['ph']) ? mysqli_real_escape_string($db, $_GET['ph']) : null;
$kelembapan_tanah = isset($_GET['kelembapan_tanah']) ? mysqli_real_escape_string($db, $_GET['kelembapan_tanah']) : null;
$cahaya           = isset($_GET['cahaya']) ? mysqli_real_escape_string($db, $_GET['cahaya']) : null;

// 2. Validasi apakah ESP mengirimkan api_token
if (!empty($api_token)) {
    
    // 3. Cari id lahan di tabel 'lahan' berdasarkan api_token yang dikirim
    $cek_lahan = mysqli_query($db, "SELECT id FROM lahan WHERE api_token = '$api_token' LIMIT 1");
    
    if (mysqli_num_rows($cek_lahan) > 0) {
        $data_lahan = mysqli_fetch_assoc($cek_lahan);
        $id_lahan   = $data_lahan['id']; // ID Lahan didapatkan secara otomatis!
        
        // 4. Masukkan data ke tabel sensor_data memakai id_lahan hasil pencarian
        // Membungkus nilai dengan '$suhu' jauh lebih aman dari resiko patahnya syntax SQL
        $query = "INSERT INTO sensor_data (id_lahan, suhu, kelembapan, ph_tanah, kelembapan_tanah, cahaya) 
                  VALUES (
                    '$id_lahan', 
                    '$suhu', 
                    '$kelembapan_udara', 
                    '$ph_tanah', 
                    '$kelembapan_tanah', 
                    '$cahaya'
                  )";
                  
        if (mysqli_query($db, $query)) {
            echo "BERHASIL: Data telemetry berhasil disimpan untuk Lahan ID: " . $id_lahan;
        } else {
            echo "GAGAL SQL: " . mysqli_error($db);
        }
        
    } else {
        echo "GAGAL: Kode Perangkat (api_token) tidak terdaftar pada lahan manapun.";
    }
} else {
    echo "GAGAL: Parameter api_token tidak ditemukan pada request.";
}

mysqli_close($db);
?>