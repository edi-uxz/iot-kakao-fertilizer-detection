<?php
// login.php
session_start();
require_once 'koneksi.php';
$error_msg = "";

// Jika sudah ada session, langsung arahkan sesuai role-nya biar tidak perlu login lagi
if (isset($_SESSION['id_pengguna'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: user/index.php");
    }
    exit;
}

if (isset($_POST['login'])) {
    global $koneksi;

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query_login = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username = '$username'");
    
    if (mysqli_num_rows($query_login) === 1) {
        $user_data = mysqli_fetch_assoc($query_login);
        
        // 1. Cek dengan password_verify (untuk user baru hasil pendaftaran BCRYPT)
        if (password_verify($password, $user_data['password'])) {
            $_SESSION['id_pengguna'] = $user_data['id_pengguna'];
            $_SESSION['nama']        = $user_data['nama'];
            $_SESSION['role']        = $user_data['role']; // Mengambil 'admin' atau 'operator'/'user'
            
            // Logika Pengalihan Halaman berdasarkan Role
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: user/index.php");
            }
            exit;
            
        } else {
            // 2. Fallback MD5 (agar akun bawaan lama di database tetap bisa login)
            if (md5($password) === $user_data['password']) {
                $_SESSION['id_pengguna'] = $user_data['id_pengguna'];
                $_SESSION['nama']        = $user_data['nama'];
                $_SESSION['role']        = $user_data['role'];
                
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: user/index.php");
                }
                exit;
            } else {
                $error_msg = "Kombinasi kata sandi tidak cocok.";
            }
        }
    } else {
        $error_msg = "Identitas pengguna tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agro Core System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-8 relative">
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Otentikasi Pengguna</h2>
            <p class="text-xs text-slate-400 mt-1">Gunakan kredensial terdaftar untuk masuk ke kendali sensor</p>
        </div>

        <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success') { ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-3 rounded-lg text-center font-mono mb-4">
                [SUCCESS] Pendaftaran berhasil, silakan login.
            </div>
        <?php } ?>

        <?php if($error_msg != "") { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3 rounded-lg text-center font-mono mb-4">
                [ERROR] <?php echo $error_msg; ?>
            </div>
        <?php } ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Username / Email</label>
                <input type="text" name="username" required placeholder="name@domain.com" class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 placeholder-slate-600 transition">
            </div>
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Kata Sandi</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 placeholder-slate-600 transition">
            </div>
            
            <button type="submit" name="login" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-emerald-500/10 mt-6 text-sm cursor-pointer">
                Masuk Sistem
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400">
            Belum memiliki hak akses? <a href="daftar.php" class="text-emerald-400 hover:underline">Buat Akun</a>
        </div>
    </div>

</body>
</html>