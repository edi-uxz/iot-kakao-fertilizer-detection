<?php
include '../koneksi.php';
header('Content-Type: application/json');

$response = [
    'sensor' => [],
    'unsur'  => []
];

// SENSOR DATA TERBARU
$q = mysqli_query($db, "
    SELECT tanggal, suhu, kelembapan, ph_tanah
    FROM sensor_data
    ORDER BY tanggal DESC
    LIMIT 30
");

$tmp = [];
while ($r = mysqli_fetch_assoc($q)) {
    $tmp[] = [
        'waktu' => date('H:i', strtotime($r['tanggal'])),
        'suhu' => (float)$r['suhu'],
        'kelembapan' => (float)$r['kelembapan'],
        'ph' => (float)$r['ph_tanah']
    ];
}
$response['sensor'] = array_reverse($tmp);

// UNSUR HARA TERAKHIR
$q2 = mysqli_query($db, "
    SELECT nitrogen, fosfor, kalium, kalsium, waktu
    FROM data_sensor
    ORDER BY waktu DESC
    LIMIT 1
");

if ($u = mysqli_fetch_assoc($q2)) {
    $response['unsur'] = $u;
}

echo json_encode($response);
