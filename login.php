<?php
session_start();
include "server/config/koneksi.php";

// Fallback: jika sudah ada session, redirect langsung
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
        exit();
    } else {
        header("Location: player/pages/dashboard_player.php");
        exit();
    }
}

// Fallback: jika ada cookie, login otomatis
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_login'])) {
    $decoded_username = base64_decode($_COOKIE['user_login']);
    $query = "SELECT * FROM users WHERE username = '$decoded_username'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        if ($row['role'] == 'admin') {
            header("Location: admin/index.php");
            exit();
        } else {
            header("Location: player/pages/dashboard_player.php");
            exit();
        }
    }
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        // Set cookie setelah login sukses
        $cookie_value = base64_encode($row['username']);
        setcookie('user_login', $cookie_value, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Lax'
        ]);

        if ($row['role'] == 'admin') {
            header("Location: admin/index.php");
            exit();
        } else {
            header("Location: player/pages/dashboard_player.php");
            exit();
        }
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CodeMaster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="text-slate-200 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">

    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="flex justify-center mb-8">
            <a href="index.php" class="flex items-center gap-2">
                <div class="bg-blue-600 p-2 rounded-lg shadow-lg shadow-blue-500/30">
                    <i class="fas fa-code text-white"></i>
                </div>
                <span class="text-2xl font-black tracking-tighter text-white uppercase">Code<span class="text-blue-500">Master</span></span>
            </a>
        </div>

        <div class="glass p-10 rounded-[2.5rem] shadow-2xl">
            <h2 class="text-3xl font-black text-white mb-2 text-center">Welcome Back!</h2>
            <p class="text-slate-400 text-sm text-center mb-8">Masukkan kredensial untuk melanjutkan misi.</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2 mb-2 block">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-4 text-slate-500 text-sm"></i>
                        <input type="text" name="username" placeholder="pro_coder" required
                            class="w-full bg-slate-900/50 border border-slate-700 p-4 pl-12 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white transition-all">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] ml-2 mb-2 block">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-4 text-slate-500 text-sm"></i>
                        <input type="password" name="password" placeholder="••••••••" required
                            class="w-full bg-slate-900/50 border border-slate-700 p-4 pl-12 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white transition-all">
                    </div>
                </div>

                <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl font-black text-white transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98] mt-4">
                    LOGIN
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-slate-400 text-sm">Belum punya akun?
                    <a href="register.php" class="text-blue-400 font-bold hover:underline underline-offset-4">Register</a>
                </p>
            </div>
        </div>
    </div>
    <!-- Mouse Tracking Spotlight -->
    <div id="mouse-spotlight"
        class="fixed pointer-events-none rounded-full opacity-0 transition-opacity duration-500 z-[9999]"
        style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%); filter: blur(30px); transform: translate(-50%, -50%); transition: opacity 0.5s, transform 0.1s ease-out;">
    </div>
    <script>
        const spotlight = document.getElementById('mouse-spotlight');

        document.addEventListener('mousemove', e => {
            // Update posisi & munculkan spotlight
            spotlight.style.left = `${e.clientX}px`;
            spotlight.style.top = `${e.clientY}px`;
            spotlight.style.opacity = '1';
        });

        document.addEventListener('mouseleave', () => spotlight.style.opacity = '0');
    </script>
</body>

</html>