<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeMaster - Level Up Your Coding Skills</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
        }

        .hero-gradient {
            background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 50%);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body class="text-slate-200 overflow-x-hidden">

    <nav class="fixed w-full z-50 px-8 py-6 backdrop-blur-md border-b border-white/5">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-2 rounded-lg"><i class="fas fa-code text-white"></i></div>
                <span class="font-black text-xl tracking-tighter text-white uppercase">CODE<span class="text-blue-500">MASTER</span></span>
            </div>
            <div class="flex gap-4">
                <a href="login.php" class="text-sm font-bold hover:text-blue-400 transition py-2 px-4">Login</a>
                <a href="register.php" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold transition shadow-lg shadow-blue-600/20">Register</a>
            </div>
        </div>
    </nav>

    <section class="relative min-h-screen flex items-center pt-20 hero-gradient">
        <div class="container mx-auto px-8 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 px-4 py-2 rounded-full mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span class="text-xs font-bold text-blue-400 uppercase tracking-widest">Platform Kuis IT No. 1</span>
                </div>
                <h1 class="text-6xl lg:text-7xl font-black text-white leading-none mb-6">
                    ASAH SKILL <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">CODING-MU</span>
                </h1>
                <p class="text-lg text-slate-400 mb-10 max-w-lg leading-relaxed">
                    Jadilah developer handal dengan menyelesaikan misi kuis interaktif. Kumpulkan XP, kuasai berbagai bahasa pemrograman, dan puncaki Leaderboard!
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="register.php" class="bg-blue-600 hover:bg-blue-500 text-white px-10 py-4 rounded-2xl font-black transition-all transform hover:-translate-y-1 shadow-2xl shadow-blue-600/40">
                        START MISSION <i class="fas fa-rocket ml-2"></i>
                    </a>
                    <div class="flex -space-x-3 items-center ml-4">
                        <img class="w-10 h-10 rounded-full border-2 border-[#0f172a]" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKkvtHqh_nteQCfh8aIGhnXfjY7X8Gq9G88Q&s">
                        <img class="w-10 h-10 rounded-full border-2 border-[#0f172a]" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSMaL8ADYeMTI_d05vO12jbizVKahEtBc_nmQ&s">
                        <img class="w-10 h-10 rounded-full border-2 border-[#0f172a]" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9Pe68oq409W00aqhW1tJtuHSUng_4Zk265g&s">
                        <span class="pl-6 text-sm font-bold text-slate-500">1617 Players Joined!</span>
                    </div>
                </div>
            </div>

            <div class="relative hidden lg:block">
                <div class="animate-float">
                    <div class="bg-slate-800/50 backdrop-blur-2xl p-8 rounded-[3rem] border border-white/10 shadow-3xl">
                        <div class="flex gap-2 mb-6">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <pre class="text-blue-400 font-mono text-sm leading-relaxed">
