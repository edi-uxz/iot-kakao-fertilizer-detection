<!-- <?php
// FILE: fixed_header.php

// PENTING: Pastikan TIDAK ADA baris kosong, spasi, atau karakter lain 
// sebelum tag <?php ini di baris pertama.

// --- INISIASI DAN MANIPULASI HEADER (HARUS DI ATAS) ---

// 1. Memulai Session (harus dilakukan sebelum output apapun)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Contoh Logika Pemeriksaan Otentikasi/Redirect 
// Jika ini diletakkan di bawah output HTML, inilah yang menyebabkan Warning.
if (!isset($_SESSION['user_id'])) {
    // header('Location: /login.php'); 
    // exit; // Komentar ini untuk demo. Jika Anda menggunakannya, harus di sini.
}

// 3. Penanganan Data POST/GET yang mungkin me-redirect
// Misalnya:
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
//     // ... proses data ...
//     // header('Location: success_page.php'); // Jika ini ada di pages/prediksi_pupuk.php baris 60, maka akan error!
//     // exit;
// }


// --- AKHIR DARI MANIPULASI HEADER ---

// Sisa kode PHP Anda, jika ada (misalnya: pengambilan data dari database)

?>
<!DOCTYPE html> 
<!-- Baris <!DOCTYPE html> ini (dan semua HTML di bawah) adalah OUTPUT PERTAMA.
     Jika ada fungsi header() di pages/prediksi_pupuk.php setelah ini, 
     maka akan muncul error. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kakao AI-IoT Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2e0ad22f4.js" crossorigin="anonymous"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* CSS Lengkap dari dashboard.php sebelumnya */
        :root {
            --primary-color: #5d4037; 
            --secondary-color: #ffc107; 
            --background-color: #f4f7f6;
            --text-color: #333;
            --card-bg: #fff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            display: flex; 
            min-height: 100vh;
        }
        .main-content {
            flex-grow: 1; 
            padding: 0; 
        }
        .container {
            width: 95%;
            max-width: 1400px;
            margin: auto;
            padding: 20px; 
        }
        
        /* --- SIDEBAR STYLING --- */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            position: sticky; 
            top: 0;
            height: 100vh;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-bottom: 5px;
        }
        .logo h2 {
            font-size: 1.4em;
            font-weight: 700;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s, border-left 0.3s;
        }
        .sidebar ul li a i {
            margin-right: 15px;
        }
        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar ul li .active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 5px solid var(--secondary-color);
            font-weight: 600;
        }

        /* --- CONTENT & CARD STYLING --- */
        .header-glow {
            text-align: center;
            padding: 30px 0 40px;
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 20px rgba(93, 64, 55, 0.4);
            margin-bottom: 30px;
        }
        .header-glow h1 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        .header-glow .subtitle {
            font-weight: 300;
            opacity: 0.9;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border-left: 5px solid #ccc; 
            overflow: hidden;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }
        .card h3 {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .card h3 i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        .card .subtitle {
            font-size: 0.9em;
            color: #555;
            line-height: 1.4;
        }

        /* Chart height fix */
        .card canvas {
            max-height: 250px; 
            width: 100% !important; 
            height: auto !important;
        }

        /* Card Wide untuk Rekomendasi */
        .card.wide {
            grid-column: span 1; 
        }
        @media (min-width: 992px) {
            .cards {
                grid-template-columns: repeat(4, 1fr);
            }
            .card.wide {
                grid-column: span 2; 
            }
        }

        /* Konten Rekomendasi (Flexbox) */
        .card.wide .content-rekomendasi {
            display: flex; 
            gap: 20px; 
            align-items: flex-start;
            flex-direction: column; 
        }
        @media (min-width: 768px) {
            .card.wide .content-rekomendasi {
                flex-direction: row; 
            }
            .card.wide .content-rekomendasi > div:last-child {
                flex-basis: 45%; 
            }
        }
        .card.wide canvas {
            max-height: 180px; 
        }

        /* ALERT STYLES FOR REKOMENDASI */
        .alert-success { border-left-color: #28a745; background-color: #d4edda; color: #155724; }
        .alert-warning { border-left-color: #ffc107; background-color: #fff3cd; color: #856404; }
        .alert-danger { border-left-color: #dc3545; background-color: #f8d7da; color: #721c24; }
        
        /* ANIMASI */
        .animate-fade-in { animation: fadeIn 0.8s ease-out; }
        .animate-up { opacity: 0; animation: slideUp 0.6s ease-out forwards; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body> -->
