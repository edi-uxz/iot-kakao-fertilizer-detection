<?php
// Tentukan halaman aktif untuk memberikan kelas 'active' pada link
// Kita cek parameter 'p' di URL untuk mengetahui halaman aktif
$current_page = isset($_GET['p']) ? $_GET['p'] : 'dashboard'; 

// Path ke root folder (untuk aset, dari pages/ dashboard)
$root_path_for_assets = "assets/"; 

// Base path untuk semua link (karena kita di root/index.php)
$base_path = "index.php?p="; 
?>

<div class="sidebar">
    <div class="logo">
        <img src="<?= $root_path_for_assets ?>images/logo.png" alt="Logo">
        <h2>AgroIntelli</h2>
    </div>
    <ul>
        <li>
            <a href="<?= $base_path ?>dashboard" class="<?= ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <i class='bx bx-home'></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= $base_path ?>data_sensor" class="<?= ($current_page == 'data_sensor') ? 'active' : ''; ?>">
                <i class='bx bx-line-chart'></i> Data Sensor
            </a>
        </li>
        <li>
            <a href="<?= $base_path ?>prediksi_pupuk" class="<?= ($current_page == 'prediksi_pupuk') ? 'active' : ''; ?>">
                <i class='bx bx-leaf'></i> Prediksi Pupuk
            </a>
        </li>
        <li>
            <a href="<?= $base_path ?>laporan" class="<?= ($current_page == 'laporan') ? 'active' : ''; ?>">
                <i class='bx bx-file'></i> Laporan
            </a>
        </li>
        <li>
            <a href="<?= $base_path ?>pengaturan_pupuk" class="<?= ($current_page == 'pengaturan_pupuk') ? 'active' : ''; ?>">
                <i class='bx bx-cog'></i> Pengaturan Pupuk
            </a>
        </li>
        <li>
            <a href="<?= $base_path ?>tentang" class="<?= ($current_page == 'tentang') ? 'active' : ''; ?>">
                <i class='bx bx-user'></i>Tentang
            </a>
        </li>
    </ul>
</div>