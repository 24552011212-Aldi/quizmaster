-- Tabel kategori materi lesson
CREATE TABLE IF NOT EXISTS materi_lesson (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(64) NOT NULL,
    icon VARCHAR(64) DEFAULT NULL
);

-- Tambah kolom materi_id ke lessons
ALTER TABLE lessons ADD COLUMN materi_id INT DEFAULT NULL;
ALTER TABLE lessons ADD CONSTRAINT fk_materi_lesson FOREIGN KEY (materi_id) REFERENCES materi_lesson(id) ON DELETE SET NULL;

-- Contoh data materi
INSERT INTO materi_lesson (nama, icon) VALUES
('HTML', 'fa-html5'),
('CSS', 'fa-css3-alt'),
('Javascript', 'fa-js'),
('PHP', 'fa-php'),
('Python', 'fa-python');

-- Contoh lesson per materi
INSERT INTO lessons (title, content, starter_code, order_no, materi_id) VALUES
('Heading Dasar', '<p>Buatlah sebuah heading &lt;h1&gt; dengan teks "Hello World"</p>', '<h1>Hello World</h1>', 1, 1),
('Paragraf HTML', '<p>Buat paragraf &lt;p&gt; dengan isi bebas</p>', '<p>Belajar HTML itu mudah!</p>', 2, 1),
('Selector CSS', '<p>Buat style CSS untuk mengubah warna teks &lt;h1&gt; menjadi biru</p>', 'h1 { color: blue; }', 1, 2),
('Alert Javascript', '<p>Buat kode JS untuk menampilkan alert "Hai!"</p>', 'alert("Hai!");', 1, 3),
('Echo PHP', '<p>Tampilkan "Halo Dunia" menggunakan PHP</p>', '<?php echo "Halo Dunia"; ?>', 1, 4),
('Print Python', '<p>Tampilkan "Hello Python" menggunakan print()</p>', 'print("Hello Python")', 1, 5);