<span class="text-purple-400">class</span> <span class="text-yellow-300">master</span> {
  <span class="text-purple-400">constructor</span>(name) {
    <span class="text-blue-300">this</span>.name = codemaster;
    <span class="text-blue-300">this</span>.level = <span class="text-orange-400">master</span>;
    <span class="text-blue-300">this</span>.status = <span class="text-green-400">'Ongoing'</span>;
  }

  <span class="text-yellow-300">coding</span>() {
    <span class="text-purple-400">return</span> <span class="text-green-400">"Keep Leveling Up!"</span>;
  }
}</pre>
                    </div>
                </div>
                <div class="absolute -bottom-10 -left-10 bg-emerald-500 p-6 rounded-3xl shadow-xl animate-bounce">
                    <i class="fas fa-check-double text-white text-3xl"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 border-t border-white/5 bg-slate-900/50">
        <div class="container mx-auto px-8">

            <h1 class="text-6xl lg:text-7xl font-black text-white leading-none mb-6 text-center">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-orange-400"> ROGUELIKE</span>
                QUIZ
            </h1>
            <p class="text-lg text-slate-400 mb-10 max-w-lg leading-relaxed mx-auto text-center">
                Satu kesalahan berarti maut. Hadapi kuis yang diacak secara prosedural, kumpulkan <span class="text-blue-400 font-bold">EXP</span>, dan capai puncak
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <div class="flex gap-4 p-4">
                    <div class="text-red-500 mt-1"><i class="fas fa-skull-crossbones text-2xl"></i></div>
                    <div>
                        <h5 class="text-white font-black text-sm uppercase">Permadeath</h5>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">Hanya punya 3 nyawa per game. Jika habis, XP lantai tersebut hangus.</p>
                    </div>
                </div>
                <div class="flex gap-4 p-4">
                    <div class="text-yellow-500 mt-1"><i class="fas fa-random text-2xl"></i></div>
                    <div>
                        <h5 class="text-white font-black text-sm uppercase">Procedural Deck</h5>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">Urutan soal diacak setiap kali kamu menekan tombol 'Deploy'.</p>
                    </div>
                </div>
                <div class="flex gap-4 p-4">
                    <div class="text-blue-500 mt-1"><i class="fas fa-dna text-2xl"></i></div>
                    <div>
                        <h5 class="text-white font-black text-sm uppercase">Skill Progression</h5>
                        <p class="text-slate-500 text-xs mt-1 leading-relaxed">XP yang terkumpul akan menentukan peringkatmu di Global Leaderboard.</p>
                    </div>
                </div>
            </div>

            <section class="py-20 border-t border-white/5 bg-slate-900/50">
                <div class="container mx-auto px-8">

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                        <div>
                            <h4 class="text-4xl font-black text-white mb-2">500+</h4>
                            <p class="text-slate-500 font-bold uppercase text-xs tracking-widest">Questions</p>
                        </div>
                        <div>
                            <h4 class="text-4xl font-black text-white mb-2">10+</h4>
                            <p class="text-slate-500 font-bold uppercase text-xs tracking-widest">Languages</p>
                        </div>
                        <div>
                            <h4 class="text-4xl font-black text-white mb-2">1.2k</h4>
                            <p class="text-slate-500 font-bold uppercase text-xs tracking-widest">Active Users</p>
                        </div>
                        <div>
                            <h4 class="text-4xl font-black text-white mb-2">24/7</h4>
                            <p class="text-slate-500 font-bold uppercase text-xs tracking-widest">Learning</p>
                        </div>
                    </div>
                </div>

                <section class="mt-20 py-20 border-t border-white/5 bg-slate-900/50">
                    <div class="container mx-auto px-8">
                </section>

                <section class="container mx-auto px-8">
                    <div class="text-center mb-20">
                        <h2 class="text-4xl font-black text-white mb-4">MENGAPA CODEMASTER?</h2>
                        <p class="text-slate-500 max-w-lg mx-auto font-medium">Belajar coding tidak pernah seseru ini sebelumnya.</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="bg-slate-800/30 p-10 rounded-[2.5rem] border border-white/5 hover:border-blue-500/50 transition-all group">
                            <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <i class="fas fa-brain text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">Adaptive Learning</h3>
                            <p class="text-slate-400 leading-relaxed text-sm">Soal diacak dan disesuaikan untuk melatih logika berpikir secara mendalam.</p>
                        </div>
                        <div class="bg-slate-800/30 p-10 rounded-[2.5rem] border border-white/5 hover:border-purple-500/50 transition-all group">
                            <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <i class="fas fa-medal text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">Leaderboard System</h3>
                            <p class="text-slate-400 leading-relaxed text-sm">Bersaing dengan developer lain dari seluruh dunia dan jadilah nomor satu.</p>
                        </div>
                        <div class="bg-slate-800/30 p-10 rounded-[2.5rem] border border-white/5 hover:border-cyan-500/50 transition-all group">
                            <div class="w-14 h-14 bg-cyan-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <i class="fas fa-code-branch text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">Multi-Language</h3>
                            <p class="text-slate-400 leading-relaxed text-sm">Tersedia kuis untuk JavaScript, Python, PHP, Java, C++, dan masih banyak lagi.</p>
                        </div>
                    </div>
                </section>

                <!-- Mouse Tracking Spotlight -->
                <div id="mouse-spotlight"
                    class="fixed pointer-events-none rounded-full opacity-0 transition-opacity duration-500 z-[9999]"
                    style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%); filter: blur(30px); transform: translate(-50%, -50%); transition: opacity 0.5s, transform 0.1s ease-out;">
                </div>
                <script>
                    const spotlight = document.getElementById('mouse-spotlight');

                    document.addEventListener('mousemove', e => {
                        // Update posisi & munculkan spotlight
                        spotlight.style.left = `${e.clientX}px`;
                        spotlight.style.top = `${e.clientY}px`;
                        spotlight.style.opacity = '1';
                    });

                    document.addEventListener('mouseleave', () => spotlight.style.opacity = '0');
                </script>

</body>

</html>