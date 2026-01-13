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
    ORDER BY q.materi ASC
");


$materi_nama = strtolower($kategori['materi']);

// Mapping Key 
$icon_map = [
    'javascript' => 'fa-js text-yellow-400',
    'js'         => 'fa-js text-yellow-400',
    'python'     => 'fa-python text-blue-500',
    'php'        => 'fa-php text-indigo-400',
    'java'       => 'fa-java text-red-500',
    'html'       => 'fa-html5 text-orange-500',
    'css'        => 'fa-css3-alt text-blue-400',
    'react'      => 'fa-react text-cyan-400',
    'vue'        => 'fa-vuejs text-emerald-500',
    'node'       => 'fa-node-js text-green-500',
    'laravel'    => 'fa-laravel text-red-600',
    'bootstrap'  => 'fa-bootstrap text-purple-500',
    'database'   => 'fa-database text-slate-400',
    'sql'        => 'fa-database text-blue-300',
    'mysql'      => 'fa-database text-blue-500',
    'c++'        => 'fa-cuttlefish text-blue-600',
    'c#'         => 'fa-hashtag text-purple-600',
    'ruby'       => 'fa-gem text-red-400',
    'swift'      => 'fa-swift text-orange-600',
    'docker'     => 'fa-docker text-blue-400',
    'github'     => 'fa-github text-slate-200',
    'git'        => 'fa-git-alt text-orange-500',
    'angular'    => 'fa-angular text-red-600',
    'figma'      => 'fa-figma text-purple-400',
    'sass'       => 'fa-sass text-pink-400',
];

// Default icon jika tidak ditemukan
$icon_class = 'fa-terminal text-blue-500';

// Cek apakah ada kata kunci yang cocok dalam nama materi
foreach ($icon_map as $key => $class) {
    if (stripos($materi_nama, $key) !== false) {
        $icon_class = $class;
        break;
    }
}
$total_misi_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM quizzes");
$total_misi_data = mysqli_fetch_assoc($total_misi_query);
$total_soal_global = $total_misi_data['total'];

