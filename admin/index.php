<?php
session_start();
// Hapus include koneksi di sini karena dashboard menggunakan fetch (JS) untuk ambil data
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CodeMaster</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        /* Sidebar Glassmorphism */
        .sidebar-glass {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        }

        /* Card Hover Effect */
        .stat-card {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* Table Styling */
        .table-container {
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        /* Animation */
        .fade-enter-active,
        .fade-leave-active {
            transition: opacity 0.3s ease;
        }

        .fade-enter-from,
        .fade-leave-to {
            opacity: 0;
        }

        [v-cloak] {
            display: none;
        }
    </style>
</head>

<body class="text-slate-800">
    <div id="app" v-cloak class="flex h-screen overflow-hidden bg-slate-50">

        <aside class="w-72 sidebar-glass text-white shadow-2xl z-50 hidden md:flex flex-col">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-10">
                    <div class="bg-blue-600 p-2 rounded-xl">
                        <i class="fas fa-user-shield text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-black tracking-tighter uppercase">Admin<span class="text-blue-500">Hub</span></span>
                </div>

                <nav class="space-y-2">
                    <div @click="activeTab = 'quizzes'" :class="activeTab === 'quizzes' ? 'bg-blue-600 shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="group flex items-center gap-3 p-4 rounded-2xl cursor-pointer transition-all">
                        <i class="fas fa-tasks text-sm"></i>
                        <span class="font-bold">Manage Quizzes</span>
                    </div>
                    <div @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'bg-blue-600 shadow-lg shadow-blue-900/20' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="group flex items-center gap-3 p-4 rounded-2xl cursor-pointer transition-all">
                        <i class="fas fa-chart-line text-sm"></i>
                        <span class="font-bold">Player Statistics</span>
                    </div>
                </nav>
            </div>
            <div class="mt-auto p-8 border-t border-slate-800">
                <a href="../logout.php" class="flex items-center gap-3 p-4 text-red-400 hover:bg-red-500/10 rounded-2xl transition-all font-bold">
                    <i class="fas fa-sign-out-alt"></i> Keluar Sistem
                </a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto custom-scrollbar p-6 md:p-12">

            <div v-if="activeTab === 'quizzes'">
                <header class="flex justify-between items-center mb-10">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Quiz Management</h1>
                        <p class="text-slate-500 font-medium">Buat tantangan coding untuk player.</p>
                    </div>
                </header>

                <div v-if="activeTab === 'quizzes'" class="inline-flex bg-slate-200/50 p-1.5 rounded-2xl mb-8">
                    <button @click="mode = 'single'" :class="mode === 'single' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Single Entry
                    </button>
                    <button @click="mode = 'bulk'" :class="mode === 'bulk' ? 'bg-white text-emerald-600 shadow-md' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-file-import"></i> Bulk Import
                    </button>
                </div>

                <div v-if="mode === 'single'" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-6">
                            <input v-model="newQuiz.judul" type="text" placeholder="Judul Kuis" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                            <div class="grid grid-cols-2 gap-4">
                                <input v-model="newQuiz.materi" type="text" placeholder="Kategori" class="p-4 rounded-2xl form-input bg-slate-50">
                                <input v-model="newQuiz.score" type="number" class="p-4 rounded-2xl form-input bg-slate-50">
                            </div>
                            <textarea v-model="newQuiz.soal" placeholder="Pertanyaan..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-32"></textarea>
                        </div>
                        <textarea v-model="newQuiz.snippet" placeholder="Code Snippet..." class="p-4 w-full rounded-2xl form-input bg-slate-900 text-emerald-400 font-mono text-sm"></textarea>
                        <div class="md:col-span-3 grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <input v-for="opt in ['a','b','c','d']" :key="opt" v-model="newQuiz['opsi_'+opt]" type="text" :placeholder="'Opsi ' + opt.toUpperCase()" class="p-4 rounded-2xl form-input bg-slate-50">
                        </div>
                        <div class="md:col-span-3 flex gap-4">
                            <select v-model="newQuiz.jawaban_benar" class="p-4 rounded-2xl form-input bg-emerald-50 flex-1">
                                <option value="">Pilih Kunci Jawaban</option>
                                <option v-for="ans in ['A','B','C','D']" :value="ans">{{ans}}</option>
                            </select>
                            <button @click="addQuiz" class="bg-slate-900 text-white px-10 rounded-2xl font-bold">Save</button>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200 mb-10">
                    <textarea v-model="bulkText" placeholder='Format [{"judul":"..", "materi":"..", "soal":"..", "opsi_a":"..", "opsi_b":"..", "opsi_c":"..", "opsi_d":"..", "jawaban_benar":"A", "score":10}]' class="p-6 w-full rounded-3xl form-input bg-slate-900 text-blue-400 font-mono h-64 mb-6"></textarea>
                    <button @click="importBulk" class="w-full bg-emerald-600 text-white font-bold py-5 rounded-2xl">Import</button>
                </div>

                <div class="bg-white rounded-[2rem] border overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase">Details</th>
                                <th class="p-5 text-center text-xs font-black text-slate-400 uppercase">XP</th>
                                <th class="p-5 text-right text-xs font-black text-slate-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="q in quizzes" :key="q.id" class="border-t">
                                <td class="p-5 font-bold">{{ q.judul }}</td>
                                <td class="p-5 text-center">{{ q.score }}</td>
                                <td class="p-5 text-right">
                                    <button @click="deleteQuiz(q.id)" class="text-red-500"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="activeTab === 'stats'">
                <header class="mb-10">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Player Performance</h1>
                    <p class="text-slate-500 font-medium">Monitoring performa dan total skor seluruh pemain.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Player</div>
                                <div class="text-3xl font-black text-slate-900">{{ playerStats.length }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div>
                                <div class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Global Avg Score</div>
                                <div class="text-3xl font-black text-emerald-600">{{ averageScore }} <span class="text-sm font-bold text-slate-400">XP</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-slate-50/50 border-b border-slate-100">
                            <tr>
                                <th class="p-6 text-left text-[10px] font-black text-slate-400 uppercase">Player Name</th>
                                <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase">Kuis Dikerjakan</th>
                                <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase">Total XP</th>
                                <th class="p-6 text-right text-[10px] font-black text-slate-400 uppercase">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in playerStats" :key="p.id" class="hover:bg-slate-50/80 transition-all">
                                <td class="p-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-500 uppercase">
                                            {{ p.username.charAt(0) }}
                                        </div>
                                        <span class="font-bold text-slate-800">{{ p.username }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="px-3 py-1 bg-slate-100 rounded-lg font-bold text-slate-600 text-sm">{{ p.total_kuis }}</span>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="font-black text-blue-600">{{ p.total_score.toLocaleString() }} XP</span>
                                </td>
                                <td class="p-6 text-right">
                                    <span class="text-xs text-slate-400 italic">{{ p.terakhir_main }}</span>
                                </td>
                            </tr>
                            <tr v-if="playerStats.length === 0">
                                <td colspan="4" class="p-20 text-center text-slate-400">
                                    <i class="fas fa-user-slash text-4xl mb-4 block opacity-20"></i>
                                    Belum ada player yang terdaftar atau mengerjakan kuis.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        const {
            createApp,
            ref,
            onMounted,
            computed
        } = Vue; // FIX 1: computed ditambahkan di sini

        createApp({
            setup() {
                const activeTab = ref('quizzes');
                const mode = ref('single');
                const quizzes = ref([]);
                const playerStats = ref([]);
                const bulkText = ref('');
                const apiUrl = '../server/api_quiz.php';

                const newQuiz = ref({
                    judul: '',
                    materi: '',
                    soal: '',
                    snippet: '',
                    opsi_a: '',
                    opsi_b: '',
                    opsi_c: '',
                    opsi_d: '',
                    jawaban_benar: '',
                    score: 10
                });

                const fetchQuizzes = async () => {
                    const res = await fetch(apiUrl + '?t=' + Date.now());
                    quizzes.value = await res.json();
                };

                const fetchStats = async () => {
                    try {
                        // Arahkan ke file API yang kita buat di langkah 1
                        const res = await fetch('../server/api_stats.php?t=' + Date.now());
                        const data = await res.json();
                        playerStats.value = data;
                    } catch (e) {
                        console.error("Gagal mengambil data statistik");
                    }
                };

                const averageScore = computed(() => {
                    if (!playerStats.value || playerStats.value.length === 0) return 0;
                    const total = playerStats.value.reduce((acc, curr) => acc + (Number(curr.total_score) || 0), 0);
                    return Math.floor(total / playerStats.value.length);
                });

                const addQuiz = async () => {
                    await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(newQuiz.value)
                    });
                    newQuiz.value = {
                        judul: '',
                        materi: '',
                        soal: '',
                        snippet: '',
                        opsi_a: '',
                        opsi_b: '',
                        opsi_c: '',
                        opsi_d: '',
                        jawaban_benar: '',
                        score: 10
                    };
                    fetchQuizzes();
                };

                const deleteQuiz = async (id) => {
                    if (confirm("Hapus?")) {
                        await fetch(`${apiUrl}?id=${id}`, {
                            method: 'DELETE'
                        });
                        fetchQuizzes();
                    }
                };

                const importBulk = async () => {
                    try {
                        const data = JSON.parse(bulkText.value);
                        for (const item of data) {
                            await fetch(apiUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(item)
                            });
                        }
                        bulkText.value = '';
                        fetchQuizzes();
                    } catch (e) {
                        alert("JSON Salah");
                    }
                };

                onMounted(() => {
                    fetchQuizzes();
                    fetchStats();
                });

                // FIX 3: Gabungkan semua return dalam SATU blok
                return {
                    activeTab,
                    mode,
                    quizzes,
                    playerStats,
                    bulkText,
                    newQuiz,
                    addQuiz,
                    deleteQuiz,
                    importBulk,
                    averageScore
                };
            }
        }).mount('#app');
    </script>
</body>

</html>