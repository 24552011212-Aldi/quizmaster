<?php
session_start();
error_reporting(0);
include "../../server/config/koneksi.php";
include_once "../../server/auth_check.php";
checkLogin();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//mengambil total score
$stmt_score = $conn->prepare("SELECT SUM(skor_akhir) as total FROM leaderboard WHERE user_id = ?");
$stmt_score->bind_param("i", $user_id);
$stmt_score->execute();
$data_score = $stmt_score->get_result()->fetch_assoc();
$total_xp = $data_score['total'] ?? 0;

// leaderboard sampai 5 besar
$query_leaderboard = mysqli_query($conn, "
    SELECT u.username, IFNULL(SUM(l.skor_akhir), 0) as total_skor 
    FROM users u
    LEFT JOIN leaderboard l ON u.id = l.user_id
    WHERE u.role = 'player'
    GROUP BY u.id
    ORDER BY total_skor DESC
");

// leaderboard sampai 5 besar dari bawah
$query_leaderboard_malas = mysqli_query($conn, "
    SELECT u.username, IFNULL(SUM(l.skor_akhir), 0) as total_skor 
    FROM users u
    LEFT JOIN leaderboard l ON u.id = l.user_id
    WHERE u.role = 'player'
    GROUP BY u.id
    ORDER BY total_skor ASC
");


$query_kategori = mysqli_query($conn, "
    SELECT 
        q.materi, 
        COUNT(DISTINCT q.id) as jumlah_soal,
        (SELECT COUNT(DISTINCT quiz_id) 
         FROM quiz_history 
         WHERE user_id = '$user_id' 
         AND quiz_id IN (SELECT id FROM quizzes WHERE materi = q.materi)
        ) as soal_selesai
    FROM quizzes q
    GROUP BY q.materi
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard Player - CodeMaster</title>
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

<body>
    <main class="min-h-screen flex items-center justify-center p-6">
        <div class="absolute left-8 top-8">
            <a href="dashboard_player.php" class="inline-flex items-center gap-2 bg-slate-800 text-white px-5 py-2 rounded-xl font-bold shadow hover:bg-blue-600 transition-all">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="flex flex-col lg:flex-row gap-6 w-full max-w-8xl">
            <!-- Panel XP dan Progress -->
            <div class="glass-card p-8 rounded-[2.5rem] flex-1 min-w-[320px] mb-8 lg:mb-0">
                <h2 class="text-lg font-black text-white mb-2 flex items-center gap-2">
                    <i class="fas fa-user-astronaut text-blue-400"></i> Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </h2>
                <div class="mb-6">
                    <div class="text-xs text-slate-400 font-bold uppercase mb-1">Total XP Kamu</div>
                    <div class="xp-gradient text-white font-black text-3xl rounded-xl px-6 py-3 inline-block shadow-lg">
                        <?php echo number_format($total_xp); ?> XP
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-400 font-bold uppercase mb-2">Progress Materi</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php while ($kat = mysqli_fetch_assoc($query_kategori)): ?>
                            <div class="category-card bg-slate-800/60 rounded-xl p-4 flex flex-col gap-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white text-sm"><?php echo htmlspecialchars($kat['materi']); ?></span>
                                    <span class="text-xs text-slate-400">Soal: <?php echo $kat['jumlah_soal']; ?></span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-lg h-3 mt-2">
                                    <div class="bg-blue-500 h-3 rounded-lg" style="width:<?php echo ($kat['jumlah_soal'] > 0 ? round($kat['soal_selesai'] / $kat['jumlah_soal'] * 100) : 0); ?>%"></div>
                                </div>
                                <div class="text-xs text-slate-400">Selesai: <?php echo $kat['soal_selesai']; ?> / <?php echo $kat['jumlah_soal']; ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Leaderboard siRajin-->
            <div class="glass-card p-8 rounded-[2.5rem] flex-1 min-w-[320px]">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i> TOP Si Paling Ngoding
                </h3>
                <div class="space-y-3;">
                    <?php
                    // Reset query_leaderboard
                    $query_leaderboard = mysqli_query($conn, "SELECT u.username, IFNULL(SUM(l.skor_akhir), 0) as total_skor FROM users u LEFT JOIN leaderboard l ON u.id = l.user_id WHERE u.role = 'player' GROUP BY u.id ORDER BY total_skor DESC");
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query_leaderboard)):
                        $is_me = ($row['username'] == $_SESSION['username']); ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl <?php echo $is_me ? 'bg-blue-600/20 border border-blue-500/30' : 'bg-slate-800/40'; ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-slate-700 text-[10px] font-black <?php echo $no == 1 ? 'text-yellow-400' : 'text-slate-400'; ?>">
                                    #<?php echo $no++; ?>
                                </span>
                                <span class="text-sm font-bold text-white "><?php echo htmlspecialchars($row['username']); ?></span>
                            </div>
                            <span class="text-xs font-black text-slate-300"><?php echo number_format($row['total_skor']); ?> XP</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Leaderboard pemalas-->
            <div class="glass-card p-8 rounded-[2.5rem] flex-1 min-w-[320px]">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i> TOP Si Paling malas
                </h3>
                <div class="space-y-3;">
                    <?php
                    // Reset query_leaderboard
                    $query_leaderboard = mysqli_query($conn, "SELECT u.username, IFNULL(SUM(l.skor_akhir), 0) as total_skor FROM users u LEFT JOIN leaderboard l ON u.id = l.user_id WHERE u.role = 'player' GROUP BY u.id ORDER BY total_skor ASC");
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($query_leaderboard_malas)):
                        $is_me = ($row['username'] == $_SESSION['username']); ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl <?php echo $is_me ? 'bg-blue-600/20 border border-blue-500/30 ' : 'bg-slate-800/40'; ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-slate-700 text-[10px] font-black <?php echo $no == 1 ? 'text-yellow-400' : 'text-slate-400'; ?>">
                                    #<?php echo $no++; ?>
                                </span>
                                <span class="text-sm font-bold text-white "><?php echo htmlspecialchars($row['username']); ?></span>
                            </div>
                            <span class="text-xs font-black text-slate-300"><?php echo number_format($row['total_skor']); ?> XP</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>