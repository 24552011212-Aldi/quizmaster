<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../../server/config/koneksi.php';
$user_id = $_SESSION['user_id'];

// Ambil data user
$query = "SELECT id, username, created_at, password FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) die('Query error: ' . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if (!$user) {
    header("Location: ../login.php");
    exit();
}

// Total XP
$stmt_score = $conn->prepare("SELECT SUM(skor_akhir) as total FROM leaderboard WHERE user_id = ?");
$stmt_score->bind_param("i", $user_id);
$stmt_score->execute();
$data_score = $stmt_score->get_result()->fetch_assoc();
$total_xp = $data_score['total'] ?? 0;

// Ranking
$rank = 0;
$query_rank = mysqli_query($conn, "SELECT user_id, SUM(skor_akhir) as total FROM leaderboard GROUP BY user_id ORDER BY total DESC");
while ($row = mysqli_fetch_assoc($query_rank)) {
    $rank++;
    if ($row['user_id'] == $user_id) break;
}

// Progress Materi
$query_kategori = mysqli_query($conn, "
    SELECT q.materi, COUNT(DISTINCT q.id) as jumlah_soal,
        (SELECT COUNT(DISTINCT quiz_id) FROM quiz_history WHERE user_id = '$user_id' AND quiz_id IN (SELECT id FROM quizzes WHERE materi = q.materi)) as soal_selesai
    FROM quizzes q GROUP BY q.materi");

// Delete Akun
if (isset($_POST['delete_account'])) {
    $del_pass = md5($_POST['delete_password']);
    if ($del_pass !== $user['password']) {
        $delete_error = 'Password salah, akun tidak dihapus.';
    } else {
        // Hapus quiz_history user
        mysqli_query($conn, "DELETE FROM quiz_history WHERE user_id='$user_id'");
        // Hapus akun user
        $delete = mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
        if ($delete) {
            session_destroy();
            header("Location: ../../login.php");
            exit();
        } else {
            $delete_error = 'Gagal menghapus akun.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - QuizWeb</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .xp-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
        }

        .category-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-slate-900">
    <div class="absolute left-8 top-8">
        <a href="dashboard_player.php" class="inline-flex items-center gap-2 bg-slate-800 text-white px-5 py-2 rounded-xl font-bold shadow hover:bg-blue-600 transition-all">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="w-full max-w-md mx-auto glass-card bg-slate-800/80 rounded-3xl p-8 shadow-2xl border border-slate-700 mt-12">
        <div class="flex flex-col items-center gap-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-blue-500 to-emerald-400 flex items-center justify-center text-white text-4xl font-black shadow-lg">
                <i class="fas fa-user"></i>
            </div>
            <h1 class="text-2xl font-black text-white mb-1">Profil Player</h1>
            <div class="w-full bg-slate-700 rounded-xl p-4 flex flex-col gap-2 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-user text-blue-400"></i>
                    <span class="font-bold text-white">Username:</span>
                    <span class="text-slate-200"> <?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-calendar-alt text-yellow-400"></i>
                    <span class="font-bold text-white">Member Sejak:</span>
                    <span class="text-slate-200"> <?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                </div>
                <div>
                    <i class="fas fa-trophy text-amber-400 mb-2"></i>
                    <span class="font-bold text-white">Total XP:</span>
                    <span class="text-slate-200"> <?php echo number_format($total_xp); ?> XP</span>
                </div>
                <div>
                    <i class="fas fa-medal text-green-400 mb-2"></i>
                    <span class="font-bold text-white">Peringkat Anda:</span>
                    <span class="text-slate-200"> <?php echo $rank; ?></span>
                </div>
            </div>

            <!-- Fitur Delete Akun -->
            <form method="POST" class="bg-slate-700 rounded-xl p-4 flex flex-col gap-3 mt-4" style="max-width:400px; margin:auto;">
                <h2 class="text-base font-bold text-white mb-2">Hapus Akun</h2>
                <input type="password" name="delete_password" placeholder="Masukkan Password" class="p-3 rounded-xl bg-slate-800 text-white" required>
                <button type="submit" name="delete_account" class="bg-red-600 text-white font-bold px-6 py-2 rounded-xl" onclick="return confirm('Yakin ingin menghapus akun? Data tidak bisa dikembalikan!')">Hapus Akun</button>
                <?php if (isset($delete_error)) echo '<div class="text-red-400 text-sm">' . $delete_error . '</div>'; ?>
            </form>
        </div>
    </div>
</body>

</html>