<?php
// tentang.php - Penjelasan Sistem AgroIntelli (Modular Sidebar UI)
session_start();
$page_aktif = 'tentang'; // Penanda menu aktif di sidebar.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail & Logika Sistem - AgroIntelli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        
        .glass-card { 
            background: rgba(15, 23, 42, 0.6); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            backdrop-filter: blur(12px); 
        }

        .step-line::before {
            content: ''; position: absolute; left: 19px; top: 0; bottom: 0; width: 2px;
            background: rgba(16, 185, 129, 0.2); z-index: 0;
        }

        .fade-in { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-[#050b0a] text-slate-100 min-h-screen relative overflow-x-hidden">

    <!-- Navbar Topbar -->
    <nav class="border-b border-slate-800 bg-slate-900/80 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Sisi Kiri: Tombol Menu Garis Tiga + Logo -->
                <div class="flex items-center gap-3">
                    <button id="open-sidebar" class="w-10 h-10 bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700/80 rounded-xl flex items-center justify-center transition shadow-lg hover:text-emerald-400 focus:outline-none cursor-pointer">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400 font-bold">
                            <i class='bx bx-brain text-xl'></i>
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-tight text-white">AgroIntelli</span>
                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded font-mono ml-2">INFO</span>
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Status Login / Tombol Aksi -->
                <div class="flex items-center gap-3">
                    <?php if (isset($_SESSION['nama'])): ?>
                        <span class="text-xs text-slate-400 hidden sm:inline">Halo, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
                        <a href="logout.php" class="text-xs bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 px-3 py-2 rounded-xl transition flex items-center gap-1">
                            <i class='bx bx-log-out text-sm'></i> <span class="hidden sm:inline">Keluar</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-xs bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 px-4 py-2 rounded-xl transition flex items-center gap-1 font-semibold">
                            <i class='bx bx-log-in text-sm'></i> <span>Masuk</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- INCLUDE SIDEBAR MODULAR -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 fade-in">
        
        <div class="mb-16 text-center">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4 tracking-tight">Gimana Sih <span class="text-emerald-400">AgroIntelli</span> Bekerja?</h1>
            <p class="text-slate-400 text-sm max-w-2xl mx-auto">Yuk, intip lebih dalam gimana teknologi IoT dan AI kami membantu petani kakao jadi lebih hebat.</p>
        </div>

        <!-- Section 1: Latar Belakang -->
        <section class="mb-16 grid md:grid-cols-1 gap-8">
            <div class="glass-card p-6 md:p-8 rounded-3xl">
                <h2 class="text-xl md:text-2xl font-bold mb-6 text-emerald-400 flex items-center gap-3">
                    <i class='bx bx-smile'></i> Kenalan Sama Masalahnya
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <h4 class="text-white font-semibold mb-2">Kenapa Sistem Ini Dibuat? (Latar Belakang)</h4>
                        <p class="text-xs md:text-sm text-slate-400 leading-relaxed">
                            Banyak petani kakao yang masih bingung kapan waktu yang pas buat kasih pupuk atau berapa banyak dosisnya. Biasanya cuma pakai perasaan saja, padahal kondisi tanah itu beda-beda. Akhirnya, kadang pupuk malah terbuang percuma karena tanahnya lagi terlalu asam atau kering. Nah, sistem ini hadir biar petani nggak perlu tebak-tebak lagi.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="p-5 bg-slate-900/50 rounded-2xl border border-slate-800">
                            <h4 class="text-white font-semibold mb-2 text-sm">Apa yang Mau Diselesaikan?</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Kita ingin buat alat yang bisa cek kondisi tanah secara otomatis dan kasih tahu petani: "Eh, tanahmu butuh pupuk ini nih, takarannya segini ya!" lewat layar HP atau komputer.
                            </p>
                        </div>
                        <div class="p-5 bg-slate-900/50 rounded-2xl border border-slate-800">
                            <h4 class="text-white font-semibold mb-2 text-sm">Tujuan Akhirnya</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Intinya, kita mau bantu petani kakao biar nggak boros pupuk dan hasil panen kakaonya jadi makin melimpah karena tanahnya selalu sehat dan ternutrisi dengan pas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Alur Kerja -->
        <section class="mb-16">
            <h2 class="text-xl md:text-2xl font-bold mb-8 flex items-center gap-3 text-white">
                <i class='bx bx-rocket text-emerald-400'></i> Cara Kerjanya Gampang Banget!
            </h2>
            
            <div class="relative space-y-8 step-line">
                <div class="relative flex gap-6 md:gap-8 z-10">
                    <div class="w-10 h-10 bg-emerald-400 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-slate-950 shadow-[0_0_20px_rgba(52,211,153,0.3)]">1</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Sensor "Ngecek" Tanah</h3>
                        <p class="text-xs md:text-sm text-slate-400">
                            Ada alat kecil (ESP8266) yang ditaruh di lahan. Alat ini punya "indra" buat ngerasain pH tanah, suhu, dan kelembapan. Setiap 5 detik sekali, dia laporin hasilnya.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-6 md:gap-8 z-10">
                    <div class="w-10 h-10 bg-emerald-400 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-slate-950">2</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Data Dikirim ke "Gudang"</h3>
                        <p class="text-xs md:text-sm text-slate-400">
                            Laporan tadi dikirim lewat WiFi ke gudang penyimpanan data kita (Database). Jadi, semua catatan kesehatan tanah tersimpan rapi dan nggak bakal hilang.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-6 md:gap-8 z-10">
                    <div class="w-10 h-10 bg-emerald-400 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-slate-950">3</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">AI Berpikir Seperti Pakar</h3>
                        <p class="text-xs md:text-sm text-slate-400">
                            Setelah data sampai, otak buatan (AI) kita bakal mikir: "Kalau pH-nya segini dan suhunya segitu, berarti tanamannya butuh apa ya?". AI ini sudah belajar dari pengalaman para ahli tani kakao.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-6 md:gap-8 z-10">
                    <div class="w-10 h-10 bg-emerald-400 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-slate-950">4</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Muncul Saran Buat Petani</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-center">
                            <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 text-xs text-slate-400"><span class="text-emerald-400 block font-bold mb-1">BERAPA BANYAK?</span>Dosis yang pas buat pohonmu.</div>
                            <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 text-xs text-slate-400"><span class="text-emerald-400 block font-bold mb-1">KAPAN?</span>Waktu terbaik buat mupuk.</div>
                            <div class="p-3 bg-slate-900/50 rounded-xl border border-slate-800 text-xs text-slate-400"><span class="text-emerald-400 block font-bold mb-1">GIMANA?</span>Cara mupuk yang bener.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Logika AI -->
        <section class="mb-16">
            <div class="bg-gradient-to-br from-emerald-950/40 to-slate-900/80 border border-emerald-500/20 p-6 md:p-8 rounded-3xl shadow-2xl">
                <h2 class="text-xl md:text-2xl font-bold mb-6 flex items-center gap-3 text-white">
                    <i class='bx bx-brain text-emerald-400'></i> Kok AI-nya Bisa Pintar?
                </h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-semibold text-white mb-2 text-sm">Aturan "Kalau - Maka"</h4>
                        <p class="text-xs md:text-sm text-slate-400 leading-relaxed">
                            AI ini pakai logika sederhana: "Kalau kondisi tanah begini, maka solusinya begitu". Aturan ini kita ambil langsung dari cara kerja para pakar petani kakao yang sudah sukses.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-2 text-sm">Sistem Poin</h4>
                        <p class="text-xs md:text-sm text-slate-400 leading-relaxed">
                            AI juga kasih poin buat setiap kondisi. Misalnya, pH tanah dikasih poin tinggi karena itu yang paling penting buat menentukan tanaman bisa "makan" nutrisi atau nggak.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kesimpulan -->
        <section class="mb-12">
            <div class="glass-card p-8 md:p-10 rounded-3xl text-center">
                <h2 class="text-xl font-bold mb-3 text-white">Intinya...</h2>
                <p class="text-xs md:text-sm text-slate-400 leading-relaxed max-w-3xl mx-auto italic">
                    "AgroIntelli itu seperti asisten pribadi buat petani kakao. Dia bantu ngecek tanah terus-terusan tanpa lelah, lalu kasih saran yang paling pas biar tanaman kakao sehat dan petani nggak rugi beli pupuk kebanyakan. Semuanya otomatis dan langsung ke HP kamu!"
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="text-center py-8 border-t border-slate-800/80 text-xs text-slate-500">
            AGROINTELLI &copy; 2026 | Smart Precision Farming System - Kakao Lampung
        </footer>
    </div>

    <!-- JAVASCRIPT ANIMASI INTERAKTIF SIDEBAR -->
    <script>
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');
        const sidebar = document.getElementById('sidebar-drawer');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar(open) {
            if (open) {
                overlay.classList.remove('pointer-events-none', 'opacity-0');
                overlay.classList.add('opacity-100');
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
            } else {
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
            }
        }

        if (openBtn) openBtn.addEventListener('click', () => toggleSidebar(true));
        if (closeBtn) closeBtn.addEventListener('click', () => toggleSidebar(false));
        if (overlay) overlay.addEventListener('click', () => toggleSidebar(false));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleSidebar(false);
        });
    </script>

</body>
</html>