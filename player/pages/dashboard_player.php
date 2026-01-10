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

// leaderboard sampai 3 besar
$query_leaderboard = mysqli_query($conn, "
    SELECT u.username, IFNULL(SUM(l.skor_akhir), 0) as total_skor 
    FROM users u
    LEFT JOIN leaderboard l ON u.id = l.user_id
    WHERE u.role = 'player'
    GROUP BY u.id
    ORDER BY total_skor DESC
    LIMIT 3
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Player - CodeMaster</title>
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

<body class="text-slate-200 min-h-screen pb-10">

    <nav class="glass-card sticky top-0 z-50 px-6 py-4 border-b border-slate-700">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-2 rounded-lg"><i class="fas fa-code text-white"></i></div>
                <span class="font-black text-xl tracking-tighter text-white uppercase">CODE<span class="text-blue-500">MASTER</span></span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] uppercase font-bold text-slate-500">Logged in as</p>
                    <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
                <a href="../../profile.php" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 w-10 h-10 flex items-center justify-center rounded-xl border border-blue-500/20 transition">
                    <i class="fas fa-user"></i>
                </a>
                <a href="../../logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 w-10 h-10 flex items-center justify-center rounded-xl border border-red-500/20 transition">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 mt-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="lg:col-span-2 xp-gradient p-8 rounded-[2.5rem] relative overflow-hidden flex flex-col justify-center min-h-[220px]">
                <div class="relative z-10">
                    <h3 class="text-blue-100/80 font-bold uppercase tracking-widest text-xs mb-2">My Total Progression</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-7xl font-black text-white"><?php echo number_format($total_xp); ?></span>
                        <span class="text-2xl font-bold text-blue-200">XP</span>
                    </div>
                </div>
                <i class="fas fa-rocket absolute right-[-20px] bottom-[-20px] text-[12rem] text-white/10 -rotate-12"></i>
            </div>

            <!-- Leaderboard Rajin -->
            <div class="glass-card p-6 rounded-[2.5rem]">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i> TOP Si Paling Ngoding
                </h3>
                <div class="space-y-3">
                    <?php $no = 1;
                    while ($row = mysqli_fetch_assoc($query_leaderboard)):
                        $is_me = ($row['username'] == $_SESSION['username']); ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl <?php echo $is_me ? 'bg-blue-600/20 border border-blue-500/30' : 'bg-slate-800/40'; ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-slate-700 text-[10px] font-black <?php echo $no == 1 ? 'text-yellow-400' : 'text-slate-400'; ?>">
                                    #<?php echo $no++; ?>
                                </span>
                                <span class="text-sm font-bold"><?php echo htmlspecialchars($row['username']); ?></span>
                            </div>
                            <span class="text-xs font-black text-slate-300"><?php echo number_format($row['total_skor']); ?> XP</span>
                        </div>
                    <?php endwhile; ?>
                    <a href="leaderboard_page.php"
                        class="flex items-center justify-between w-full bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-2xl font-bold transition-all shadow-lg shadow-blue-600/20 group/btn">
                        <span>Lihat Leaderboard</span>
                        <i class="fas fa-arrow-right -rotate-45 group-hover/btn:rotate-0 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>

        <h2 class="text-2xl font-black text-white mb-8 flex items-center gap-3 uppercase tracking-tight">
            <span class="w-2 h-8 bg-blue-600 rounded-full"></span> Available Missions
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php while ($kategori = mysqli_fetch_assoc($query_kategori)):
                $materi_nama = $kategori['materi'];
                $total = $kategori['jumlah_soal'];
                $selesai = $kategori['soal_selesai'];
                $is_fully_done = ($total > 0 && $total == $selesai);

                // Ikon materi
                $icon = "fa-code";
                if (stripos($materi_nama, 'js') !== false || stripos($materi_nama, 'javascript') !== false) $icon = "fa-js";
                elseif (stripos($materi_nama, 'python') !== false) $icon = "fa-python";
                elseif (stripos($materi_nama, 'java') !== false) $icon = "fa-java";
                elseif (stripos($materi_nama, 'php') !== false) $icon = "fa-php";
                elseif (stripos($materi_nama, 'html') !== false) $icon = "fa-html5";
                elseif (stripos($materi_nama, 'css') !== false) $icon = "fa-css3-alt";
                elseif (stripos($materi_nama, 'c++') !== false) $icon = "fa-cuttlefish";
                elseif (stripos($materi_nama, 'c#') !== false) $icon = "fa-cuttlefish";
                elseif (stripos($materi_nama, 'ruby') !== false) $icon = "fa-gem";
                elseif (stripos($materi_nama, 'sql') !== false) $icon = "fa-sql-server";
            ?>
                <div class="category-card glass-card rounded-[2.5rem] overflow-hidden flex flex-col group <?php echo $is_fully_done ? 'opacity-60' : ''; ?>">
                    <div class="h-40 flex items-center justify-center relative overflow-hidden bg-slate-800/30 border-b border-slate-700/50">
                        <i class="fab <?php echo $icon; ?> text-7xl text-white/5 absolute transform group-hover:scale-125 transition-transform duration-500"></i>
                        <i class="fab <?php echo $icon; ?> text-5xl text-white relative z-10"></i>

                        <?php if ($is_fully_done): ?>
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-[2px] z-20">
                                <div class="bg-emerald-500 text-white px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-xl">
                                    <i class="fas fa-check-circle mr-1"></i> Completed
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-8">
                        <h3 class="text-2xl font-black text-white mb-1"><?php echo htmlspecialchars($materi_nama); ?></h3>

                        <div class="flex items-center justify-between mb-2 mt-6">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Progress</span>
                            <span class="text-[10px] font-black text-slate-300 uppercase"><?php echo $selesai; ?> / <?php echo $total; ?> Solved</span>
                        </div>

                        <div class="w-full h-2 bg-slate-800 rounded-full mb-8 overflow-hidden">
                            <div class="h-full bg-blue-500 transition-all duration-1000 shadow-[0_0_15px_rgba(59,130,246,0.4)]"
                                style="width: <?php echo ($total > 0) ? ($selesai / $total) * 100 : 0; ?>%"></div>
                        </div>

                        <?php if (!$is_fully_done): ?>
                            <a href="quiz_play.php?materi=<?php echo urlencode($materi_nama); ?>"
                                class="flex items-center justify-between w-full bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-2xl font-bold transition-all shadow-lg shadow-blue-600/20 group/btn">
                                <span>Deploy Mission</span>
                                <i class="fas fa-arrow-right -rotate-45 group-hover/btn:rotate-0 transition-transform"></i>
                            </a>
                        <?php else: ?>
                            <button disabled class="flex items-center justify-center gap-2 w-full bg-slate-800/50 text-slate-500 p-4 rounded-2xl font-bold border border-slate-700/50 cursor-not-allowed">
                                <i class="fas fa-lock-open text-emerald-500/50"></i>
                                <span>Mission Cleared</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>

</html>