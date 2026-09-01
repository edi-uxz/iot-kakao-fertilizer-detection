<?php
// koneksi.php
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "db_ai_kakao1"; 

try {
    $db = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    die("Koneksi Database Gagal! Periksa Laragon Anda. Pesan: " . $e->getMessage());
}

if ($db->connect_error) {
    die("Koneksi gagal ke Database {$dbname}: " . $db->connect_error);
}

$db->set_charset("utf8");

// JEMBATAN PERBAIKAN: Menyediakan variabel $koneksi agar kompatibel dengan daftar.php dan login.php
$koneksi = $db; 
?>