<script setup>
import { ref, onMounted } from 'vue'

// State untuk menyimpan daftar kuis
const quizzes = ref([])

// State untuk form input kuis baru
const newQuiz = ref({ 
  judul: '', 
  materi: '', 
  soal: '', 
  snippet: '', 
  jawaban_benar: '',
  score: 10 // Default skor per soal
})

// Fungsi Ambil Data dari API PHP
const fetchQuizzes = async () => {
  try {
    const res = await fetch('http://localhost/Quizweb/server/api_quiz.php')
    quizzes.value = await res.json()
  } catch (error) {
    console.error("Gagal mengambil data:", error)
  }
}

// Fungsi Tambah Kuis (Create)
const addQuiz = async () => {
  if (!newQuiz.value.judul || !newQuiz.value.soal) {
    alert("Judul dan Soal wajib diisi!")
    return
  }

  try {
    const res = await fetch('http://localhost/Quizweb/server/api_quiz.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newQuiz.value)
    })
    
    const result = await res.json()
    alert(result.message)
    
    // Reset Form
    newQuiz.value = { judul: '', materi: '', soal: '', snippet: '', jawaban_benar: '', score: 10 }
    fetchQuizzes() // Refresh tabel
  } catch (error) {
    alert("Gagal menambah kuis")
  }
}

// Fungsi Hapus Kuis (Delete)
const deleteQuiz = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus kuis ini?')) {
    try {
      await fetch(`http://localhost/Quizweb/server/api_quiz.php?id=${id}`, { 
        method: 'DELETE' 
      })
      fetchQuizzes()
    } catch (error) {
      alert("Gagal menghapus")
    }
  }
}

// Jalankan fungsi fetch saat halaman dimuat
onMounted(fetchQuizzes)
</script>

<template>
  <div class="flex h-screen bg-gray-100 font-sans">
    <div class="w-64 bg-slate-900 text-white p-6 shadow-xl">
      <h2 class="text-xl font-bold border-b border-slate-700 pb-4 mb-6 text-blue-400">
        <i class="fas fa-user-shield mr-2"></i>Admin Panel
      </h2>
      <nav class="space-y-2">
        <div class="p-3 bg-blue-600 rounded-lg cursor-pointer">Manage Quizzes</div>
        <div class="p-3 hover:bg-slate-800 rounded-lg cursor-pointer transition text-slate-400">Manage Players</div>
        <div class="p-3 hover:bg-slate-800 rounded-lg cursor-pointer transition text-slate-400">Leaderboard</div>
      </nav>
    </div>

    <div class="flex-1 overflow-y-auto p-10">
      <h1 class="text-3xl font-bold text-slate-800 mb-8">Pengelolaan Materi Quiz</h1>

      <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 mb-10">
        <h2 class="text-lg font-semibold mb-6 text-slate-700">Buat Pertanyaan Baru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <input v-model="newQuiz.judul" type="text" placeholder="Judul Kuis (Contoh: Looping JS)" class="input-style">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-slate-500">Bobot Skor:</span>
            <input v-model="newQuiz.score" type="number" class="input-style w-24 text-center font-bold text-blue-600">
          </div>
          <input v-model="newQuiz.materi" type="text" placeholder="Kategori (Contoh: JavaScript)" class="input-style col-span-2">
          <textarea v-model="newQuiz.soal" placeholder="Pertanyaan..." class="input-style col-span-2 h-24"></textarea>
          <textarea v-model="newQuiz.snippet" placeholder="Masukkan Code Snippet (Opsional)..." class="input-style col-span-2 h-32 font-mono text-sm bg-slate-50 text-slate-700"></textarea>
          <input v-model="newQuiz.jawaban_benar" type="text" placeholder="Kunci Jawaban" class="input-style col-span-2 border-emerald-200 focus:ring-emerald-500">
          
          <button @click="addQuiz" class="col-span-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-blue-200">
            Simpan Pertanyaan & Skor
          </button>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-slate-50 border-b border-gray-100">
            <tr>
              <th class="p-4 text-sm font-bold text-slate-600 uppercase">Judul & Materi</th>
              <th class="p-4 text-sm font-bold text-slate-600 uppercase text-center">Skor</th>
              <th class="p-4 text-sm font-bold text-slate-600 uppercase text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="q in quizzes" :key="q.id" class="border-b border-gray-50 hover:bg-slate-50 transition">
              <td class="p-4">
                <div class="font-bold text-slate-800">{{ q.judul }}</div>
                <div class="text-xs text-slate-400 uppercase tracking-widest">{{ q.materi }}</div>
              </td>
              <td class="p-4 text-center">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
                  +{{ q.score }}
                </span>
              </td>
              <td class="p-4 text-right">
                <button @click="deleteQuiz(q.id)" class="text-red-500 hover:text-red-700 font-bold p-2">
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input-style {
  @apply border border-gray-200 p-4 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all;
}
</style>