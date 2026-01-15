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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material-darker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #0f172a;
        }

        .output-frame {
            background: white;
            width: 100%;
            height: 100%;
            border: none;
        }

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

        .panel-container {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        #code-editor-container {
            font-family: 'Fira Code', monospace;
            height: 100%;
        }

        .CodeMirror {
            font-family: 'Fira Code', monospace !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
            background-color: #1e1e1e !important;
            height: 100% !important;
        }

        .CodeMirror-gutters {
            background-color: #181818 !important;
            border-right: 1px solid #2d2d2d !important;
        }
    </style>
</head>

<body class="text-slate-200">

    <div class="h-screen flex flex-col">
        <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 shadow-xl z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg">
                        <i class="fas <?php echo $materi_icon ?: 'fa-code'; ?> text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-white"><?php echo htmlspecialchars($materi_nama); ?></h1>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Interactive Coding Workspace</p>
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
                        <a href="../dashboard_player.php" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-xs font-bold transition-all border border-slate-700">DASHBOARD</a>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 flex overflow-hidden p-3 gap-3 bg-slate-950">
            <section class="w-[32rem] flex-shrink-0 flex flex-col gap-3">
                <div class="flex-1 panel-container bg-slate-900/50 flex flex-col">
                    <div class="p-4 border-b border-slate-800 flex items-center gap-2">
                        <i class="fas fa-book-open text-blue-400 text-xs"></i>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Instructions</span>
                    </div>
                    <div id="lesson-content" class="flex-1 overflow-y-auto p-6 prose prose-invert prose-base max-w-none">
                        <div class="animate-pulse space-y-4">
                            <div class="h-6 bg-slate-800 rounded w-3/4"></div>
                            <div class="h-4 bg-slate-800 rounded w-full"></div>
                            <div class="h-4 bg-slate-800 rounded w-5/6"></div>
                        </div>
                    </div>
                </div>
                <div class="h-120 panel-container bg-slate-900/50 flex flex-col">
                    <div class="p-3 border-b border-slate-800 flex items-center gap-2">
                        <i class="fas fa-list text-emerald-400 text-xs"></i>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Curriculum</span>
                    </div>
                    <div id="lesson-list" class="flex-1 overflow-y-auto p-3 space-y-1.5"></div>
                </div>
            </section>
            <section class="flex-1 panel-container bg-[#1e1e1e] flex flex-col shadow-2xl border-slate-800">
                <div class="bg-slate-800/50 p-2 px-4 border-b border-white/5 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-1.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-[#ff5f56] shadow-lg shadow-red-500/20"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-[#ffbd2e] shadow-lg shadow-amber-500/20"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-[#27c93f] shadow-lg shadow-emerald-500/20"></div>
                        </div>
                        <div id="file-tabs" class="flex gap-1 ml-2"></div>
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
                    <div id="code-editor-container" class="flex-1"></div>
                </div>
            </section>
            <section class="flex-1 panel-container bg-white flex flex-col shadow-2xl border-slate-800">
                <div class="bg-slate-100 p-2 px-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[11px] font-bold uppercase text-slate-500 tracking-tighter">Live Preview</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono italic">Aldi alfariz 24552011212 - TIF RP23 CNS A - UASWEB1</span>
                </div>
                <div class="flex-1 bg-white">
                    <iframe id="output-frame" class="output-frame" sandbox="allow-scripts allow-same-origin"></iframe>
                </div>
            </section>
        </main>
    </div>
    <div id="success-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/20"><span class="text-4xl">🏆</span></div>
            <h2 class="text-2xl font-bold mb-2 text-white">Challenge Solved!</h2>
            <div class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 px-4 py-2 rounded-lg mb-4">
                <i class="fas fa-star text-yellow-400"></i>
                <span class="text-lg font-bold text-yellow-300">+<span id="earned-xp">10</span> XP</span>
            </div>
            <p class="text-slate-400 mb-8 text-sm">You have successfully completed this lesson. Keep the momentum going!</p>
            <button id="next-lesson-btn" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-emerald-900/40">NEXT LESSON</button>
        </div>
    </div>
    <div id="error-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                <i class="fas fa-times text-red-500 text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold mb-2 text-white">Not Quite Right</h2>
            <p id="error-message" class="text-slate-400 mb-6 text-sm font-mono bg-slate-950 p-3 rounded-lg border border-slate-800 text-left"></p>
            <button id="close-error-btn" class="w-full bg-slate-800 hover:bg-slate-700 text-white py-3 rounded-xl font-bold transition-all">TRY AGAIN</button>
        </div>
    </div>

    <script>
        let currentLessonId = null,
            allLessons = [],
            userProgress = [],
            currentFiles = {},
            currentActiveFile = null,
            editor = null;
        const materiId = <?php echo $materi_id; ?>;
        const languageFileMap = {
            html: ['index.html'],
            css: ['index.html', 'style.css']
        };

        const getLanguageMode = (filename) => {
            const ext = filename.split('.').pop().toLowerCase();
            const modeMap = {
                'html': 'htmlmixed',
                'css': 'css'
            };
            return modeMap[ext] || 'null';
        };

        const getLanguageFromRules = (rules) => rules ? (rules.language || 'html').toLowerCase() : 'html';
        const buildStarterFiles = (lesson) => {
            const rules = lesson.validation_rules ? JSON.parse(lesson.validation_rules) : null;
            const lang = getLanguageFromRules(rules);
            const starter = lesson.starter_code || '';

            switch (lang) {
                case 'html':
                    return {
                        files: {
                                'index.html': starter || '<!DOCTYPE html>\n<html>\n<head>\n  <meta charset="UTF-8" />\n  <title>My Page</title>\n</head>\n<body>\n  <h1>Hello World</h1>\n</body>\n</html>'
                            },
                            active: 'index.html'
                    };
                case 'css':
                    return {
                        files: {
                                'index.html': `<!DOCTYPE html>\n<html>\n<head>\n  <meta charset="UTF-8" />\n  <title>CSS Lesson</title>\n  <link rel="stylesheet" href="style.css" />\n</head>\n<body>\n  <p class="text">Text example</p>\n  <div class="box"></div>\n</body>\n</html>`,
                                'style.css': starter || '/* Write your CSS here */\n.text {\n  color: black;\n}'
                            },
                            active: 'style.css'
                    };
                default:
                    return {
                        files: {
                                'index.html': starter || '<!DOCTYPE html>\n<html>\n<head>\n  <meta charset="UTF-8" />\n  <title>Lesson</title>\n</head>\n<body>\n  <h1>Hello World</h1>\n</body>\n</html>'
                            },
                            active: 'index.html'
                    };
            }
        };

        const renderFileTabs = () => {
            const tabs = document.getElementById('file-tabs');
            tabs.innerHTML = '';
            Object.keys(currentFiles).forEach(fn => {
                const btn = document.createElement('button');
                btn.className = `text-[11px] font-mono px-3 py-1.5 border-b-2 transition-all ${fn === currentActiveFile ? 'border-blue-500 text-blue-400 bg-slate-700/50' : 'border-transparent text-slate-500 hover:text-slate-300'}`;
                btn.textContent = fn;
                btn.onclick = () => switchFile(fn);
                tabs.appendChild(btn);
            });
        };

        const switchFile = (fn) => {
            currentFiles[currentActiveFile] = editor.getValue();
            currentActiveFile = fn;
            editor.setValue(currentFiles[fn] || '');
            editor.setOption('mode', getLanguageMode(fn));
            renderFileTabs();
            runCode();
        };

        document.addEventListener('DOMContentLoaded', () => {
            loadLessons();
            loadProgress();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('run-btn').addEventListener('click', runCode);
            document.getElementById('submit-btn').addEventListener('click', submitAnswer);
            document.getElementById('next-lesson-btn').addEventListener('click', goToNextLesson);
            document.getElementById('close-error-btn').addEventListener('click', () => document.getElementById('error-modal').classList.add('hidden'));
        }

        async function loadLessons() {
            try {
                const data = await (await fetch('../../../server/lesson/api_lesson.php')).json();
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
                const data = await (await fetch('../../../server/lesson/api_progress.php?action=get')).json();
                if (data.success) {
                    userProgress = data.progress;
                    updateProgressBar();
                }
            } catch (error) {
                console.error('Error loading progress:', error);
            }
        }

        function renderLessonList() {
            const list = document.getElementById('lesson-list');
            if (allLessons.length === 0) {
                list.innerHTML = '<div class="text-slate-500 text-xs p-4">No lessons found.</div>';
                return;
            }
            list.innerHTML = allLessons.map(l => {
                const done = userProgress.includes(l.id),
                    active = currentLessonId === l.id,
                    exp = l.exp || 10;
                let bg = 'hover:bg-slate-800 text-slate-400',
                    badge = '';
                if (done && !active) {
                    bg = 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20';
                    badge = '<span class="text-[8px] font-bold bg-emerald-500/40 text-emerald-200 px-2 py-1 rounded">✓ DONE</span>';
                } else if (active) bg = 'bg-blue-600 text-white shadow-lg';
                return `<button onclick="loadLesson(${l.id})" class="w-full text-left p-3 rounded-lg flex items-center justify-between transition-all group ${bg}">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <span class="text-[10px] font-mono ${active ? 'text-blue-200' : done ? 'text-emerald-500' : 'text-slate-600'} flex-shrink-0">${l.order_no.toString().padStart(2, '0')}</span>
                        <span class="text-xs font-semibold truncate">${l.title}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">${badge}<span class="text-[10px] font-bold ${active ? 'text-yellow-300' : 'text-yellow-500/60'} flex items-center gap-1 whitespace-nowrap">
                        <i class="fas fa-star"></i> ${exp}</span>${done ? '<i class="fas fa-check-circle text-emerald-400 text-xs flex-shrink-0"></i>' : '<i class="far fa-circle text-[10px] opacity-20 group-hover:opacity-100 flex-shrink-0"></i>'}</div></button>`;
            }).join('');
        }

        async function loadLesson(id) {
            try {
                const data = await (await fetch(`../../../server/lesson/api_lesson.php?id=${id}`)).json();
                if (data.success) {
                    currentLessonId = id;
                    const l = data.lesson,
                        exp = l.exp || 10,
                        done = userProgress.includes(id);
                    let html = `<div class="flex items-start justify-between mb-4"><h2 class="text-xl font-bold text-white">${l.title}</h2><div class="flex items-center gap-2">`;
                    if (done) html += '<div class="flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-500/30 px-3 py-1.5 rounded-lg"><i class="fas fa-check-circle text-emerald-400 text-sm"></i><span class="text-sm font-bold text-emerald-300">COMPLETED</span></div>';
                    html += '<div class="flex items-center gap-1.5 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 px-3 py-1.5 rounded-lg"><i class="fas fa-star text-yellow-400 text-sm"></i><span class="text-sm font-bold text-yellow-300">' + exp + ' XP</span></div></div></div><div class="text-slate-300 leading-relaxed text-sm">' + l.content + '</div>';
                    document.getElementById('lesson-content').innerHTML = html;

                    const starter = buildStarterFiles(l);
                    currentFiles = starter.files;
                    currentActiveFile = starter.active;

                    if (!editor) {
                        editor = CodeMirror(document.getElementById('code-editor-container'), {
                            value: currentFiles[currentActiveFile] || '',
                            mode: getLanguageMode(currentActiveFile),
                            theme: 'material-darker',
                            lineNumbers: true,
                            indentUnit: 4,
                            indentWithTabs: false,
                            lineWrapping: true,
                            styleActiveLine: true,
                            matchBrackets: true,
                            autoCloseBrackets: true
                        });
                        // Auto-run on change with debounce
                        let runTimeout;
                        editor.on('change', function() {
                            clearTimeout(runTimeout);
                            runTimeout = setTimeout(runCode, 1000);
                        });
                    } else {
                        editor.setValue(currentFiles[currentActiveFile] || '');
                        editor.setOption('mode', getLanguageMode(currentActiveFile));
                    }

                    renderFileTabs();

                    const btn = document.getElementById('submit-btn');
                    if (done) {
                        editor.setOption('readOnly', true);
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        editor.setOption('readOnly', false);
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                    renderLessonList();
                    runCode();
                }
            } catch (error) {
                console.error('Error loading lesson:', error);
            }
        }

        async function runCode() {
            currentFiles[currentActiveFile] = editor.getValue();
            try {
                const data = await (await fetch('../../../server/lesson/api_run.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        files: currentFiles
                    })
                })).json();
                if (data.success) {
                    const iframe = document.getElementById('output-frame'),
                        doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write(data.html);
                    doc.close();
                }
            } catch (error) {
                console.error('Error running code:', error);
            }
        }

        async function submitAnswer() {
            currentFiles[currentActiveFile] = editor.getValue();
            const code = currentFiles[currentActiveFile];
            if (!currentLessonId) return;
            try {
                const data = await (await fetch('../../../server/lesson/api_check.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `lesson_id=${currentLessonId}&code=${encodeURIComponent(code)}`
                })).json();
                if (data.success && data.correct) {
                    if (!userProgress.includes(currentLessonId)) userProgress.push(currentLessonId);
                    const l = allLessons.find(x => x.id === currentLessonId),
                        exp = l?.exp || 10;
                    document.getElementById('earned-xp').textContent = exp;
                    saveXPToAccount(currentLessonId, exp);
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
            const idx = allLessons.findIndex(l => l.id === currentLessonId);
            if (idx < allLessons.length - 1) loadLesson(allLessons[idx + 1].id);
            else window.location.href = '../dashboard_player.php';
        }

        function updateProgressBar() {
            const pct = allLessons.length > 0 ? Math.min(100, Math.round((userProgress.length / allLessons.length) * 100)) : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
            document.getElementById('progress-text').textContent = pct + '%';
        }

        async function saveXPToAccount(lessonId, exp) {
            try {
                const data = await (await fetch('../../../server/lesson/api_exp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lesson_id: lessonId,
                        exp_amount: exp
                    })
                })).json();
                if (data.success) console.log('XP saved: +' + data.exp_added + ' XP (Total: ' + data.total_exp + ')');
                else console.error('Error saving XP:', data.message);
            } catch (error) {
                console.error('Error saving XP:', error);
            }
        }
    </script>

</body>

</html>