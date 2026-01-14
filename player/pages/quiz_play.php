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
    <title>CodeMaster - Rogue Run: <?php echo htmlspecialchars($materi); ?></title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/tomorrow-night.min.css">
    <!-- LottieFiles CDN for animation -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617;
            /* Lebih gelap untuk tema Roguelike */
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Efek Shake jika salah jawab */
        .shake {
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }

        .hp-bar {
            transition: width 0.5s ease-in-out;
        }
    </style>
</head>

<body class="text-slate-200 min-h-screen overflow-x-hidden">

    <div id="app" class="max-w-4xl mx-auto px-6 py-12 min-h-screen flex flex-col" :class="{ 'shake': isWrong }">

        <div class="flex justify-between items-center mb-8 bg-slate-900/50 p-6 rounded-3xl border border-white/5 shadow-2xl">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <i class="fas fa-heart text-red-500 text-3xl"></i>
                    <span class="absolute inset-0 flex items-center justify-center text-[10px] font-black text-white">{{ lives }}</span>
                </div>
                <div class="w-32 h-3 bg-slate-800 rounded-full overflow-hidden border border-white/10">
                    <div class="hp-bar h-full bg-gradient-to-r from-red-600 to-pink-500" :style="{ width: (lives/3)*100 + '%' }"></div>
                </div>
            </div>

            <div class="text-center">
                <span class="text-[10px] text-slate-500 font-black tracking-widest block uppercase">Floor</span>
                <span class="text-2xl font-black text-blue-500 mono">{{ currentStep + 1 }}</span>
            </div>

            <div class="text-right">
                <span class="text-[10px] text-slate-500 font-black tracking-widest block uppercase">XP Collected</span>
                <div class="flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-400 animate-pulse"></i>
                    <span class="text-2xl font-black text-white">{{ totalScore }}</span>
                </div>
            </div>
        </div>

        <transition name="fade" mode="out-in">
            <div v-if="gameOver" class="flex-1 flex flex-col items-center justify-center py-12">
                <div class="glass p-12 rounded-[3rem] text-center max-w-md w-full border-red-500/30 border-2">
                    <div class="w-24 h-24 bg-red-600 rounded-full mx-auto mb-6 flex items-center justify-center shadow-lg shadow-red-600/40">
                        <i class="fas fa-skull text-4xl text-white"></i>
                    </div>
                    <h2 class="text-4xl font-black text-white mb-2">YOU DIED</h2>
                    <p class="text-slate-400 mb-8 text-sm">"Algoritma ini terlalu susah untukmu..."</p>
                    <p class="text-slate-400 mb-8">Belajar lagi... ~(˘▾˘~)</p>
                    <a href="dashboard_player.php" class="block w-full py-4 bg-white text-slate-900 font-black rounded-2xl hover:bg-slate-200 transition-all">
                        TRY NEW RUN
                    </a>
                </div>
            </div>

            <div v-else-if="!finished" :key="currentStep" class="flex-1 flex flex-col">
                <div class="glass p-8 md:p-12 rounded-[2.5rem] shadow-2xl relative overflow-hidden mb-8 border border-white/5">
                    <div class="relative z-10">
                        <p class="text-xl md:text-2xl font-bold leading-relaxed text-white mb-8">
                            {{ soal[currentStep].soal }}
                        </p>

                        <div v-if="soal[currentStep].snippet" class="rounded-2xl overflow-hidden border border-slate-700 shadow-inner mb-8">
                            <pre class="!m-0 !bg-[#011627] !p-6 mono text-sm overflow-x-auto"><code>{{ soal[currentStep].snippet }}</code></pre>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button v-for="opt in ['a','b','c','d']" :key="opt"
                                @click="handleAnswer(opt.toUpperCase())"
                                :disabled="isWrong"
                                class="group p-5 rounded-2xl border border-slate-700 bg-slate-800/20 text-left flex items-center gap-5 hover:border-blue-500 hover:bg-blue-500/5 transition-all">
                                <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-slate-800 border border-slate-700 font-black text-blue-500 group-hover:bg-blue-600 group-hover:text-white transition-all uppercase">
                                    {{ opt }}
                                </span>
                                <span class="text-slate-300 group-hover:text-white font-medium">{{ soal[currentStep]['opsi_' + opt] }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex-1 flex flex-col items-center justify-center py-12">
                <div class="glass p-12 rounded-[3rem] text-center max-w-md w-full border-green-500/30 border-2 relative">
                    <!-- Lottie Animation for Celebration -->
                    <lottie-player
                        src="https://assets2.lottiefiles.com/packages/lf20_jbrw3hcz.json"
                        background="transparent"
                        speed="1"
                        style="width: 120px; height: 120px; margin: 0 auto 24px auto;"
                        autoplay
                        loop
                        id="celebrate-lottie"
                    ></lottie-player>
                    <h2 class="text-4xl font-black text-white mb-2">SURVIVED!</h2>
                    <p class="text-slate-400 mb-8 italic">Kamu MC! Dungeon berhasil ditaklukkan.</p>
                    <p class="text-slate-400 mb-8">ヾ(⌐■_■)ノ♪</p>
                    <div class="bg-slate-900/50 rounded-2xl p-6 mb-8 border border-white/5">
                        <p class="text-[10px] uppercase font-bold text-slate-500 tracking-widest mb-1">Final XP</p>
                        <div class="text-5xl font-black text-green-400">{{ totalScore }}</div>
                    </div>
                    <a href="dashboard_player.php" class="block w-full py-4 bg-green-600 text-white font-black rounded-2xl">EXIT BASE</a>
                </div>
            </div>
        </transition>
    </div>

    <!-- Sound Effects -->
    <audio id="sfx-correct" src="../../player/assets/audio/correct.mp3" preload="auto"></audio>
    <audio id="sfx-wrong" src="../../player/assets/audio/wrong.mp3" preload="auto"></audio>
    <audio id="sfx-celebrate" src="../../player/assets/audio/celebrate.mp3" preload="auto"></audio>
    <audio id="sfx-transition" src="../../player/assets/audio/transition.mp3" preload="auto"></audio>
    <audio id="sfx-gameover" src="../../player/assets/audio/game over.mp3" preload="auto"></audio>
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
                const lives = ref(3); // Sistem Nyawa Roguelike
                const finished = ref(false);
                const gameOver = ref(false);
                const isWrong = ref(false);

                // SFX elements
                let sfxCorrect, sfxWrong, sfxCelebrate, sfxTransition, sfxGameover;

                const highlightCode = () => {
                    nextTick(() => {
                        if (typeof Prism !== 'undefined') Prism.highlightAll();
                    });
                };

                const playSFX = (audio) => {
                    if (audio) {
                        audio.currentTime = 0;
                        audio.play().catch(e => {
                            // Log error for debugging
                            console.warn('Audio play failed:', e);
                        });
                    } else {
                        console.warn('Audio element not found');
                    }
                };

                const handleAnswer = (userAns) => {
                    if (userAns === soal.value[currentStep.value].jawaban_benar) {
                        // Benar: Tambah XP
                        playSFX(sfxCorrect);
                        totalScore.value += parseInt(soal.value[currentStep.value].score);
                        setTimeout(() => {
                            playSFX(sfxTransition);
                            nextQuestion();
                        }, 250);
                    } else {
                        // Salah: Kurangi Nyawa & Shake Efek
                        playSFX(sfxWrong);
                        lives.value--;
                        isWrong.value = true;

                        setTimeout(async () => {
                            isWrong.value = false;
                            if (lives.value <= 0) {
                                playSFX(sfxGameover);
                                gameOver.value = true;
                                // Kirim data kegagalan ke server
                                try {
                                    await fetch('api_quiz_fail.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            quiz_id: soal.value[currentStep.value].id
                                        })
                                    });
                                } catch (e) {}
                            } else {
                                playSFX(sfxTransition);
                                nextQuestion();
                            }
                        }, 500);
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

                const submitFinalResults = async () => {
                    finished.value = true;
                    // Play celebration SFX
                    playSFX(sfxCelebrate);
                    // Kirim XP dan quiz_ids ke server untuk lock quiz
                    try {
                        const quizIds = soal.value.map(q => q.id);
                        await fetch('../../server/api_score.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                skor_akhir: totalScore.value,
                                quiz_ids: quizIds
                            })
                        });
                    } catch (e) {
                        // Optional: tampilkan error jika gagal
                    }
                };

                onMounted(() => {
                    highlightCode();
                    sfxCorrect = document.getElementById('sfx-correct');
                    sfxWrong = document.getElementById('sfx-wrong');
                    sfxCelebrate = document.getElementById('sfx-celebrate');
                    sfxTransition = document.getElementById('sfx-transition');
                    sfxGameover = document.getElementById('sfx-gameover');
                });

                return {
                    soal,
                    currentStep,
                    totalScore,
                    lives,
                    finished,
                    gameOver,
                    isWrong,
                    handleAnswer
                };
            }
        }).mount('#app');
    </script>
</body>

</html>