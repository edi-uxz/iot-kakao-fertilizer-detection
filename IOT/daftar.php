<?php
// daftar.php
require_once 'koneksi.php'; 
$error_msg = "";

if (isset($_POST['register'])) {
    global $koneksi; 

    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    // Tangkap pilihan role dari form (default 'operator' jika tidak diisi)
    $role     = isset($_POST['role']) ? mysqli_real_escape_string($koneksi, $_POST['role']) : 'operator';

    // Cek duplikasi username di tabel pengguna asli
    $cek_user = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $error_msg = "Alamat email / username tersebut sudah terdaftar!";
    } else {
        // Enkripsi modern anti-cybercrime
        $password_secure = password_hash($password, PASSWORD_BCRYPT);
        
        // Simpan ke database sesuai role yang dipilih ('admin' atau 'operator')
        $query_daftar = "INSERT INTO pengguna (nama, username, password, role) VALUES ('$nama', '$username', '$password_secure', '$role')";
        
        if (mysqli_query($koneksi, $query_daftar)) {
            header("Location: login.php?signup=success");
            exit;
        } else {
            $error_msg = "Sistem gagal mendaftarkan data Anda.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - Smart Farming</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-6 sm:p-8 relative">
        <div class="absolute -top-10 -left-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Buat Akun Baru</h2>
            <p class="text-xs text-slate-400 mt-1">Daftarkan diri Anda untuk mengelola instrumen perkebunan digital</p>
        </div>

        <?php if($error_msg != "") { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs p-3 rounded-lg text-center font-mono mb-4">
                [ERROR] <?php echo $error_msg; ?>
            </div>
        <?php } ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Contoh: Edi Kurniawan" class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 placeholder-slate-600 transition">
            </div>
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Username / Email</label>
                <input type="text" name="username" required placeholder="name@domain.com" class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 placeholder-slate-600 transition">
            </div>
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Hak Akses (Role)</label>
                <select name="role" required class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 transition">
                    <option value="operator" selected>Operator</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-mono uppercase tracking-wider text-slate-400 mb-1.5">Kata Sandi</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl focus:outline-none focus:border-emerald-500 text-slate-200 placeholder-slate-600 transition">
            </div>
            
            <button type="submit" name="register" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-emerald-500/10 mt-6 text-sm cursor-pointer">
                Daftar Akun Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-400">
            Sudah memiliki hak akses? <a href="login.php" class="text-emerald-400 hover:underline">Masuk Aplikasi</a>
        </div>
    </div>

</body>
</html>