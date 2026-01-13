<?php
session_start();
require_once '../../../server/auth_check.php';
checkLogin();

$materi_id = isset($_GET['materi']) ? intval($_GET['materi']) : 0;
$materi_nama = 'CodeLearn';
$materi_icon = '';

if ($materi_id > 0) {
    require_once '../../../server/config/koneksi.php';
    $q = mysqli_query($conn, "SELECT nama, icon FROM materi_lesson WHERE id = " . intval($materi_id));
    if ($row = mysqli_fetch_assoc($q)) {
        $materi_nama = $row['nama'];
        $materi_icon = $row['icon'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $materi_nama; ?> - Interactive Lessons</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #0f172a;
        }

        .code-editor {
            font-family: 'Fira Code', monospace;
            background-color: #1e1e1e;
            color: #d4d4d4;
            line-height: 1.6;
            resize: none;
            font-size: 14px;
        }

        .line-numbers {
            background-color: #181818;
            color: #565656;
            font-family: 'Fira Code', monospace;
            padding: 16px 12px;
            text-align: right;
            user-select: none;
            font-size: 14px;
            line-height: 1.6;
            border-right: 1px solid #2d2d2d;
        }

        .output-frame {
            background: white;
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(8px);
        }

        .panel-container {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>

<body class="text-slate-200">
    <div class="h-screen flex flex-col">
        <header class="bg-slate-900 border-b border-slate-800 p-3 px-6 shadow-2xl z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-900/20">
                        <i class="fas fa-code <?php echo $materi_icon ?: 'fa-code'; ?> text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight"><?php echo htmlspecialchars($materi_nama); ?></h1>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Player Workspace</p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex flex-col items-end gap-1">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <span>COURSE PROGRESS</span>
                            <span id="progress-text" class="text-emerald-400">0%</span>
                        </div>
                        <div class="w-48 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div id="progress-bar" class="h-full bg-emerald-500 transition-all duration-700 shadow-[0_0_8px_rgba(16,185,129,0.4)]" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-slate-800"></div>
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden md:block">
                            <p class="text-xs text-slate-500 font-medium">Signed in as</p>
                            <p class="text-sm font-bold text-slate-200"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                        <a href="../dashboard_player.php" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-xs font-bold transition-all border border-slate-700">
                            DASHBOARD
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden p-3 gap-3 bg-slate-950">

            <section class="w-[28%] flex flex-col gap-3">
                <div class="flex-1 panel-container bg-slate-900/50 flex flex-col">
                    <div class="p-4 border-b border-slate-800 flex items-center gap-2">
                        <i class="fas fa-book-open text-blue-400 text-xs"></i>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Instructions</span>
                    </div>
                    <div id="lesson-content" class="flex-1 overflow-y-auto p-6 prose prose-invert prose-sm max-w-none">
                        <div class="animate-pulse space-y-4">
                            <div class="h-6 bg-slate-800 rounded w-3/4"></div>
                            <div class="h-4 bg-slate-800 rounded w-full"></div>
                            <div class="h-4 bg-slate-800 rounded w-5/6"></div>
                        </div>
                    </div>
                </div>

                <div class="h-[35%] panel-container bg-slate-900/50 flex flex-col">
                    <div class="p-3 border-b border-slate-800 flex justify-between items-center">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Curriculum</span>
                    </div>
                    <div id="lesson-list" class="flex-1 overflow-y-auto p-2 space-y-1"></div>
                </div>
            </section>

            <section class="w-[37%] panel-container bg-[#1e1e1e] flex flex-col shadow-2xl border-slate-800">
                <div class="bg-slate-800/50 p-2 px-4 border-b border-white/5 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/40"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/40"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/20 border border-green-500/40"></div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-500">index.html</span>
                    </div>
                    <div class="flex gap-2">
                        <button id="run-btn" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-1.5 rounded text-[11px] font-bold transition-all flex items-center gap-2">
                            <i class="fas fa-play text-[9px]"></i> RUN
                        </button>
                        <button id="submit-btn" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded text-[11px] font-bold transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20">
                            <i class="fas fa-check text-[9px]"></i> SUBMIT
                        </button>
                    </div>
                </div>
                <div class="flex-1 flex overflow-hidden">
                    <div id="line-numbers" class="line-numbers">1</div>
                    <textarea id="code-editor" class="code-editor flex-1 p-4 outline-none" spellcheck="false" placeholder=""></textarea>
                </div>
            </section>

            <section class="w-[35%] panel-container bg-white flex flex-col shadow-2xl border-slate-800">
                <div class="bg-slate-100 p-2 px-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[11px] font-bold uppercase text-slate-500 tracking-tighter">Live Preview</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono italic">localhost:8080</span>
                </div>
                <div class="flex-1 bg-white">
                    <iframe id="output-frame" class="output-frame" sandbox="allow-scripts allow-same-origin"></iframe>
                </div>
            </section>
        </main>
    </div>

    <div id="success-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/20">
                <span class="text-4xl">🏆</span>
            </div>
            <h2 class="text-2xl font-bold mb-2 text-white">Challenge Solved!</h2>
            <p class="text-slate-400 mb-8 text-sm">You have successfully completed this lesson. Keep the momentum going!</p>
            <button id="next-lesson-btn" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-emerald-900/40">
                NEXT LESSON
            </button>
        </div>
    </div>

    <div id="error-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                <i class="fas fa-times text-red-500 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold mb-2 text-white">Not Quite Right</h2>
            <p id="error-message" class="text-slate-400 mb-6 text-sm font-mono bg-slate-950 p-3 rounded-lg border border-slate-800 text-left"></p>
            <button id="close-error-btn" class="w-full bg-slate-800 hover:bg-slate-700 text-white py-3 rounded-xl font-bold transition-all">
                TRY AGAIN
            </button>
        </div>
    </div>

    <script>
        let currentLessonId = null;
        let allLessons = [];
        let userProgress = [];
        const materiId = <?php echo $materi_id; ?>;

        document.addEventListener('DOMContentLoaded', () => {
            loadLessons();
            loadProgress();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('run-btn').addEventListener('click', runCode);
            document.getElementById('submit-btn').addEventListener('click', submitAnswer);
            document.getElementById('next-lesson-btn').addEventListener('click', goToNextLesson);
            document.getElementById('close-error-btn').addEventListener('click', () => {
                document.getElementById('error-modal').classList.add('hidden');
            });
            const editor = document.getElementById('code-editor');
            editor.addEventListener('input', updateLineNumbers);
            editor.addEventListener('scroll', syncScroll);

            // Tab support in textarea
            editor.addEventListener('keydown', function(e) {
                if (e.key == 'Tab') {
                    e.preventDefault();
                    let start = this.selectionStart;
                    let end = this.selectionEnd;
                    this.value = this.value.substring(0, start) + "    " + this.value.substring(end);
                    this.selectionStart = this.selectionEnd = start + 4;
                }
            });
        }

        async function loadLessons() {
            try {
                const response = await fetch('../../../server/lesson/api_lesson.php');
                const data = await response.json();
                if (data.success) {
                    allLessons = materiId > 0 ? data.lessons.filter(l => parseInt(l.materi_id) === materiId) : data.lessons;
                    renderLessonList();
                    if (allLessons.length > 0) loadLesson(allLessons[0].id);
                }
            } catch (error) {
                console.error('Error loading lessons:', error);
            }
        }

        async function loadProgress() {
            try {
                const response = await fetch('../../../server/lesson/api_progress.php?action=get');
                const data = await response.json();
                if (data.success) {
                    userProgress = data.progress;
                    updateProgressBar();
                }
            } catch (error) {
                console.error('Error loading progress:', error);
            }
        }

        function renderLessonList() {
            const listContainer = document.getElementById('lesson-list');
            if (allLessons.length === 0) {
                listContainer.innerHTML = '<div class="text-slate-500 text-xs p-4">No lessons found.</div>';
                return;
            }
            listContainer.innerHTML = allLessons.map(lesson => {
                const isCompleted = userProgress.includes(lesson.id);
                const isActive = currentLessonId === lesson.id;
                return `
                    <button onclick="loadLesson(${lesson.id})" class="w-full text-left p-3 rounded-lg flex items-center justify-between transition-all group ${isActive ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 text-slate-400'}">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-mono ${isActive ? 'text-blue-200' : 'text-slate-600'}">${lesson.order_no.toString().padStart(2, '0')}</span>
                            <span class="text-xs font-semibold">${lesson.title}</span>
                        </div>
                        ${isCompleted ? '<i class="fas fa-check-circle text-emerald-400 text-xs"></i>' : '<i class="far fa-circle text-[10px] opacity-20 group-hover:opacity-100"></i>'}
                    </button>
                `;
            }).join('');
        }

        async function loadLesson(lessonId) {
            try {
                const response = await fetch(`../../../server/lesson/api_lesson.php?id=${lessonId}`);
                const data = await response.json();
                if (data.success) {
                    currentLessonId = lessonId;
                    const lesson = data.lesson;
                    document.getElementById('lesson-content').innerHTML = `
                        <h2 class="text-xl font-bold text-white mb-2">${lesson.title}</h2>
                        <div class="text-slate-300 leading-relaxed text-sm">${lesson.content}</div>
                    `;
                    document.getElementById('code-editor').value = lesson.starter_code || '';
                    updateLineNumbers();
                    renderLessonList();
                    runCode(); // Auto run on load
                }
            } catch (error) {
                console.error('Error loading lesson:', error);
            }
        }

        async function runCode() {
            const code = document.getElementById('code-editor').value;
            try {
                const response = await fetch('../../../server/lesson/api_run.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `code=${encodeURIComponent(code)}`
                });
                const data = await response.json();
                if (data.success) {
                    const iframe = document.getElementById('output-frame');
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    iframeDoc.open();
                    iframeDoc.write(data.html);
                    iframeDoc.close();
                }
            } catch (error) {
                console.error('Error running code:', error);
            }
        }

        async function submitAnswer() {
            const code = document.getElementById('code-editor').value;
            if (!currentLessonId) return;

            try {
                const response = await fetch('../../../server/lesson/api_check.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `lesson_id=${currentLessonId}&code=${encodeURIComponent(code)}`
                });
                const data = await response.json();

                if (data.success && data.correct) {
                    if (!userProgress.includes(currentLessonId)) userProgress.push(currentLessonId);
                    updateProgressBar();
                    renderLessonList();
                    document.getElementById('success-modal').classList.remove('hidden');
                } else {
                    document.getElementById('error-message').textContent = data.message || 'Output structure is incorrect. Keep trying!';
                    document.getElementById('error-modal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error checking answer:', error);
            }
        }

        function goToNextLesson() {
            document.getElementById('success-modal').classList.add('hidden');
            const currentIndex = allLessons.findIndex(l => l.id === currentLessonId);
            if (currentIndex < allLessons.length - 1) {
                loadLesson(allLessons[currentIndex + 1].id);
            } else {
                window.location.href = '../dashboard_player.php';
            }
        }

        function updateProgressBar() {
            const percentage = allLessons.length > 0 ? Math.round((userProgress.length / allLessons.length) * 100) : 0;
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = percentage + '%';
        }

        function updateLineNumbers() {
            const editor = document.getElementById('code-editor');
            const lineNumbersDiv = document.getElementById('line-numbers');
            const lines = editor.value.split('\n').length;
            lineNumbersDiv.textContent = Array.from({
                length: lines
            }, (_, i) => i + 1).join('\n');
        }

        function syncScroll() {
            const editor = document.getElementById('code-editor');
            const lineNumbersDiv = document.getElementById('line-numbers');
            lineNumbersDiv.scrollTop = editor.scrollTop;
        }
    </script>
</body>

</html>