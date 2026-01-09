<?php
session_start();
include "../../server/config/koneksi.php";
include_once "../../server/auth_check.php";
checkLogin();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../../login.php");
    exit();
}

// mengambil materi
$materi = isset($_GET['materi']) ? mysqli_real_escape_string($conn, $_GET['materi']) : '';

// Mengambil soal secara acak menggunakan ORDER BY RAND()
$query = mysqli_query($conn, "SELECT * FROM quizzes WHERE materi = '$materi' ORDER BY RAND()");
$soal_list = mysqli_fetch_all($query, MYSQLI_ASSOC);

if (count($soal_list) == 0) {
    echo "<script>alert('Belum ada soal untuk kategori ini!'); window.location='dashboard_player.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeMaster - Playing <?php echo htmlspecialchars($materi); ?></title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/tomorrow-night.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Animasi Transisi Soal */
        .fade-enter-active,
        .fade-leave-active {
            transition: all 0.4s ease;
        }

        .fade-enter-from {
            opacity: 0;
            transform: translateX(30px);
        }

        .fade-leave-to {
            opacity: 0;
            transform: translateX(-30px);
        }

        .btn-option {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-option:hover {
            transform: translateX(8px);
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.5);
        }
    </style>
</head>

<body class="text-slate-200 min-h-screen overflow-x-hidden">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full"></div>
    </div>

    <div id="app" class="max-w-4xl mx-auto px-6 py-12 min-h-screen flex flex-col">
        <transition name="fade" mode="out-in">
            <div v-if="showImage" class="fixed inset-0 z-[100] flex items-center justify-center bg-[#0f172a]/90 backdrop-blur-xl p-6">
                <div class="max-w-md w-full text-center">
                    <div class="relative inline-block mb-6">
                        <img src="https://media.tenor.com/gNgtEpVk_fUAAAAM/prabowo-wowo.gif"
                            class="rounded-[2rem] shadow-2xl border-4 border-blue-500/30 animate-float" alt="Great Job!">

                        <div class="absolute -top-6 -right-6 bg-yellow-400 text-slate-900 font-black px-4 py-2 rounded-xl rotate-12 shadow-lg">
                            HIDUP! 🔥
                        </div>
                    </div>

                    <h2 class="text-3xl font-black text-white mb-2">KERJA BAGUS!</h2>
                    <p class="text-slate-400 mb-8 font-medium">Kamu sudah menyelesaikan 5 soal. Istirahat sejenak sebelum lanjut ke tantangan berikutnya!</p>

                    <button @click="closeImage" class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-600/40">
                        LANJUTKAN MISI <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>
            <div v-if="!finished" :key="currentStep" class="flex-1 flex flex-col">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <span class="text-blue-500 font-extrabold tracking-[0.2em] text-xs uppercase mb-1 block">Mission: <?php echo htmlspecialchars($materi); ?></span>
                        <h1 class="text-3xl font-black text-white">Question {{ currentStep + 1 }}<span class="text-slate-500 text-lg font-medium">/{{ soal.length }}</span></h1>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Current XP</p>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-bolt text-yellow-400"></i>
                            <span class="text-3xl font-black text-white tracking-tighter">{{ totalScore }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-full h-1.5 bg-slate-800 rounded-full mb-12 overflow-hidden flex">
                    <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-400 shadow-[0_0_15px_rgba(59,130,246,0.5)] transition-all duration-700"
                        :style="{ width: ((currentStep + 1) / soal.length) * 100 + '%' }"></div>
                </div>

                <div class="glass p-8 md:p-12 rounded-[2rem] shadow-2xl relative overflow-hidden mb-8 border border-white/5">
                    <div class="relative z-10">
                        <p class="text-xl md:text-2xl font-semibold leading-relaxed text-white mb-8">
                            {{ soal[currentStep].soal }}
                        </p>

                        <div v-if="soal[currentStep].snippet" class="rounded-2xl overflow-hidden border border-slate-700 shadow-inner mb-8 group">
                            <div class="bg-slate-800/50 px-4 py-2 flex gap-1.5 border-b border-slate-700">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-500/50"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/50"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-green-500/50"></div>
                            </div>
                            <pre class="!m-0 !bg-[#011627] !p-6 mono text-sm leading-relaxed overflow-x-auto"><code>{{ soal[currentStep].snippet }}</code></pre>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button v-for="opt in ['a','b','c','d']" :key="opt"
                                @click="handleAnswer(opt.toUpperCase())"
                                class="btn-option group p-5 rounded-2xl border border-slate-700 bg-slate-800/40 text-left flex items-center gap-5 hover:shadow-[0_0_20px_rgba(59,130,246,0.15)]">
                                <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-slate-800 border border-slate-700 font-black text-blue-500 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-400 transition-all uppercase">
                                    {{ opt }}
                                </span>
                                <span class="text-slate-300 group-hover:text-white font-medium">{{ soal[currentStep]['opsi_' + opt] }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex-1 flex flex-col items-center justify-center py-12">
                <div class="glass p-12 rounded-[3rem] text-center max-w-md w-full relative shadow-2xl border border-white/10">
                    <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-24 h-24 bg-blue-600 rounded-[2rem] rotate-12 flex items-center justify-center shadow-xl shadow-blue-500/40">
                        <i class="fas fa-trophy text-4xl text-white -rotate-12"></i>
                    </div>

                    <h2 class="text-4xl font-black text-white mt-8 mb-2">Victory!</h2>
                    <p class="text-slate-400 mb-10 italic">"Mission completed successfully. You earned more experience points."</p>

                    <div class="bg-slate-900/50 rounded-2xl p-6 border border-slate-700/50 mb-10">
                        <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Final Experience Points</p>
                        <div class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">
                            {{ totalScore }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <button onclick="location.reload()" class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-600/20">
                            Play Again
                        </button>
                        <a href="dashboard_player.php" class="w-full py-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-2xl transition-all border border-slate-700 text-center">
                            Back to Base
                        </a>
                    </div>
                </div>
            </div>
        </transition>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script>
        const {
            createApp,
            ref,
            onMounted,
            nextTick
        } = Vue;

        createApp({
            setup() {
                const soal = ref(<?php echo json_encode($soal_list); ?>);
                const currentStep = ref(0);
                const totalScore = ref(0);
                const finished = ref(false);
                const showImage = ref(false);

                const completedQuizIds = soal.value.map(s => s.id);

                const highlightCode = () => {
                    nextTick(() => {
                        if (typeof Prism !== 'undefined') Prism.highlightAll();
                    });
                };

                const handleAnswer = (userAns) => {
                    // Cek Jawaban & Tambah Skor
                    if (userAns === soal.value[currentStep.value].jawaban_benar) {
                        totalScore.value += parseInt(soal.value[currentStep.value].score);
                    }

                    // Logika Munculkan Gambar setiap selesai 5 soal
                    if ((currentStep.value + 1) % 5 === 0 && (currentStep.value + 1) < soal.value.length) {
                        showImage.value = true;
                    } else {
                        nextQuestion();
                    }
                };

                const nextQuestion = () => {
                    if (currentStep.value + 1 < soal.value.length) {
                        currentStep.value++;
                        highlightCode();
                    } else {
                        submitFinalResults();
                    }
                };

                const closeImage = () => {
                    showImage.value = false;
                    nextQuestion();
                };

                const submitFinalResults = async () => {
                    finished.value = true;
                    try {
                        const response = await fetch('../../server/api_score.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                quiz_ids: completedQuizIds,
                                skor_akhir: totalScore.value
                            })
                        });
                        const result = await response.json();
                        console.log("Progress Saved:", result.message);
                    } catch (e) {
                        console.error("Critical Error:", e);
                    }
                };

                onMounted(highlightCode);

                return {
                    soal,
                    currentStep,
                    totalScore,
                    finished,
                    showImage,
                    handleAnswer,
                    closeImage
                };
            }
        }).mount('#app');
    </script>
</body>

</html>