<?php
// tentang.php - Penjelasan Santai & Mudah Dipahami
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sistem - AgroIntelli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .step-line::before {
            content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 2px;
            background: rgba(46, 204, 113, 0.2); z-index: 0;
        }
        .glass-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#050b0a] text-[#e0e0e0] p-4 md:p-8">

    <div class="max-w-5xl mx-auto">
        <div class="mb-16 text-center">
            <h1 class="text-4xl font-extrabold text-white mb-4 tracking-tight">Gimana Sih <span class="text-green-500">AgroIntelli</span> Bekerja?</h1>
            <p class="text-gray-400 max-w-2xl mx-auto">Yuk, intip lebih dalam gimana teknologi IoT dan AI kami membantu petani kakao jadi lebih hebat.</p>
        </div>

        <section class="mb-20 grid md:grid-cols-1 gap-8">
            <div class="glass-card p-8 rounded-3xl">
                <h2 class="text-2xl font-bold mb-6 text-green-400 flex items-center gap-3">
                    <i class='bx bx-smile'></i> Kenalan Sama Masalahnya
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <h4 class="text-white font-semibold mb-2">Kenapa Sistem Ini Dibuat? (Latar Belakang)</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Banyak petani kakao yang masih bingung kapan waktu yang pas buat kasih pupuk atau berapa banyak dosisnya. Biasanya cuma pakai perasaan saja, padahal kondisi tanah itu beda-beda. Akhirnya, kadang pupuk malah terbuang percuma karena tanahnya lagi terlalu asam atau kering. Nah, sistem ini hadir biar petani nggak perlu tebak-tebak lagi.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="p-5 bg-white/5 rounded-2xl border border-white/5">
                            <h4 class="text-white font-semibold mb-2 text-sm">Apa yang Mau Diselesaikan?</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                Kita ingin buat alat yang bisa cek kondisi tanah secara otomatis dan kasih tahu petani: "Eh, tanahmu butuh pupuk ini nih, takarannya segini ya!" lewat layar HP atau komputer.
                            </p>
                        </div>
                        <div class="p-5 bg-white/5 rounded-2xl border border-white/5">
                            <h4 class="text-white font-semibold mb-2 text-sm">Tujuan Akhirnya</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                Intinya, kita mau bantu petani kakao biar nggak boros pupuk dan hasil panen kakaonya jadi makin melimpah karena tanahnya selalu sehat dan ternutrisi dengan pas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold mb-10 flex items-center gap-3">
                <i class='bx bx-rocket text-green-500'></i> Cara Kerjanya Gampang Banget!
            </h2>
            
            <div class="relative space-y-10 step-line">
                <div class="relative flex gap-8 z-10">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-black shadow-[0_0_20px_rgba(46,204,113,0.3)]">1</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Sensor "Ngecek" Tanah</h3>
                        <p class="text-sm text-gray-400">
                            Ada alat kecil (ESP8266) yang ditaruh di lahan. Alat ini punya "indra" buat ngerasain pH tanah, suhu, dan kelembapan. Setiap 5 detik sekali, dia laporin hasilnya.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-8 z-10">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-black">2</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Data Dikirim ke "Gudang"</h3>
                        <p class="text-sm text-gray-400">
                            Laporan tadi dikirim lewat WiFi ke gudang penyimpanan data kita (Database). Jadi, semua catatan kesehatan tanah tersimpan rapi dan nggak bakal hilang.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-8 z-10">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-black">3</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">AI Berpikir Seperti Pakar</h3>
                        <p class="text-sm text-gray-400">
                            Setelah data sampai, otak buatan (AI) kita bakal mikir: "Kalau pH-nya segini dan suhunya segitu, berarti tanamannya butuh apa ya?". AI ini sudah belajar dari pengalaman para ahli tani kakao.
                        </p>
                    </div>
                </div>

                <div class="relative flex gap-8 z-10">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-black">4</div>
                    <div class="glass-card p-6 rounded-2xl w-full">
                        <h3 class="font-bold text-white mb-2">Muncul Saran Buat Petani</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 text-center">
                            <div class="p-3 bg-white/5 rounded-xl border border-white/5"><span class="text-green-500 block font-bold mb-1">BERAPA BANYAK?</span>Dosis yang pas buat pohonmu.</div>
                            <div class="p-3 bg-white/5 rounded-xl border border-white/5"><span class="text-green-500 block font-bold mb-1">KAPAN?</span>Waktu terbaik buat mupuk.</div>
                            <div class="p-3 bg-white/5 rounded-xl border border-white/5"><span class="text-green-500 block font-bold mb-1">GIMANA?</span>Cara mupuk yang bener.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-20">
            <div class="bg-gradient-to-br from-green-900/20 to-blue-900/20 border border-green-500/20 p-8 rounded-3xl shadow-2xl">
                <h2 class="text-2xl font-bold mb-8 flex items-center gap-3">
                    <i class='bx bx-brain text-blue-400'></i> Kok AI-nya Bisa Pintar?
                </h2>
                <div class="grid md:grid-cols-2 gap-10">
                    <div>
                        <h4 class="font-semibold text-white mb-3">Aturan "Kalau - Maka"</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            AI ini pakai logika sederhana: "Kalau kondisi tanah begini, maka solusinya begitu". Aturan ini kita ambil langsung dari cara kerja para pakar petani kakao yang sudah sukses.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white mb-3">Sistem Poin</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            AI juga kasih poin buat setiap kondisi. Misalnya, pH tanah dikasih poin tinggi karena itu yang paling penting buat menentukan tanaman bisa "makan" nutrisi atau nggak.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-12">
            <div class="glass-card p-10 rounded-3xl text-center">
                <h2 class="text-2xl font-bold mb-4 text-white">Intinya...</h2>
                <p class="text-sm text-gray-400 leading-relaxed max-w-3xl mx-auto italic">
                    "AgroIntelli itu seperti asisten pribadi buat petani kakao. Dia bantu ngecek tanah terus-terusan tanpa lelah, lalu kasih saran yang paling pas biar tanaman kakao sehat dan petani nggak rugi beli pupuk kebanyakan. Semuanya otomatis dan langsung ke HP kamu!"
                </p>
            </div>
        </section>

        <footer class="text-center py-10 border-t border-white/5 opacity-50">
            <p class="text-xs">AGROINTELLI &copy; 2026 | Solusi Pintar Buat Petani Kakao</p>
        </footer>
    </div>

</body>
</html>