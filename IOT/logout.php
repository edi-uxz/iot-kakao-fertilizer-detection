<?php
// logout.php
session_start();

// 1. Bersihkan semua variabel session
$_SESSION = array();

// 2. Hancurkan session yang tersimpan di server
session_destroy();

// 3. Tendang kembali ke halaman utama (Landing Page)
header("Location: index.php");
exit;
?>