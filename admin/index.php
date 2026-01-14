<?php
session_start();
include_once "../server/auth_check.php";
checkLogin();
if (!isAdmin()) {
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

        :root {
            --admin-primary: #6366f1;
            --admin-dark: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Sidebar Premium */
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        }

 
        .glass-panel {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
        }


        .stat-card-premium {
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
        }

        .stat-card-premium:hover {
            transform: translateY(-8px);
            border-color: var(--admin-primary);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.1);
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        [v-cloak] {
            display: none;
        }
    </style>
</head>

<body class="text-slate-800">
    <div id="app" v-cloak class="flex h-screen overflow-hidden bg-slate-50">

        <aside class="w-80 sidebar-gradient text-white shadow-2xl z-50 hidden md:flex flex-col relative">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-12">
                    <div class="bg-indigo-500 p-3 rounded-2xl shadow-lg shadow-indigo-500/40 rotate-3">
                        <i class="fas fa-terminal text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tighter uppercase block leading-none">Admin<span class="text-indigo-400">Panel</span></span>
                        <span class="text-[10px] text-slate-400 font-bold tracking-[0.2em] uppercase">Control Center</span>
                    </div>
                </div>

                <nav class="space-y-3">
                    <div v-for="item in [{id:'materi', icon:'book', label:'Materi'}, {id:'lessons', icon:'graduation-cap', label:'Lessons'}, {id:'quizzes', icon:'code', label:'Quizzes'}, {id:'stats', icon:'chart-pie', label:'Analytics'}]"
                        @click="activeTab = item.id"
                        :class="activeTab === item.id ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-900/40' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                        class="group flex items-center gap-4 p-4 rounded-2xl cursor-pointer transition-all duration-300">
                        <i :class="'fas fa-' + item.icon" class="text-lg"></i>
                        <span class="font-bold tracking-tight">{{ item.label }}</span>
                        <i v-if="activeTab === item.id" class="fas fa-chevron-right ml-auto text-xs opacity-50"></i>
                    </div>
                </nav>
            </div>

            <div class="mt-auto p-8">
                <div class="bg-white/5 rounded-3xl p-6 border border-white/5 mb-6">
                    <p class="text-xs text-slate-500 font-bold uppercase mb-2">Logged in as</p>
                    <p class="text-sm font-bold truncate"><?php echo $_SESSION['username']; ?></p>
                </div>
                <a href="../logout.php" class="flex items-center justify-center gap-3 p-4 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-2xl transition-all font-black text-xs uppercase tracking-widest">
                    <i class="fas fa-power-off"></i> Secure Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto custom-scrollbar p-6 md:p-12">


            <!--OPSI MATERI-->
            <div v-if="activeTab === 'materi'">
                <header class="flex justify-between items-center mb-10">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Materi Management</h1>
                        <p class="text-slate-500 font-medium">Kelola kategori materi untuk quiz.</p>
                    </div>
                </header>
                <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200 mb-10">
                    <div class="flex gap-4 mb-6">
                        <input v-model="newMateri" type="text" placeholder="Nama Materi" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                        <button @click="addMateri" class="bg-blue-600 text-white px-8 rounded-2xl font-bold">Tambah</button>
                    </div>
                    <div class="bg-white rounded-[2rem] border overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="p-5 text-xs font-black text-slate-400 uppercase">Materi</th>
                                    <th class="p-5 text-right text-xs font-black text-slate-400 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in materiList" :key="m.id" class="border-t">
                                    <td class="p-5 font-bold flex items-center gap-3">
                                        <i :class="getMateriIcon(m.nama)" class="text-xl"></i> {{ m.nama }}
                                    </td>
                                    <td class="p-5 text-right">
                                        <button @click="deleteMateri(m.id)" class="text-red-500"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr v-if="materiList.length === 0">
                                    <td colspan="2" class="p-10 text-center text-slate-400">Belum ada materi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!--LESSONS-->
            <div v-if="activeTab === 'lessons'">
                <header class="flex justify-between items-center mb-10">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Lesson Management</h1>
                        <p class="text-slate-500 font-medium">Buat pelajaran interaktif untuk player.</p>
                    </div>
                </header>

                <div class="inline-flex bg-slate-200/50 p-1.5 rounded-2xl mb-8">
                    <button @click="lessonMode = 'single'" :class="lessonMode === 'single' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Single Entry
                    </button>
                    <button @click="lessonMode = 'bulk'" :class="lessonMode === 'bulk' ? 'bg-white text-emerald-600 shadow-md' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-file-import"></i> Bulk Import
                    </button>
                </div>

                <div v-if="lessonMode === 'single'" class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-200 mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-6">
                            <input v-model="newLesson.title" type="text" placeholder="Judul Lesson" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                            <div class="grid grid-cols-3 gap-4">
                                <select v-model="newLesson.materi_id" class="p-4 rounded-2xl form-input bg-slate-50">
                                    <option value="">Pilih Materi</option>
                                    <option v-for="m in materiList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                                </select>
                                <input v-model="newLesson.order_no" type="number" placeholder="Order No" class="p-4 rounded-2xl form-input bg-slate-50">
                                <input v-model="newLesson.exp" type="number" placeholder="EXP Points" class="p-4 rounded-2xl form-input bg-slate-50">
                            </div>
                            <textarea v-model="newLesson.content" placeholder="Soal Pelajaran..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-64"></textarea>
                        </div>

                        <div class="md:col-span-3 lg:col-span-1">
                            <div class="relative bg-[#1e2235] p-8 rounded-[2rem] shadow-2xl border border-white/5 group">

                                <div class="flex gap-2 mb-8 px-2">
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#ff5f56] shadow-lg shadow-red-500/20"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#ffbd2e] shadow-lg shadow-amber-500/20"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#27c93f] shadow-lg shadow-emerald-500/20"></div>
                                </div>

                                <div class="relative">
                                    <div class="absolute -inset-2"></div>

                                    <textarea
                                        v-model="newLesson.starter_code"
                                        placeholder="// Starter code untuk pelajaran..."
                                        class="relative w-full h-[320px] bg-transparent text-[#e1e4e8] font-mono text-sm leading-relaxed outline-none resize-none custom-scrollbar spell-none"
                                        style="color: #9cdceb;"
                                        spellcheck="false"></textarea>
                                </div>

                                <div class="absolute bottom-6 right-8 opacity-20 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-terminal text-xs text-indigo-400"></i>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-3 space-y-6">
                            <div>
                                <label class="block text-xs font-black text-slate-600 uppercase mb-2">Validation Rules (JSON)</label>
                                <textarea v-model="newLesson.validation_rules" placeholder='{"language": "html", "required_tags": ["h1"], "min_counts": {"h1": 1}, "require_non_empty_text_tags": ["h1"]}' class="p-4 w-full rounded-2xl form-input bg-slate-50 h-24 font-mono text-xs"></textarea>
                                <p class="text-[10px] text-slate-500 mt-2">Required: Include "language" field (html/css/javascript/php/python). Optional: required_tags, min_counts, require_non_empty_text_tags, required_contains, required_regex</p>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <button @click="addLesson" class="w-full bg-slate-900 text-white px-10 py-4 rounded-2xl font-bold">Save Lesson</button>
                        </div>
                    </div>
                </div>

                <!--BULK IMPORT LESSONS-->
                <div v-else class="space-y-6 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="relative bg-[#1e2235] p-8 rounded-[2rem] shadow-2xl border border-white/5 group">

                        <div class="flex items-center justify-between mb-8 px-2">
                            <div class="flex gap-2">
                                <div class="w-3.5 h-3.5 rounded-full bg-[#ff5f56] shadow-lg shadow-red-500/20"></div>
                                <div class="w-3.5 h-3.5 rounded-full bg-[#ffbd2e] shadow-lg shadow-amber-500/20"></div>
                                <div class="w-3.5 h-3.5 rounded-full bg-[#27c93f] shadow-lg shadow-emerald-500/20"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-blue-400 animate-pulse"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Batch_Lessons.json</span>
                                <a href="./bulk_lesson_format.json" download class="ml-3 px-3 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-bold hover:bg-emerald-200 transition-all border border-emerald-200 flex items-center gap-1">
                                    <i class="fas fa-download"></i> Download Format
                                </a>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -inset-4"></div>

                            <textarea
                                v-model="bulkLessonText"
                                placeholder='[{"title": "HTML Basics", "materi_id": 1, ...}]'
                                class="relative w-full h-80 bg-transparent text-blue-300 font-mono text-sm leading-relaxed outline-none resize-none custom-scrollbar"
                                spellcheck="false"></textarea>
                        </div>

                        <div class="absolute bottom-6 right-8 opacity-40">
                            <span class="text-[9px] font-black text-slate-500 border border-slate-700 px-2 py-1 rounded">JSON ARRAY</span>
                        </div>
                    </div>

                    <button @click="importBulkLessons"
                        class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black py-5 rounded-[2rem] hover:shadow-2xl hover:shadow-emerald-500/30 transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                        <i class="fas fa-layer-group"></i>
                        LAUNCH BATCH IMPORT
                    </button>
                </div>

                <div class="bg-white rounded-[2rem] border overflow-hidden">
                    <div class="flex gap-4 p-5">
                        <select v-model="selectedMateriLesson" class="p-4 rounded-2xl form-input bg-slate-50">
                            <option value="">Semua Materi</option>
                            <option v-for="m in materiList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                        </select>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase">No</th>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase">Judul</th>
                                <th class="p-5 text-center text-xs font-black text-slate-400 uppercase">Materi</th>
                                <th class="p-5 text-center text-xs font-black text-slate-400 uppercase">EXP</th>
                                <th class="p-5 text-right text-xs font-black text-slate-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="l in filteredLessons" :key="l.id" class="border-t">
                                <td class="p-5 font-bold">{{ l.order_no }}</td>
                                <td class="p-5 font-bold">{{ l.title }}</td>
                                <td class="p-5 text-center">{{ getMateriNameById(l.materi_id) }}</td>
                                <td class="p-5 text-center"><span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">{{ l.exp || 10 }} XP</span></td>
                                <td class="p-5 text-right flex gap-2 justify-end">
                                    <button @click="showEditLesson(l)" class="text-blue-500"><i class="fas fa-edit"></i></button>
                                    <button @click="deleteLesson(l.id)" class="text-red-500"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr v-if="filteredLessons.length === 0">
                                <td colspan="4" class="p-10 text-center text-slate-400">Belum ada lesson.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <!-- Modal Edit Lesson -->
                <div v-if="editLessonModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                    <div class="bg-white rounded-3xl p-8 w-full max-w-2xl shadow-xl relative">
                        <button @click="closeEditLesson" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                        <h2 class="text-2xl font-bold mb-6">Edit Lesson</h2>
                        <div class="space-y-4">
                            <input v-model="editLessonData.title" type="text" placeholder="Judul" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                            <div class="grid grid-cols-3 gap-4">
                                <select v-model="editLessonData.materi_id" class="p-4 rounded-2xl form-input bg-slate-50">
                                    <option value="">Pilih Materi</option>
                                    <option v-for="m in materiList" :key="m.id" :value="m.id">{{ m.nama }}</option>
                                </select>
                                <input v-model="editLessonData.order_no" type="number" placeholder="Order No" class="p-4 rounded-2xl form-input bg-slate-50">
                                <input v-model="editLessonData.exp" type="number" placeholder="EXP Points" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                            </div>
                            <textarea v-model="editLessonData.content" placeholder="Konten..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-32"></textarea>
                            <textarea v-model="editLessonData.starter_code" placeholder="Starter code..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-32 font-mono"></textarea>
                            <div>
                                <label class="block text-xs font-black text-slate-600 uppercase mb-2">Validation Rules (JSON)</label>
                                <textarea v-model="editLessonData.validation_rules" placeholder='{"language": "html", "required_tags": ["h1"], "min_counts": {"h1": 1}}' class="p-4 w-full rounded-2xl form-input bg-slate-50 h-24 font-mono text-xs"></textarea>
                                <p class="text-[10px] text-slate-500 mt-2">Required: Include "language" field</p>
                            </div>
                            <div class="flex gap-4">
                                <button @click="updateLesson" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold">Update</button>
                                <button @click="closeEditLesson" class="flex-1 bg-slate-200 text-slate-700 px-6 py-3 rounded-2xl font-bold">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--QUIZ-->
            <div v-if="activeTab === 'quizzes'">
                <header class="flex justify-between items-center mb-10">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Quiz Management</h1>
                        <p class="text-slate-500 font-medium">Buat tantangan coding untuk player.</p>
                    </div>
                </header>

                <div class="inline-flex bg-slate-200/50 p-1.5 rounded-2xl mb-8">
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
                                <select v-model="newQuiz.materi" class="p-4 rounded-2xl form-input bg-slate-50">
                                    <option value="">Pilih Materi</option>
                                    <option v-for="m in materiList" :key="m" :value="m">{{ m }}</option>
                                </select>
                                <input v-model="newQuiz.score" type="number" class="p-4 rounded-2xl form-input bg-slate-50">
                            </div>
                            <textarea v-model="newQuiz.soal" placeholder="Pertanyaan..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-32"></textarea>
                        </div>

                        <div class="md:col-span-3 lg:col-span-1">
                            <div class="relative bg-[#1e2235] p-8 rounded-[2rem] shadow-2xl border border-white/5 group">

                                <div class="flex gap-2 mb-8 px-2">
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#ff5f56] shadow-lg shadow-red-500/20"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#ffbd2e] shadow-lg shadow-amber-500/20"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#27c93f] shadow-lg shadow-emerald-500/20"></div>
                                </div>

                                <div class="relative">
                                    <textarea
                                        v-model="newQuiz.snippet"
                                        placeholder="// Tulis atau tempel kode di sini..."
                                        class="relative w-full h-[320px] bg-transparent text-[#e1e4e8] font-mono text-sm leading-relaxed outline-none resize-none custom-scrollbar spell-none"
                                        style="color: #9cdceb;"
                                        spellcheck="false"></textarea>
                                </div>

                                <div class="absolute bottom-6 right-8">
                                    <i class="fas fa-terminal text-xs text-indigo-400"></i>
                                </div>
                            </div>
                        </div>

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

                <!--BULK IMPORT-->
                <div v-else class="space-y-6 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="relative bg-[#1e2235] p-8 rounded-[2rem] shadow-2xl border border-white/5 group">

                        <div class="flex items-center justify-between mb-8 px-2">
                            <div class="flex gap-2">
                                <div class="w-3.5 h-3.5 rounded-full bg-[#ff5f56] shadow-lg shadow-red-500/20"></div>
                                <div class="w-3.5 h-3.5 rounded-full bg-[#ffbd2e] shadow-lg shadow-amber-500/20"></div>
                                <div class="w-3.5 h-3.5 rounded-full bg-[#27c93f] shadow-lg shadow-emerald-500/20"></div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-blue-400 animate-pulse"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Batch_Quiz.json</span>
                                <a href="./bulk_quiz_format.json" download class="ml-3 px-3 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-bold hover:bg-emerald-200 transition-all border border-emerald-200 flex items-center gap-1">
                                    <i class="fas fa-download"></i> Download Format
                                </a>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -inset-4"></div>

                            <textarea
                                v-model="bulkText"
                                placeholder='[{"judul": "Syntax Master", "materi": "PHP", ...}]'
                                class="relative w-full h-80 bg-transparent text-blue-300 font-mono text-sm leading-relaxed outline-none resize-none custom-scrollbar"
                                spellcheck="false"></textarea>
                        </div>

                        <div class="absolute bottom-6 right-8 opacity-40">
                            <span class="text-[9px] font-black text-slate-500 border border-slate-700 px-2 py-1 rounded">JSON ARRAY</span>
                        </div>
                    </div>

                    <button @click="importBulk"
                        class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black py-5 rounded-[2rem] hover:shadow-2xl hover:shadow-emerald-500/30 transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                        <i class="fas fa-layer-group"></i>
                        LAUNCH BATCH IMPORT
                    </button>
                </div>
                <div class="bg-white rounded-[2rem] border overflow-hidden mb-6">
                    <div class="flex gap-4 p-5">
                        <select v-model="selectedMateri" class="p-4 rounded-2xl form-input bg-slate-50">
                            <option value="">Semua Materi</option>
                            <option v-for="m in materiList" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-5 text-xs font-black text-slate-400 uppercase">Details</th>
                                <th class="p-5 text-center text-xs font-black text-slate-400 uppercase">Materi</th>
                                <th class="p-5 text-center text-xs font-black text-slate-400 uppercase">XP</th>
                                <th class="p-5 text-right text-xs font-black text-slate-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="q in filteredQuizzes" :key="q.id" class="border-t">
                                <td class="p-5 font-bold">{{ q.judul }}</td>
                                <td class="p-5 text-center">{{ q.materi }}</td>
                                <td class="p-5 text-center">{{ q.score }}</td>
                                <td class="p-5 text-right flex gap-2 justify-end">
                                    <button @click="showEditQuiz(q)" class="text-blue-500 z-50"><i class="fas fa-edit"></i></button>
                                    <button @click="deleteQuiz(q.id)" class="text-red-500"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Modal Edit Quiz -->
                            <div v-if="editQuizModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                                <div class="bg-white rounded-3xl p-8 w-full max-w-2xl shadow-xl relative">
                                    <button @click="closeEditQuiz" class="absolute top-4 right-4 text-slate-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                                    <h2 class="text-xl font-bold mb-6">Edit Quiz</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="md:col-span-2 space-y-6">
                                            <input v-model="editQuizData.judul" type="text" placeholder="Judul Kuis" class="p-4 w-full rounded-2xl form-input bg-slate-50">
                                            <div class="grid grid-cols-2 gap-4">
                                                <select v-model="editQuizData.materi" class="p-4 rounded-2xl form-input bg-slate-50">
                                                    <option value="">Pilih Materi</option>
                                                    <option v-for="m in materiList" :key="m" :value="m">{{ m }}</option>
                                                </select>
                                                <input v-model="editQuizData.score" type="number" class="p-4 rounded-2xl form-input bg-slate-50">
                                            </div>
                                            <textarea v-model="editQuizData.soal" placeholder="Pertanyaan..." class="p-4 w-full rounded-2xl form-input bg-slate-50 h-32"></textarea>
                                        </div>
                                        <textarea v-model="editQuizData.snippet" placeholder="Code Snippet..." class="p-4 w-full rounded-2xl form-input bg-slate-900 text-emerald-400 font-mono text-sm"></textarea>
                                        <div class="md:col-span-3 grid grid-cols-2 lg:grid-cols-4 gap-4">
                                            <input v-for="opt in ['a','b','c','d']" :key="opt" v-model="editQuizData['opsi_'+opt]" type="text" :placeholder="'Opsi ' + opt.toUpperCase()" class="p-4 rounded-2xl form-input bg-slate-50">
                                        </div>
                                        <div class="md:col-span-3 flex gap-4">
                                            <select v-model="editQuizData.jawaban_benar" class="p-4 rounded-2xl form-input bg-emerald-50 flex-1">
                                                <option value="">Pilih Kunci Jawaban</option>
                                                <option v-for="ans in ['A','B','C','D']" :value="ans">{{ans}}</option>
                                            </select>
                                            <button @click="updateQuiz" class="bg-blue-600 text-white px-10 rounded-2xl font-bold">Update</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </tbody>
                    </table>
                </div>
            </div>

            <!--DATA-->
            <div v-if="activeTab === 'stats'" class="animate-in fade-in duration-500">
                <header class="mb-12">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-2">Performance <span class="text-indigo-600">Overview</span></h1>
                    <p class="text-slate-500 font-medium">Monitoring real-time aktivitas dan skor pemain.</p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="glass-panel p-8 stat-card-premium border-l-4 border-l-indigo-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Total Players</p>
                                <h3 class="text-4xl font-black text-slate-900 tracking-tighter">{{ playerStats.length }}</h3>
                            </div>
                            <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-500"><i class="fas fa-users-viewfinder"></i></div>
                        </div>
                    </div>

                    <div class="glass-panel p-8 stat-card-premium border-l-4 border-l-emerald-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Average XP</p>
                                <h3 class="text-4xl font-black text-emerald-600 tracking-tighter">{{ averageScore }}</h3>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-500"><i class="fas fa-bolt"></i></div>
                        </div>
                    </div>

                    <div class="glass-panel p-8 stat-card-premium border-l-4 border-l-amber-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Total Score</p>
                                <h3 class="text-4xl font-black text-slate-900 tracking-tighter">
                                    {{ playerStats.length > 0 ? playerStats.reduce((acc, p) => acc + (Number(p.total_score) || 0), 0).toLocaleString() : 0 }}
                                </h3>
                            </div>
                            <div class="p-3 bg-amber-50 rounded-2xl text-amber-500"><i class="fas fa-crown"></i></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 mb-8 justify-between items-center">
                    <div class="flex gap-4">
                        <a href="../server/export_excel.php" class="bg-[#1D6F42] text-white px-6 py-3 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="../server/export_pdf.php" class="bg-[#E92224] text-white px-6 py-3 rounded-2xl font-bold hover:scale-105 transition-all shadow-lg flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="glass-panel overflow-hidden border border-slate-200">
                    <table class="w-full">
                        <thead class="bg-slate-50/80 border-b border-slate-200">
                            <tr>
                                <th class="p-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Rank & Player</th>
                                <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Missions</th>
                                <th class="p-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Score</th>
                                <th class="p-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(p, index) in playerStats" :key="p.id" class="hover:bg-indigo-50/30 transition-all group">
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <span class="text-xs font-black text-slate-300">#{{ index + 1 }}</span>
                                        <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center font-black text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                                            {{ p.username.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ p.username }}</p>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-black">{{ p.terakhir_main || 'No Activity' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="px-4 py-2 bg-slate-100 rounded-xl font-bold text-slate-600 text-xs border border-slate-200">
                                        {{ p.total_kuis }} Quiz
                                    </span>
                                </td>
                                <td class="p-6 text-center">
                                    <div class="inline-flex items-center gap-2 text-indigo-600 font-black">
                                        <span>{{ p.total_score.toLocaleString() }}</span>
                                        <span class="text-[10px] text-indigo-300">XP</span>
                                    </div>
                                </td>
                                <td class="p-6 text-right">
                                    <button @click="deleteAccount(p.id)" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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
        } = Vue;

        createApp({
            setup() {
                const activeTab = ref('quizzes');
                const mode = ref('single');
                const quizzes = ref([]);
                const playerStats = ref([]);
                const bulkText = ref('');
                const apiQuizBase = '../server/admin';

                // Materi
                const materiList = ref([]);
                const newMateri = ref('');
                const selectedMateri = ref('');

                // Quiz
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

                // Edit Quiz
                const editQuizModal = ref(false);
                const editQuizData = ref({});

                const fetchMateri = async () => {
                    try {
                        const res = await fetch(`../server/lesson/api_materi.php?t=${Date.now()}`);
                        const data = await res.json();
                        if (data.success && data.data) {
                            materiList.value = data.data;
                        } else {
                            materiList.value = [];
                        }
                    } catch (e) {
                        console.error('Error fetching materi:', e);
                        materiList.value = [];
                    }
                };

                const addMateri = async () => {
                    if (newMateri.value) {
                        try {
                            const res = await fetch('../server/lesson/api_materi.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ nama: newMateri.value, icon: '' })
                            });
                            const data = await res.json();
                            if (data.success) {
                                newMateri.value = '';
                                fetchMateri();
                            } else {
                                alert('Error adding materi: ' + data.message);
                            }
                        } catch (e) {
                            alert('Error: ' + e.message);
                        }
                    }
                };

                const deleteMateri = async (id) => {
                    if (confirm('Hapus materi?')) {
                        try {
                            const res = await fetch(`../server/lesson/api_materi.php?id=${id}`, {
                                method: 'DELETE'
                            });
                            const data = await res.json();
                            if (data.success) {
                                fetchMateri();
                                // Also refresh quizzes and lessons
                                fetchQuizzes();
                                fetchLessons();
                            }
                        } catch (e) {
                            alert('Error: ' + e.message);
                        }
                    }
                };

                const fetchQuizzes = async () => {
                    const res = await fetch(`${apiQuizBase}/read.php?t=${Date.now()}`);
                    quizzes.value = await res.json();
                    fetchMateri();
                };

                const fetchStats = async () => {
                    try {
                        const res = await fetch('../server/api_stats.php?t=' + Date.now());
                        const data = await res.json();
                        playerStats.value = data;
                    } catch (e) {
                        console.error("Gagal mengambil data statistik");
                    }
                };

                const averageScore = computed(() => {
                    if (!playerStats.value || playerStats.value.length === 0) return 0;
                    return playerStats.value.reduce((acc, curr) => acc + (Number(curr.total_kuis) || 0), 0);
                });

                const addQuiz = async () => {
                    await fetch(`${apiQuizBase}/create.php`, {
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

                const showEditQuiz = (quiz) => {
                    editQuizData.value = {
                        ...quiz
                    };
                    editQuizModal.value = true;
                };

                const closeEditQuiz = () => {
                    editQuizModal.value = false;
                    editQuizData.value = {};
                };

                const updateQuiz = async () => {
                    await fetch(`${apiQuizBase}/update.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(editQuizData.value)
                    });
                    closeEditQuiz();
                    fetchQuizzes();
                };

                const deleteQuiz = async (id) => {
                    if (confirm("Hapus?")) {
                        await fetch(`${apiQuizBase}/delete.php?id=${id}`, {
                            method: 'DELETE'
                        });
                        fetchQuizzes();
                    }
                };

                const deleteAccount = async (id) => {
                    if (confirm("Hapus akun player ini?")) {
                        try {
                            const res = await fetch(`../server/admin/delete_user.php?id=${id}`, {
                                method: 'DELETE'
                            });
                            const data = await res.json();
                            if (data.success) {
                                alert('Akun player berhasil dihapus!');
                                fetchStats();
                            } else {
                                alert('Gagal menghapus akun: ' + (data.error || 'Unknown error'));
                                fetchStats();
                            }
                        } catch (e) {
                            alert('Terjadi kesalahan koneksi saat menghapus akun!');
                        }
                    }
                };

                const importBulk = async () => {
                    try {
                        const data = JSON.parse(bulkText.value);
                        for (const item of data) {
                            await fetch(`${apiQuizBase}/create.php`, {
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

                const filteredQuizzes = computed(() => {
                    if (!selectedMateri.value) return quizzes.value;
                    return quizzes.value.filter(q => q.materi === selectedMateri.value);
                });

                // Lesson Management
                const lessons = ref([]);
                const lessonMode = ref('single');
                const bulkLessonText = ref('');
                const newLesson = ref({
                    title: '',
                    materi_id: '',
                    content: '',
                    starter_code: '',
                    validation_rules: '',
                    exp: 10,
                    order_no: 1
                });
                const selectedMateriLesson = ref('');
                const editLessonModal = ref(false);
                const editLessonData = ref({});

                const fetchLessons = async () => {
                    try {
                        const res = await fetch(`../server/lesson/api_lesson.php?t=${Date.now()}`);
                        const data = await res.json();
                        if (data.success) {
                            lessons.value = data.lessons || [];
                        }
                    } catch (e) {
                        console.error('Error fetching lessons:', e);
                        lessons.value = [];
                    }
                };

                const addLesson = async () => {
                    try {
                        const lessonData = { ...newLesson.value };
                        // Parse validation_rules if it's a string
                        if (lessonData.validation_rules && typeof lessonData.validation_rules === 'string') {
                            try {
                                lessonData.validation_rules = JSON.parse(lessonData.validation_rules);
                            } catch (e) {
                                alert('Invalid JSON in Validation Rules');
                                return;
                            }
                        }
                        
                        await fetch(`../server/lesson/api_lesson.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(lessonData)
                        });
                        newLesson.value = {
                            title: '',
                            materi_id: '',
                            content: '',
                            starter_code: '',
                            validation_rules: '',
                            exp: 10,
                            order_no: 1
                        };
                        fetchLessons();
                        alert('Lesson added successfully!');
                    } catch (e) {
                        alert('Error adding lesson: ' + e.message);
                    }
                };

                const deleteLesson = async (id) => {
                    if (confirm('Delete this lesson?')) {
                        try {
                            await fetch(`../server/lesson/api_lesson.php?id=${id}`, {
                                method: 'DELETE'
                            });
                            fetchLessons();
                        } catch (e) {
                            alert('Error deleting lesson: ' + e.message);
                        }
                    }
                };

                const showEditLesson = (lesson) => {
                    editLessonData.value = { ...lesson };
                    editLessonModal.value = true;
                };

                const closeEditLesson = () => {
                    editLessonModal.value = false;
                    editLessonData.value = {};
                };

                const updateLesson = async () => {
                    try {
                        const lessonData = { ...editLessonData.value };
                        // Parse validation_rules if it's a string
                        if (lessonData.validation_rules && typeof lessonData.validation_rules === 'string') {
                            try {
                                lessonData.validation_rules = JSON.parse(lessonData.validation_rules);
                            } catch (e) {
                                alert('Invalid JSON in Validation Rules');
                                return;
                            }
                        }
                        
                        await fetch(`../server/lesson/api_lesson.php`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(lessonData)
                        });
                        closeEditLesson();
                        fetchLessons();
                        alert('Lesson updated successfully!');
                    } catch (e) {
                        alert('Error updating lesson: ' + e.message);
                    }
                };

                const importBulkLessons = async () => {
                    try {
                        const data = JSON.parse(bulkLessonText.value);
                        for (const item of data) {
                            await fetch(`../server/lesson/api_lesson.php`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(item)
                            });
                        }
                        bulkLessonText.value = '';
                        lessonMode.value = 'single';
                        fetchLessons();
                        alert('All lessons imported successfully!');
                    } catch (e) {
                        alert('JSON Invalid atau error: ' + e.message);
                    }
                };

                const filteredLessons = computed(() => {
                    if (!selectedMateriLesson.value) return lessons.value;
                    return lessons.value.filter(l => String(l.materi_id) === String(selectedMateriLesson.value));
                });

                onMounted(() => {
                    fetchQuizzes();
                    fetchStats();
                    fetchLessons();
                });

                // Mapping materi ke ikon FontAwesome
                function getMateriIcon(materi) {
                    const map = {
                        'javascript': 'fab fa-js text-yellow-400',
                        'js': 'fab fa-js text-yellow-400',
                        'python': 'fab fa-python text-blue-500',
                        'php': 'fab fa-php text-indigo-400',
                        'java': 'fab fa-java text-red-500',
                        'html': 'fab fa-html5 text-orange-500',
                        'css': 'fab fa-css3-alt text-blue-400',
                        'react': 'fab fa-react text-cyan-400',
                        'vue': 'fab fa-vuejs text-emerald-500',
                        'node': 'fab fa-node-js text-green-500',
                        'laravel': 'fab fa-laravel text-red-600',
                        'database': 'fas fa-database text-slate-400',
                        'sql': 'fas fa-database text-blue-300',
                        'mysql': 'fas fa-database text-blue-500',
                        'c++': 'fab fa-cuttlefish text-blue-600',
                        'c#': 'fas fa-hashtag text-purple-600',
                        'ruby': 'fas fa-gem text-red-400',
                        'swift': 'fab fa-swift text-orange-600',
                        'docker': 'fab fa-docker text-blue-400',
                        'github': 'fab fa-github text-slate-200',
                        'git': 'fab fa-git-alt text-orange-500',
                        'angular': 'fab fa-angular text-red-600',
                        'figma': 'fab fa-figma text-purple-400',
                        'sass': 'fab fa-sass text-pink-400',
                    };
                    const lower = (materi || '').toLowerCase();
                    for (const key in map) {
                        if (lower.includes(key)) return map[key];
                    }
                    return 'fas fa-terminal text-blue-500';
                }

                function getMateriNameById(id) {
                    const materi = materiList.value.find(m => m.id == id);
                    return materi ? materi.nama : 'Unknown';
                }

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
                    averageScore,
                    // Materi
                    materiList,
                    newMateri,
                    addMateri,
                    deleteMateri,
                    selectedMateri,
                    filteredQuizzes,
                    getMateriIcon,
                    getMateriNameById,
                    // Edit Quiz
                    editQuizModal,
                    editQuizData,
                    showEditQuiz,
                    closeEditQuiz,
                    updateQuiz,
                    deleteAccount,
                    // Lessons
                    lessons,
                    lessonMode,
                    bulkLessonText,
                    newLesson,
                    addLesson,
                    deleteLesson,
                    importBulkLessons,
                    selectedMateriLesson,
                    filteredLessons,
                    editLessonModal,
                    editLessonData,
                    showEditLesson,
                    closeEditLesson,
                    updateLesson
                };
            }
        }).mount('#app');
    </script>
</body>

</html>