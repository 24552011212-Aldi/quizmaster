// File has been removed
<?php
// Halaman input quiz baru per materi
include '../server/auth_check.php';
checkLogin();
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}
include '../server/config/koneksi.php';

// Ambil daftar materi
$materi = [];
$res = mysqli_query($conn, "SELECT * FROM materi ORDER BY nama ASC");
if (!$res) {
    die("Gagal query materi: " . mysqli_error($conn));
}
while ($row = mysqli_fetch_assoc($res)) {
    $materi[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Quiz Baru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-2xl mx-auto bg-white rounded-3xl shadow-lg p-10">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-black text-slate-900 flex items-center gap-3"><i class="fas fa-plus-circle text-blue-500"></i> Tambah Quiz Baru</h1>
            <div class="flex gap-2">
                <a href="index.php" class="bg-slate-200 text-slate-700 px-5 py-2 rounded-2xl font-bold hover:bg-slate-300 transition flex items-center gap-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                <a href="quiz_add.php" class="bg-blue-100 text-blue-600 px-5 py-2 rounded-2xl font-bold hover:bg-blue-200 transition flex items-center gap-2"><i class="fas fa-plus"></i> Quiz Baru</a>
            </div>
        </div>
        <form action="../server/admin/quiz_create.php" method="post" id="quizForm" class="space-y-6">
            <div>
                <label class="block mb-2 font-bold text-slate-700">Pilih Materi</label>
                <select name="id_materi" class="w-full p-4 rounded-2xl border bg-slate-50" required>
                    <option value="" selected>-- Pilih Materi --</option>
                    <?php foreach($materi as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block mb-2 font-bold text-slate-700">Judul Quiz</label>
                <input type="text" name="judul" class="w-full p-4 rounded-2xl border bg-slate-50" required value="">
            </div>
            <div>
                <label class="block mb-2 font-bold text-slate-700">Soal</label>
                <textarea name="soal" class="w-full p-4 rounded-2xl border bg-slate-50 h-24" required></textarea>
            </div>
            <div>
                <label class="block mb-2 font-bold text-slate-700">Code Snippet <span class="text-xs text-slate-400">(opsional)</span></label>
                <textarea name="snippet" class="w-full p-4 rounded-2xl border bg-slate-900 text-emerald-400 font-mono text-sm h-32"></textarea>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block mb-2 font-bold text-slate-700">Opsi A</label>
                    <input type="text" name="opsi_a" class="w-full p-4 rounded-2xl border bg-slate-50" required value="">
                </div>
                <div>
                    <label class="block mb-2 font-bold text-slate-700">Opsi B</label>
                    <input type="text" name="opsi_b" class="w-full p-4 rounded-2xl border bg-slate-50" required value="">
                </div>
                <div>
                    <label class="block mb-2 font-bold text-slate-700">Opsi C</label>
                    <input type="text" name="opsi_c" class="w-full p-4 rounded-2xl border bg-slate-50" required value="">
                </div>
                <div>
                    <label class="block mb-2 font-bold text-slate-700">Opsi D</label>
                    <input type="text" name="opsi_d" class="w-full p-4 rounded-2xl border bg-slate-50" required value="">
                </div>
            </div>
            <div>
                <label class="block mb-2 font-bold text-slate-700">Jawaban Benar</label>
                <select name="jawaban_benar" class="w-full p-4 rounded-2xl border bg-slate-50" required>
                    <option value="" selected>-- Pilih --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 font-bold text-slate-700">XP (Score)</label>
                <input type="number" name="score" class="w-full p-4 rounded-2xl border bg-slate-50" value="10" min="1" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl text-lg hover:bg-blue-700 transition">Simpan Quiz</button>
        </form>
    </div>
</body>
</html>