$selesai_query = mysqli_query($conn, "SELECT COUNT(DISTINCT quiz_id) as total FROM quiz_history WHERE user_id = '$user_id'");
$selesai_data = mysqli_fetch_assoc($selesai_query);
$total_selesai_global = $selesai_data['total'];

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

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.4), rgba(15, 23, 42, 0.8));
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
                <a href="profile.php" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 w-10 h-10 flex items-center justify-center rounded-xl border border-blue-500/20 transition">
                    <i class="fas fa-user"></i>
                </a>
                <a href="../../logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 w-10 h-10 flex items-center justify-center rounded-xl border border-red-500/20 transition">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 mt-10">

        <div class="mt-8 mb-12 p-8 rounded-[2.5rem] bg-slate-900/40 border border-white/5 relative overflow-hidden">
            <div class="flex flex-col md:flex-row gap-8 items-center relative z-10">
                <div class="w-20 h-20 rounded-full bg-indigo-500/20 flex items-center justify-center border border-indigo-500/30">
                    <i class="fas fa-user-shield text-3xl text-indigo-400"></i>
                </div>
                <div>
                    <h4 class="text-xl font-black text-white mb-2 uppercase tracking-tight">System Status: <span class="text-indigo-400">Stable</span></h4>
                    <p class="text-slate-400 leading-relaxed max-w-2xl">
                        Selamat datang kembali, <span class="text-white font-bold"><?php echo $_SESSION['username']; ?></span>.
                        Dungeon algoritma telah diperbarui dengan tantangan baru. Ingat, dalam mode <span class="text-red-400 italic font-bold">Roguelike</span>, ketelitian adalah kunci.
                        Satu kesalahan kecil pada sintaks bisa mengakhiri "perjalanan" kamu. Pilih misimu dengan bijak.
                    </p>
                </div>
            </div>
        </div>

        <!--EXP collect-->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="lg:col-span-2 xp-gradient p-8 rounded-[2.5rem] relative overflow-hidden flex flex-col justify-center min-h-[220px]">
                <div class="relative z-10">
                    <h3 class="text-blue-100/80 font-bold uppercase tracking-widest text-xs mb-2">My Total Progression</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-7xl font-black text-white"><?php echo number_format($total_xp); ?></span>
                        <span class="text-2xl font-bold text-blue-200">XP</span>
                    </div>
                    <div>
                        <p class="text-sm text-blue-100/80 mt-2">Misi selesai: <span class="font-bold"><?php echo $total_selesai_global; ?></span> dari <span class="font-bold"><?php echo $total_soal_global; ?></span> total misi.</p>
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

        <!--live history-->
        <div class="glass-card p-6 rounded-[2.5rem] mb-6 border-l-4 border-blue-500">
            <h3 class="text-xs font-black text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-users animate-pulse"></i> Live Quiz Completion
            </h3>
            <div id="live-quiz-history" class="space-y-3 font-mono text-[10px]">
                <div class="text-slate-400">Loading...</div>
            </div>
            <script>
                async function fetchQuizHistory() {
                    try {
                        const res = await fetch('api_quiz_history.php');
                        const data = await res.json();
                        const container = document.getElementById('live-quiz-history');
                        if (!Array.isArray(data) || data.length === 0) {
                            container.innerHTML = '<div class="text-slate-500">Belum ada player yang menyelesaikan quiz.</div>';
                            return;
                        }
                        container.innerHTML = data.map(item => {
                            let statusColor = item.status === 'DONE' ? 'text-green-400' : 'text-red-500';
                            let statusText = item.status === 'DONE' ? '[DONE]' : '[FAILED]';
                            let actionText = item.status === 'DONE' ? 'menyelesaikan' : 'gagal menyelesaikan';
                            let waktu = item.waktu || item.selesai_pada;
                            return `<div class="flex gap-2 items-center">` +
                                `<span class="${statusColor}">${statusText}</span>` +
                                `<span class="text-slate-300"><b>${item.username}</b> ${actionText} <b>${item.judul}</b> (<span class="text-blue-400">${item.materi}</span>)</span>` +
                                `<span class="text-slate-500 ml-auto">${new Date(waktu).toLocaleString('id-ID', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })}</span>` +
                                `</div>`;
                        }).join('');
                    } catch (e) {
                        document.getElementById('live-quiz-history').innerHTML = '<div class="text-red-500">Gagal memuat data.</div>';
                    }
                }
                fetchQuizHistory();
                setInterval(fetchQuizHistory, 10000);
            </script>
        </div>

        <!--help center-->
        <div class="fixed bottom-6 right-6 z-[100] group">
            <div class="absolute bottom-full right-0 mb-4 w-64 p-4 bg-slate-900 border border-blue-500/30 rounded-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none transform translate-y-2 group-hover:translate-y-0">
                <p class="text-[10px] font-black text-blue-400 uppercase mb-2">Pro-Tip Agent:</p>
                <p id="pro-tip-text" class="text-xs text-slate-300 leading-relaxed italic">
                    "Selalu gunakan strict equality (===) di Javascript untuk menghindari bug tipe data."
                </p>
            </div>

            <button onclick="changeTip()" class="w-14 h-14 bg-blue-600 rounded-full flex items-center justify-center shadow-2xl shadow-blue-600/40 hover:scale-110 transition-transform">
                <i class="fas fa-headset text-white text-xl"></i>
            </button>
        </div>

        <script>
            const tips = [
                "Ingat: Array di sebagian besar bahasa pemrograman dimulai dari index 0.",
                "Gunakan 'git commit -m' untuk memberikan pesan yang jelas pada perubahan kodenya.",
                "Roguelike Tips: Fokus pada satu bahasa sampai Master sebelum pindah ke dungeon lain.",
                "DRY: Don't Repeat Yourself. Jika kode diulang, buatlah fungsi!"
            ];

            function changeTip() {
                const tipText = document.getElementById('pro-tip-text');
                tipText.innerText = tips[Math.floor(Math.random() * tips.length)];
            }
        </script>

        <h2 class="text-2xl font-black text-white mb-8 flex items-center gap-3 uppercase tracking-tight">
            <span class="w-2 h-8 bg-blue-600 rounded-full"></span> Available Dungeons Missions
        </h2>

        <div id="materi-container">
            <h2 id="heading-active" class="text-xl font-black text-white mb-2 flex items-center gap-3">
                <i class="fas fa-swords text-blue-500"></i> Active Missions
            </h2>
            <!-- Category Toggle: Quiz vs Lessons -->
            <div class="mb-6 flex items-center gap-2">
                <button id="tab-quiz" class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-600 text-white">Quiz</button>
                <button id="tab-lesson" class="px-3 py-1.5 rounded-full text-xs font-bold bg-slate-800 text-blue-400">Lessons</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16" id="materi-list-active">
                <?php
                // Reset pointer ke awal
                mysqli_data_seek($query_kategori, 0);
                $has_active = false;
                $completed = [];
                while ($kategori = mysqli_fetch_assoc($query_kategori)):
                    $total = $kategori['jumlah_soal'];
                    $selesai = $kategori['soal_selesai'];
                    $is_fully_done = ($total > 0 && $total == $selesai);
                    if (!$is_fully_done):
                        $has_active = true;
                        renderCard($kategori, false);
                    else:
                        $completed[] = $kategori;
                    endif;
                endwhile;
                if (!$has_active) echo "<p class='col-span-full text-slate-500 italic'>Semua misi di kategori ini telah selesai!</p>";
                ?>
            </div>
            <h2 id="heading-completed" class="text-xl font-black text-slate-400 mb-6 flex items-center gap-3">
                <i class="fas fa-check-double text-emerald-500"></i> Completed Missions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 opacity-75" id="materi-list-completed">
                <?php
                foreach ($completed as $kategori):
                    renderCard($kategori, true);
                endforeach;
                if (empty($completed)) echo "<p class='col-span-full text-slate-500 italic'>Belum ada misi yang selesai!</p>";
                ?>
            </div>

            <!-- Lessons Category Content -->
            <div id="lesson-dashboard-container" class="hidden">
                <h2 class="text-xl font-black text-white mb-4 flex items-center gap-3">
                    <i class="fas fa-book-open text-emerald-500"></i> Lessons
                </h2>
                <div id="lesson-list-dashboard" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Ambil semua materi
                $materi = [];
                $resMateri = mysqli_query($conn, "SELECT * FROM materi_lesson ORDER BY id ASC");
                while ($row = mysqli_fetch_assoc($resMateri)) { $materi[] = $row; }

                // Ambil lesson per materi
                $lessonsByMateri = [];
                $resLessons = mysqli_query($conn, "SELECT id, title, order_no, materi_id FROM lessons ORDER BY order_no ASC");
                while ($row = mysqli_fetch_assoc($resLessons)) {
                    $lessonsByMateri[$row['materi_id']][] = $row;
                }

                // Ambil progress user
                $progressIds = [];
                $stmtProg = $conn->prepare("SELECT lesson_id FROM progress WHERE user_id = ? AND completed = 1");
                $stmtProg->bind_param("i", $user_id);
                $stmtProg->execute();
                $rsProg = $stmtProg->get_result();
                while ($r = $rsProg->fetch_assoc()) { $progressIds[] = intval($r['lesson_id']); }
                $stmtProg->close();

                // Icon mapping
                function materiIconClass($icon) {
                    $fa = [
                        'fa-html5' => 'fab fa-html5 text-orange-500',
                        'fa-css3-alt' => 'fab fa-css3-alt text-blue-400',
                        'fa-js' => 'fab fa-js text-yellow-400',
                        'fa-php' => 'fab fa-php text-indigo-400',
                        'fa-python' => 'fab fa-python text-blue-500',
                        'fa-java' => 'fab fa-java text-red-500',
                    ];
                    return $fa[$icon] ?? 'fas fa-terminal text-blue-500';
                }

                // Render card per materi
                foreach ($materi as $m) {
                    $mid = $m['id'];
                    $lessons = $lessonsByMateri[$mid] ?? [];
                    $total = count($lessons);
                    $done = 0;
                    foreach ($lessons as $l) {
                        if (in_array($l['id'], $progressIds)) $done++;
                    }
                    $percent = $total > 0 ? round(($done/$total)*100) : 0;
                    $iconClass = materiIconClass($m['icon']);
                    echo '<div class="category-card glass-card rounded-[2.5rem] overflow-hidden flex flex-col group relative">';
                    echo '  <i class="'.$iconClass.' absolute -right-4 -top-4 opacity-[0.03] rotate-12 group-hover:rotate-0 group-hover:opacity-[0.07] transition-all duration-700 pointer-events-none" style="font-size:10rem"></i>';
                    echo '  <div class="relative p-6 flex flex-col h-full z-10">';
                    echo '    <div class="flex justify-between items-start mb-6">';
                    echo '      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center border border-white/10 shadow-2xl group-hover:scale-110 group-hover:border-indigo-500/50 transition-all duration-500">';
                    echo '        <i class="'.$iconClass.' text-2xl"></i>';
                    echo '      </div>';
                    $statusClass = ($done==$total && $total>0) ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-500' : 'bg-indigo-500/10 border border-indigo-500/20 text-indigo-400';
                    $dotColor = ($done==$total && $total>0) ? 'bg-emerald-500 animate-pulse' : 'bg-indigo-400';
                    $statusText = ($done==$total && $total>0) ? 'Done' : 'Active';
                    echo '      <div class="flex items-center gap-1.5 ' . $statusClass . ' px-3 py-1 rounded-full backdrop-blur-md">';
                    echo '        <span class="w-1.5 h-1.5 ' . $dotColor . ' rounded-full"></span>';
                    echo '        <span class="text-[9px] font-black uppercase tracking-widest">' . $statusText . '</span>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '    <div class="mb-4">';
                    echo '      <h3 class="text-lg font-extrabold text-white tracking-tight group-hover:text-indigo-400 transition-colors">'.htmlspecialchars($m['nama']).'</h3>';
                    echo '      <p class="text-slate-400 text-xs leading-relaxed opacity-80">'.($total).' Lesson</p>';
                    echo '    </div>';
                    echo '    <div class="mt-auto space-y-3 mb-6 bg-slate-900/50 p-4 rounded-3xl border border-white/5 group-hover:border-indigo-500/20 transition-colors">';
                    echo '      <div class="flex justify-between items-end">';
                    echo '        <div>'; 
                    echo '          <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter mb-1">Completion Rate</p>';
                    echo '          <p class="text-lg font-black text-white">'.$percent.'<span class="text-xs text-slate-500 ml-0.5">%</span></p>';
                    echo '        </div>';
                    echo '        <p class="text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-1 rounded-md">'.$done.' <span class="text-slate-600">/</span> '.$total.'</p>';
                    echo '      </div>';
                    echo '      <div class="h-2 w-full bg-slate-800 rounded-full overflow-hidden p-[2px]">';
                    echo '        <div class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r '.($done==$total && $total>0?'from-emerald-600 to-teal-400 shadow-[0_0_10px_rgba(16,185,129,0.4)]':'from-indigo-600 to-blue-400 shadow-[0_0_10px_rgba(79,70,229,0.4)]').'" style="width: '.$percent.'%"></div>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '    <a href="lesson/index.php?materi='.$mid.'" class="group/btn relative flex items-center justify-center w-full '.($done==$total && $total>0?'bg-emerald-600 hover:bg-emerald-500':'bg-indigo-600 hover:bg-indigo-500').' text-white py-3 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all shadow-lg '.($done==$total && $total>0?'shadow-emerald-600/20':'shadow-indigo-600/20').' active:scale-95 overflow-hidden">';
                    echo '      <span class="relative z-10 flex items-center gap-2">Lihat Lesson <i class="fas fa-arrow-right text-[10px] group-hover/btn:translate-x-1 transition-transform"></i></span>';
                    echo '      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>';
                    echo '    </a>';
                    echo '  </div>';
                    echo '</div>';
                }
                ?>
                </div>
            </div>
            <script>
                // Category toggle logic
                const tabQuiz = document.getElementById('tab-quiz');
                const tabLesson = document.getElementById('tab-lesson');
                const activeGrid = document.getElementById('materi-list-active');
                const completedGrid = document.getElementById('materi-list-completed');
                const headingActive = document.getElementById('heading-active');
                const headingCompleted = document.getElementById('heading-completed');
                const lessonContainer = document.getElementById('lesson-dashboard-container');

                function selectCategory(cat) {
                    const isQuiz = cat === 'quiz';
                    tabQuiz.className = isQuiz ? 'px-3 py-1.5 rounded-full text-xs font-bold bg-blue-600 text-white' : 'px-3 py-1.5 rounded-full text-xs font-bold bg-slate-800 text-blue-400';
                    tabLesson.className = !isQuiz ? 'px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-600 text-white' : 'px-3 py-1.5 rounded-full text-xs font-bold bg-slate-800 text-blue-400';
                    activeGrid.classList.toggle('hidden', !isQuiz);
                    completedGrid.classList.toggle('hidden', !isQuiz);
                    headingCompleted.classList.toggle('hidden', !isQuiz);
                    lessonContainer.classList.toggle('hidden', isQuiz);
                }

                tabQuiz.addEventListener('click', () => selectCategory('quiz'));
                tabLesson.addEventListener('click', () => selectCategory('lesson'));
                // Default tab
                selectCategory('quiz');

                // Lessons are rendered server-side above; no client fetch needed here.
                function filterBahasa(idmateri, btn) {
                    var items = document.querySelectorAll('.category-card');
                    items.forEach(function(item) {
                        if (idmateri === 'all' || item.getAttribute('data-idmateri') === idmateri) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                    var buttons = document.querySelectorAll('.kategori-btn');
                    buttons.forEach(function(b) { b.classList.remove('bg-blue-600', 'text-white'); b.classList.add('bg-slate-800', 'text-blue-400'); });
                    btn.classList.add('bg-blue-600', 'text-white');
                    btn.classList.remove('bg-slate-800', 'text-blue-400');
                }
            </script>
        </div>

        <?php
        // untuk merender kartu agar kode tidak duplikat
        function renderCard($kategori, $is_fully_done)
        {
            $materi_nama = $kategori['materi'];
            $id_materi = isset($kategori['id_materi']) ? $kategori['id_materi'] : '';
            $total = $kategori['jumlah_soal'];
            $selesai = $kategori['soal_selesai'];

            $icon_map = [
                'javascript' => 'fab fa-js text-yellow-400',
                'js' => 'fab fa-js text-yellow-400',
                'python' => 'fab fa-python text-blue-500',
                'php' => 'fab fa-php text-indigo-400',
                'java' => 'fab fa-java text-red-500',
                'html' => 'fab fa-html5 text-orange-500',
                'css' => 'fab fa-css3-alt text-blue-400',
                'react' => 'fab fa-react text-cyan-400',
                'database' => 'fas fa-database text-slate-400',
                'mysql' => 'fas fa-database text-blue-500'
            ];

            $icon_class = 'fas fa-terminal text-blue-500';
            foreach ($icon_map as $key => $class) {
                if (stripos(strtolower($materi_nama), $key) !== false) {
                    $icon_class = $class;
                    break;
                }
            }
        ?>
            <div class="category-card glass-card rounded-[2.5rem] overflow-hidden flex flex-col group relative" data-materi="<?php echo htmlspecialchars($materi_nama); ?>" data-idmateri="<?php echo htmlspecialchars($id_materi); ?>">
    <i class="<?php echo $icon_class; ?> absolute -right-4 -top-4 text-8 opacity-[0.03] rotate-12 group-hover:rotate-0 group-hover:opacity-[0.07] transition-all duration-700 pointer-events-none" style="font-size: 10rem;"></i>

    <div class="relative p-8 flex flex-col h-full z-10">
        <div class="flex justify-between items-start mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center border border-white/10 shadow-2xl group-hover:scale-110 group-hover:border-indigo-500/50 transition-all duration-500">
                <i class="<?php echo $icon_class; ?> text-3xl"></i>
            </div>
            
            <?php if ($is_fully_done): ?>
                <div class="flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full backdrop-blur-md">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Done</span>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-1.5 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1 rounded-full backdrop-blur-md">
                    <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full"></span>
                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Active</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-6">
            <h3 class="text-2xl font-extrabold text-white mb-2 tracking-tight group-hover:text-indigo-400 transition-colors">
                <?php echo htmlspecialchars($materi_nama); ?>
            </h3>
            <p class="text-slate-400 text-xs leading-relaxed opacity-80">
                Uji kemampuan algoritma dan logika sistem pada modul <?php echo htmlspecialchars($materi_nama); ?>.
            </p>
        </div>

        <div class="mt-auto space-y-3 mb-8 bg-slate-900/50 p-5 rounded-3xl border border-white/5 group-hover:border-indigo-500/20 transition-colors">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter mb-1">Completion Rate</p>
                    <p class="text-xl font-black text-white">
                        <?php echo ($total > 0) ? round(($selesai / $total) * 100) : 0; ?><span class="text-xs text-slate-500 ml-0.5">%</span>
                    </p>
                </div>
                <p class="text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-1 rounded-md">
                    <?php echo $selesai; ?> <span class="text-slate-600">/</span> <?php echo $total; ?>
                </p>
            </div>
            <div class="h-2 w-full bg-slate-800 rounded-full overflow-hidden p-[2px]">
                <div class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r <?php echo $is_fully_done ? 'from-emerald-600 to-teal-400 shadow-[0_0_10px_rgba(16,185,129,0.4)]' : 'from-indigo-600 to-blue-400 shadow-[0_0_10px_rgba(79,70,229,0.4)]'; ?>"
                    style="width: <?php echo ($total > 0) ? ($selesai / $total) * 100 : 0; ?>%">
                </div>
            </div>
        </div>

        <?php if (!$is_fully_done): ?>
            <a href="quiz_play.php?materi=<?php echo urlencode($materi_nama); ?>"
                class="group/btn relative flex items-center justify-center w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all shadow-lg shadow-indigo-600/20 active:scale-95 overflow-hidden">
                <span class="relative z-10 flex items-center gap-2">
                    Deploy Mission <i class="fas fa-rocket text-[10px] group-hover/btn:translate-x-1 group-hover/btn:-translate-y-1 transition-transform"></i>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
            </a>
        <?php else: ?>
            <div class="flex items-center justify-center gap-3 w-full bg-emerald-500/5 text-emerald-500/50 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] border border-emerald-500/10">
                <i class="fas fa-certificate text-emerald-500"></i>
                Mission Cleared
            </div>
        <?php endif; ?>
    </div>
</div>
        <?php
        }
        ?>

        <script>
            function filterMateri(materi, btn) {
                var items = document.querySelectorAll('.category-card');
                items.forEach(function(item) {
                    if (materi === 'all' || item.getAttribute('data-materi') === materi) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });

                var btns = document.querySelectorAll('.kategori-btn');
                btns.forEach(b => b.className = 'kategori-btn bg-slate-800 text-blue-400 px-4 py-2 rounded-full font-bold text-xs');
                btn.className = 'kategori-btn bg-blue-600 text-white px-4 py-2 rounded-full font-bold text-xs';
            }
        </script>
    </main>
</body>

<footer class="mt-20 border-t border-slate-800 bg-slate-900/50 backdrop-blur-md pt-16 pb-8">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center gap-2 mb-6">
                    <div class="bg-blue-600 p-2 rounded-lg shadow-lg shadow-blue-600/20">
                        <i class="fas fa-code text-white"></i>
                    </div>
                    <span class="font-black text-xl tracking-tighter text-white uppercase">
                        CODE<span class="text-blue-500">MASTER</span>
                    </span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    Platform dungeon-crawler untuk mengasah logika algoritma. Hadapi tantangan, kumpulkan XP, dan jadilah Master di dunia sintaks.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:text-blue-500 hover:border-blue-500/50 border border-transparent transition-all">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:text-pink-500 hover:border-pink-500/50 border border-transparent transition-all">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:text-blue-400 hover:border-blue-400/50 border border-transparent transition-all">
                        <i class="fab fa-discord"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-white font-black text-xs uppercase tracking-[0.2em] mb-6">Navigation</h4>
                <ul class="space-y-4">
                    <li><a href="#" class="text-slate-500 hover:text-blue-400 text-sm transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Dashboard</a></li>
                    <li><a href="leaderboard_page.php" class="text-slate-500 hover:text-blue-400 text-sm transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Leaderboard</a></li>
                    <li><a href="profile.php" class="text-slate-500 hover:text-blue-400 text-sm transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px]"></i> Player Profile</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-black text-xs uppercase tracking-[0.2em] mb-6">Active</h4>
                <ul class="space-y-4 text-slate-500 text-sm">
                    <li class="flex items-center gap-2"><i class="fab fa-js text-yellow-500/50"></i> Javascript Logic</li>
                    <li class="flex items-center gap-2"><i class="fab fa-python text-blue-500/50"></i> Python Chamber</li>
                    <li class="flex items-center gap-2"><i class="fas fa-database text-slate-500/50"></i> SQL Fortress</li>
                </ul>
            </div>

            <div class="bg-slate-800/30 p-6 rounded-[2rem] border border-white/5">
                <h4 class="text-white font-black text-xs uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> System Status
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-500">SERVER:</span>
                        <span class="text-green-400 uppercase">Online</span>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-500">VERSION:</span>
                        <span class="text-blue-400">v2.4.0-stable</span>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-500">LATENCY:</span>
                        <span class="text-slate-400">24ms</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-slate-600 text-[10px] font-bold uppercase tracking-widest">
                &copy; 24552011212_Aldi Alfariz_TIF 23 RP CNS A_UASWEB1
            </p>
            <div class="flex gap-8">
                <a href="#" class="text-slate-600 hover:text-slate-400 text-[10px] font-bold uppercase tracking-widest">Privacy Policy</a>
                <a href="#" class="text-slate-600 hover:text-slate-400 text-[10px] font-bold uppercase tracking-widest">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

</html>