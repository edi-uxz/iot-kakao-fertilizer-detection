<?php
// user/index.php
ob_start();
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// PROTEKSI AKSES: Hanya user/petani yang sudah login yang boleh masuk halaman ini
if (!isset($_SESSION['id_pengguna']) || $_SESSION['role'] === 'admin') {
    header("Location: ../login.php");
    exit;
}

// ROUTING LOGIC (Sudah ditambahkan halaman 'lahan')
$page = isset($_GET['p']) ? $_GET['p'] : 'dashboard';
$allowed_pages = ['dashboard', 'data_sensor', 'prediksi_pupuk', 'laporan', 'tentang', 'pengaturan_pupuk', 'lahan'];
$content_file = (in_array($page, $allowed_pages) && file_exists("pages/$page.php")) ? "pages/$page.php" : "pages/dashboard.php";

// Memanggil koneksi database
require_once '../koneksi.php';

// JEMBATAN FIX ERROR: Menyediakan $db agar file di dalam folder pages/ tidak error Undefined Variable
global $koneksi;
$db = $koneksi; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AgroIntelli Pro | Smart Control</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-body: #050b0a;
            --sidebar-bg: rgba(10, 20, 18, 0.98);
            --accent-green: #2ecc71;
            --accent-red: #e74c3c;
            --text-main: #e0e0e0;
            --glass-border: rgba(255, 255, 255, 0.05);
            --card-glass: rgba(0, 0, 0, 0.4);
            --sidebar-width: 280px;
        }

        /* RESET TOTAL */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        html, body {
            height: 100%;
            background: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {
            display: flex;
            background-image: radial-gradient(circle at top right, #0d1b18, #050b0a);
        }

        /* ======================================================
           KUSTOMISASI SCROLLBAR (Menghilangkan Tampilan Mengganggu)
           ====================================================== */
        /* Untuk Windows/Chrome/Safari/Edge */
        ::-webkit-scrollbar {
            width: 6px; /* Sangat tipis untuk scroll vertikal */
            height: 6px; /* Sangat tipis untuk scroll horizontal */
        }
        
        /* Area rel tempat scrollbar berjalan */
        ::-webkit-scrollbar-track {
            background: transparent; 
        }
        
        /* Batang penunjuk scrollbar */
        ::-webkit-scrollbar-thumb {
            background: rgba(46, 204, 113, 0.1); /* Hijau transparan super redup */
            border-radius: 10px;
            transition: background 0.3s;
        }
        
        /* Efek saat scrollbar disentuh atau digeser */
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(46, 204, 113, 0.4); /* Hijau terlihat lebih jelas saat aktif */
        }

        /* Dukungan Khusus Firefox */
        html {
            scrollbar-width: thin;
            scrollbar-color: rgba(46, 204, 113, 0.1) transparent;
        }

        /* --- SIDEBAR RE-STRUCTURE --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 2000;
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto; 
        }

        .logo-area { padding: 30px; display: flex; align-items: center; gap: 15px; }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent-green), #27ae60);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-area h2 { font-size: 1.2rem; font-weight: 800; color: #fff; }

        .nav-menu { list-style: none; padding: 0 15px; flex-grow: 1; }
        .nav-item { margin-bottom: 5px; }
        .nav-link {
            display: flex; align-items: center; padding: 12px 20px;
            color: #888; text-decoration: none; border-radius: 14px; transition: 0.3s;
        }
        .nav-link i { font-size: 1.3rem; margin-right: 12px; }
        .nav-link:hover, .nav-link.active {
            color: var(--accent-green);
            background: rgba(46, 204, 113, 0.1);
        }
        .nav-link.active { font-weight: 600; color: #fff; }

        /* Logout Link Specific Style */
        .nav-link-logout {
            color: #b33939;
        }
        .nav-link-logout:hover {
            color: var(--accent-red) !important;
            background: rgba(231, 76, 60, 0.1) !important;
        }

        /* --- MAIN CONTENT AREA --- */
        .main-container {
            flex: 1;
            margin-left: var(--sidebar-width); 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px 30px;
            width: 100%;
        }

        .page-content {
            flex: 1;
            background: var(--card-glass);
            border-radius: 24px;
            padding: 25px;
            border: 1px solid var(--glass-border);
            margin-bottom: 20px;
        }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-container { margin-left: 0; padding: 15px; padding-top: 80px; }
            footer { padding-bottom: 80px; }
        }

        /* --- MENU BUTTON --- */
        #menuBtnBox { position: fixed; top: 20px; right: 20px; z-index: 3000; }
        #menuBtn {
            background: var(--accent-green);
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        @media (min-width: 1025px) { #menuBtnBox { display: none; } }

        #rotate-warning {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-body); z-index: 9999; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
        }
        @media screen and (max-width: 600px) and (orientation: portrait) {
            #rotate-warning { display: flex; }
        }
    </style>
