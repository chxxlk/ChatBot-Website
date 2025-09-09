# ChatBot-Website
Chatbot Powered by Gemini AI, Custom Chatbots for Specific Websites


Untuk Dummy Database MySQL, kamu bisa menggunakan seperti ini:

CREATE TABLE ti_db;
USE ti_db;

-- ========================
-- 1. Tabel PENGUMUMAN
-- ========================
CREATE TABLE pengumuman (
    id_pengumuman INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200),
    isi TEXT,
    tanggal DATE
);

INSERT INTO pengumuman (judul, isi, tanggal) VALUES
('Ujian Tengah Semester Ganjil 2025', 'UTS akan dilaksanakan pada tanggal 10-15 Oktober 2025.', '2025-09-01'),
('Pengumpulan Proposal PKM', 'Batas akhir pengumpulan proposal PKM adalah 20 September 2025.', '2025-09-05'),
('Penerimaan Beasiswa Yayasan', 'Mahasiswa dapat mengajukan beasiswa mulai 12 September 2025.', '2025-09-07'),
('Jadwal Kuliah Pengganti', 'Kuliah Jaringan Komputer akan diganti tanggal 18 September 2025.', '2025-09-08'),
('Pendaftaran Wisuda Periode November 2025', 'Pendaftaran dibuka mulai 25 September 2025.', '2025-09-09'),
('Workshop AI dan Data Science', 'Akan dilaksanakan workshop AI pada 30 September 2025.', '2025-09-09');

-- ========================
-- 2. Tabel BERITA ALUMNI
-- ========================
CREATE TABLE berita_alumni (
    id_berita INT AUTO_INCREMENT PRIMARY KEY,
    nama_alumni VARCHAR(100),
    judul_berita VARCHAR(200),
    isi TEXT,
    tanggal DATE
);

INSERT INTO berita_alumni (nama_alumni, judul_berita, isi, tanggal) VALUES
('Andi Pratama', 'Alumni TI Menjadi CTO Startup', 'Andi kini menjabat sebagai CTO di startup teknologi finansial.', '2025-07-10'),
('Rina Santoso', 'Rina Raih Gelar Master di Jepang', 'Rina berhasil menyelesaikan studi S2 di Tokyo Institute of Technology.', '2025-06-22'),
('Budi Wijaya', 'Alumni Budi Mendapatkan Penghargaan Inovasi', 'Budi menerima penghargaan inovasi dari Kemenristek.', '2025-05-15'),
('Siti Aminah', 'Karir Gemilang di Google', 'Siti bekerja sebagai software engineer di Google.', '2025-04-11'),
('David Kusuma', 'David Membuka Perusahaan Konsultan IT', 'Perusahaan konsultan IT milik David berkembang pesat.', '2025-03-30'),
('Maya Anggraini', 'Maya Menjadi Dosen di UNS', 'Maya bergabung menjadi dosen tetap di UNS.', '2025-02-17');

-- ========================
-- 3. Tabel LOWONGAN ASISTEN DOSEN
-- ========================
CREATE TABLE lowongan_asisten_dosen (
    id_lowongan INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah VARCHAR(100),
    kualifikasi TEXT,
    deadline DATE,
    kontak VARCHAR(100)
);

INSERT INTO lowongan_asisten_dosen (mata_kuliah, kualifikasi, deadline, kontak) VALUES
('Pemrograman Web', 'Minimal semester 5, menguasai HTML, CSS, dan JavaScript.', '2025-09-20', 'webti@kampus.ac.id'),
('Basis Data', 'IPK minimal 3.25, mampu mengajar SQL.', '2025-09-22', 'dbti@kampus.ac.id'),
('Jaringan Komputer', 'Menguasai jaringan dasar, konfigurasi router, dan Linux.', '2025-09-25', 'jarkomti@kampus.ac.id'),
('Kecerdasan Buatan', 'Menguasai Python dan library AI.', '2025-09-28', 'aisti@kampus.ac.id'),
('Sistem Operasi', 'Minimal semester 6, menguasai Linux.', '2025-09-29', 'sosti@kampus.ac.id'),
('Rekayasa Perangkat Lunak', 'Memiliki pengalaman proyek software.', '2025-09-30', 'rplti@kampus.ac.id');

-- ========================
-- 4. Tabel PROFIL PRODI
-- ========================
CREATE TABLE profil_prodi (
    id_prodi INT AUTO_INCREMENT PRIMARY KEY,
    visi TEXT,
    misi TEXT,
    akreditasi VARCHAR(10),
    ketua_prodi VARCHAR(100)
);

INSERT INTO profil_prodi (visi, misi, akreditasi, ketua_prodi) VALUES
('Menjadi program studi unggulan di bidang teknologi informasi.', 'Misi: Mendidik mahasiswa agar kompeten di bidang TI.', 'A', 'Dr. Anton Wijaya'),
('Menghasilkan lulusan TI yang berintegritas dan profesional.', 'Misi: Menjalankan kurikulum berbasis industri.', 'A', 'Dr. Ratna Puspita'),
('Menjadi pusat penelitian teknologi informasi di Jawa Tengah.', 'Misi: Melaksanakan penelitian terapan di bidang TI.', 'A', 'Dr. Bagus Santoso'),
('Membangun jejaring industri yang kuat.', 'Misi: Meningkatkan kerjasama dengan industri TI.', 'A', 'Dr. Andini Maharani'),
('Mengembangkan inovasi digital untuk masyarakat.', 'Misi: Memberikan solusi digital yang bermanfaat.', 'A', 'Dr. Rudi Hartanto'),
('Meningkatkan mutu pembelajaran berbasis AI.', 'Misi: Mengintegrasikan AI dalam pembelajaran.', 'A', 'Dr. Lina Susanti');

-- ========================
-- 5. Tabel PROFIL HIMPUNAN MAHASISWA
-- ========================
CREATE TABLE profil_himpunan_mahasiswa (
    id_himpunan INT AUTO_INCREMENT PRIMARY KEY,
    nama_himpunan VARCHAR(100),
    visi TEXT,
    misi TEXT,
    ketua_umum VARCHAR(100),
    periode VARCHAR(20)
);

INSERT INTO profil_himpunan_mahasiswa (nama_himpunan, visi, misi, ketua_umum, periode) VALUES
('Himpunan Mahasiswa TI', 'Menjadi wadah aspirasi mahasiswa TI.', 'Misi: Menyelenggarakan kegiatan akademik dan non-akademik.', 'Ahmad Fauzi', '2025-2026'),
('Himpunan Mahasiswa TI', 'Mengembangkan kreativitas mahasiswa TI.', 'Misi: Mengadakan workshop rutin.', 'Lia Kartika', '2024-2025'),
('Himpunan Mahasiswa TI', 'Meningkatkan solidaritas antar mahasiswa.', 'Misi: Melaksanakan kegiatan sosial.', 'Rizky Pratama', '2023-2024'),
('Himpunan Mahasiswa TI', 'Menjadi organisasi mahasiswa yang profesional.', 'Misi: Membina kaderisasi berkelanjutan.', 'Yuni Andira', '2022-2023'),
('Himpunan Mahasiswa TI', 'Mewujudkan mahasiswa TI yang aktif.', 'Misi: Menyelenggarakan lomba internal dan eksternal.', 'Fajar Nugroho', '2021-2022'),
('Himpunan Mahasiswa TI', 'Mendorong mahasiswa TI berprestasi.', 'Misi: Memberikan wadah kompetisi.', 'Siti Nurhaliza', '2020-2021');
