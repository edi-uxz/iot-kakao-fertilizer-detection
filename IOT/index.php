<?php
// index.php (Landing Page)
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART FARMING - Agro-Tech Platform</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen font-sans antialiased overflow-x-hidden">

    <header class="border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0 z-50 px-4 sm:px-8 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-md sm:text-lg font-mono font-bold tracking-widest text-emerald-400">AGRO_CORE v2.0</span>
        </div>
        <div class="flex items-center space-x-4">
            <?php if(isset($_SESSION['id_pengguna'])): ?>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="text-xs sm:text-sm bg-emerald-500 hover:bg-emerald-400 text-slate-950 px-4 py-2 rounded-lg font-bold transition">Panel Admin</a>
                <?php else: ?>
                    <a href="user/index.php" class="text-xs sm:text-sm bg-emerald-500 hover:bg-emerald-400 text-slate-950 px-4 py-2 rounded-lg font-bold transition">Dashboard Lahan</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="text-xs sm:text-sm text-slate-400 hover:text-white transition">Masuk</a>
                <a href="daftar.php" class="text-xs sm:text-sm bg-slate-800 hover:bg-slate-700 border border-slate-700 px-4 py-2 rounded-lg transition font-medium">Daftar Akun</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="max-w-6xl mx-auto px-4 pt-16 pb-24 md:pt-28 md:pb-36 text-center relative">
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 sm:w-96 h-72 sm:h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <span class="inline-block px-3 py-1 rounded-full text-xs font-mono bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 mb-6 tracking-wide">
            TRANSFORMASI DIGITAL PERTANIAN PRESISI
        </span>
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight">
            Kendalikan Nutrisi Lahan Berbasis <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-200">Kecerdasan Buatan</span>
        </h1>
        <p class="mt-6 text-sm sm:text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed">
            Dari Kakao, Cabai Merah, hingga Sistem Hidroponik. Pantau kondisi tanah secara real-time dan dapatkan rekomendasi pemupukan akurat langsung dari kamar Anda.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4">
            <?php if(isset($_SESSION['id_pengguna'])): ?>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl font-bold transition shadow-lg shadow-emerald-500/20 text-center">
                        Masuk Ke Panel Admin
                    </a>
                <?php else: ?>
                    <a href="user/index.php" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl font-bold transition shadow-lg shadow-emerald-500/20 text-center">
                        Masuk Ke Dashboard Anda
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="daftar.php" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl font-bold transition shadow-lg shadow-emerald-500/20 text-center">
                    Mulai Digitalisasi Gratis
                </a>
            <?php endif; ?>
            <a href="#fitur" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-slate-800 text-slate-300 rounded-xl font-medium border border-slate-800 transition text-center">
                Pelajari Alur Sistem
            </a>
        </div>
    </section>

    <section id="fitur" class="max-w-6xl mx-auto px-4 py-12 border-t border-slate-900">
        <div class="text-center mb-16">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Kompatibilitas Komoditas Universal</h2>
            <p class="text-slate-400 text-xs sm:text-sm mt-2">Satu platform tangguh untuk berbagai kebutuhan vegetatif perkebunan Anda.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-900/50 border border-slate-900 rounded-2xl p-6 hover:border-emerald-500/30 transition">
                <div class="text-2xl mb-4">🍫</div>
                <h3 class="text-lg font-bold text-slate-100">Perkebunan Kakao</h3>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Optimalisasi pertumbuhan buah cokelat berdasarkan standarisasi suhu makro dan mikro Pringsewu.</p>
            </div>
            <div class="bg-slate-900/50 border border-slate-900 rounded-2xl p-6 hover:border-emerald-500/30 transition">
                <div class="text-2xl mb-4">🌶️</div>
                <h3 class="text-lg font-bold text-slate-100">Hortikultura Cabai</h3>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Pantau kelembapan tanah intensif guna mencegah pembusukan akar pada tanaman cabai merah secara presisi.</p>
            </div>
            <div class="bg-slate-900/50 border border-slate-900 rounded-2xl p-6 hover:border-emerald-500/30 transition">
                <div class="text-2xl mb-4">💧</div>
                <h3 class="text-lg font-bold text-slate-100">Sistem Hidroponik</h3>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">Integrasi sensor pembaca nilai kepekatan nutrisi air (PPM) dan pH meter otomatis untuk sayuran sehat.</p>
            </div>
        </div>
    </section>

    <footer class="text-center py-8 border-t border-slate-900 text-xs text-slate-600 font-mono">
        &copy; 2026 AGRO_CORE PLATFORM. All rights reserved.
    </footer>

</body>
</html>