</head>
<body>

    <div id="rotate-warning">
        <i class='bx bx-rotate-right' style="font-size: 4rem; color: var(--accent-green);"></i>
        <h2 style="margin-top: 15px;">Miringkan HP Anda</h2>
        <p style="color: #666;">Gunakan mode landscape agar tampilan rapi.</p>
    </div>

    <div id="menuBtnBox">
        <div id="menuBtn">
            <i class='bx bx-menu-alt-right' style="color: white; font-size: 1.8rem;"></i>
        </div>
    </div>

    <aside class="sidebar" id="navSidebar">
        <div class="logo-area">
            <div class="logo-icon"><i class='bx bxs-leaf' style="color: white;"></i></div>
            <h2>AgroIntelli<span style="color: var(--accent-green);">.</span></h2>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?p=dashboard" class="nav-link <?= ($page == 'dashboard') ? 'active' : ''; ?>"><i class='bx bxs-grid-alt'></i> Dashboard</a></li>
            
            <li class="nav-item"><a href="index.php?p=lahan" class="nav-link <?= ($page == 'lahan') ? 'active' : ''; ?>"><i class='bx bx-map-alt'></i> Lahan Perkebunan</a></li>
            
            <li class="nav-item"><a href="index.php?p=data_sensor" class="nav-link <?= ($page == 'data_sensor') ? 'active' : ''; ?>"><i class='bx bx-radar'></i> Live Sensor</a></li>
            <li class="nav-item"><a href="index.php?p=prediksi_pupuk" class="nav-link <?= ($page == 'prediksi_pupuk') ? 'active' : ''; ?>"><i class='bx bx-brain'></i> AI Prediction</a></li>
            <li class="nav-item"><a href="index.php?p=laporan" class="nav-link <?= ($page == 'laporan') ? 'active' : ''; ?>"><i class='bx bx-bar-chart-alt-2'></i> Laporan</a></li>
            
            <li class="nav-item"><a href="index.php?p=tentang" class="nav-link <?= ($page == 'tentang') ? 'active' : ''; ?>"><i class='bx bx-book-open'></i> Tentang</a></li>

            <li class="nav-item" style="border-top: 1px solid rgba(255,255,255,0.05); margin-top: 15px; padding-top: 15px;">
                <a href="index.php?p=pengaturan_pupuk" class="nav-link <?= ($page == 'pengaturan_pupuk') ? 'active' : ''; ?>"><i class='bx bx-slider-alt'></i> Control Panel</a>
            </li>

            <li class="nav-item" style="margin-top: 10px;">
                <a href="../logout.php" class="nav-link nav-link-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                    <i class='bx bx-log-out'></i> Keluar Sistem
                </a>
            </li>
        </ul>

        <div style="padding: 20px;">
            <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 15px; text-align: center;">
                <span style="font-size: 0.75rem; color: var(--accent-green);">● User: <?= htmlspecialchars($_SESSION['nama']); ?></span>
            </div>
        </div>
    </aside>

    <div class="main-container">
        <header style="margin-bottom: 25px;">
            <h1 style="font-size: 1.5rem;">Control Panel</h1>
            <p id="liveTime" style="font-size: 0.8rem; color: #666;"></p>
        </header>

        <main class="page-content">
            <?php include $content_file; ?>
        </main>

        <footer style="text-align: center; color: rgba(255,255,255,0.1); font-size: 0.7rem; padding: 20px;">
            AGROINTELLI PRO &copy; 2026
        </footer>
    </div>

    <script>
        const btn = document.getElementById('menuBtn');
        const side = document.getElementById('navSidebar');
        
        btn.onclick = (e) => {
            e.stopPropagation();
            side.classList.toggle('show');
        }

        document.onclick = (e) => {
            if (window.innerWidth <= 1024 && !side.contains(e.target) && e.target !== btn) {
                side.classList.remove('show');
            }
        }

        function updateClock() {
            const now = new Date();
            document.getElementById('liveTime').innerText = now.toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
<?php ob_end_flush(); ?>