-- Buat ekstensi vector (jika image mendukung pgvector)
CREATE EXTENSION IF NOT EXISTS vector;

-- Tabel users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id bigserial PRIMARY KEY,
    name varchar NOT NULL,
    email varchar NOT NULL UNIQUE,
    password varchar NOT NULL,
    role varchar CHECK (role IN ('superadmin', 'admin', 'user')) DEFAULT 'user',
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel sessions
DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
    id varchar PRIMARY KEY,
    user_id bigint REFERENCES users(id),
    ip_address varchar(45),
    user_agent text,
    payload text,
    last_activity integer
);

-- Tabel personal_access_tokens
DROP TABLE IF EXISTS personal_access_tokens;
CREATE TABLE personal_access_tokens (
    id bigserial PRIMARY KEY,
    tokenable_type varchar,
    tokenable_id bigint,
    name varchar NOT NULL,
    token varchar(64) NOT NULL UNIQUE,
    abilities text,
    last_used_at timestamp with time zone,
    expires_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel pengumuman
DROP TABLE IF EXISTS pengumuman;
CREATE TABLE pengumuman (
    id bigserial PRIMARY KEY,
    judul varchar NOT NULL,
    isi text NOT NULL,
    file varchar,
    kategori varchar,
    user_id bigint REFERENCES users(id) ON DELETE CASCADE,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel lowongan
DROP TABLE IF EXISTS lowongan;
CREATE TABLE lowongan (
    id bigserial PRIMARY KEY,
    judul varchar NOT NULL,
    deskripsi text NOT NULL,
    file varchar,
    link_pendaftaran varchar,
    user_id bigint REFERENCES users(id) ON DELETE CASCADE,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel dosen
DROP TABLE IF EXISTS dosen;
CREATE TABLE dosen (
    id bigserial PRIMARY KEY,
    nama_lengkap varchar NOT NULL,
    keahlian_rekognisi text,
    email varchar NOT NULL UNIQUE,
    external_link varchar,
    photo varchar,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel history
DROP TABLE IF EXISTS chat_history;
CREATE TABLE chat_history (
    id bigserial PRIMARY KEY,
    user_message text NOT NULL,
    bot_response text NOT NULL,
    session_id varchar DEFAULT 'default',
    source varchar NOT NULL,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);

-- Tabel embeddings (vector)
DROP TABLE IF EXISTS embeddings;
CREATE TABLE embeddings (
    id bigserial PRIMARY KEY,
    table_name varchar NOT NULL,
    row_id bigint NOT NULL,
    vector vector(4096),
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    UNIQUE (table_name, row_id)
);

-- Superadmin users
INSERT INTO users (name, email, password, role) VALUES
('Budi Santoso', 'budi.santoso@example.com', 'hashed_password_1', 'superadmin'),
('Siti Rahayu', 'siti.rahayu@example.com', 'hashed_password_2', 'superadmin'),
('Ahmad Wijaya', 'ahmad.wijaya@example.com', 'hashed_password_3', 'superadmin');

-- Admin users  
INSERT INTO users (name, email, password, role) VALUES
('Dewi Kurnia', 'dewi.kurnia@example.com', 'hashed_password_4', 'admin'),
('Rizki Pratama', 'rizki.pratama@example.com', 'hashed_password_5', 'admin'),
('Maya Sari', 'maya.sari@example.com', 'hashed_password_6', 'admin');

-- Regular users
INSERT INTO users (name, email, password, role) VALUES
('Fajar Nugroho', 'fajar.nugroho@example.com', 'hashed_password_7', 'user'),
('Linda Wati', 'linda.wati@example.com', 'hashed_password_8', 'user'),
('Hendra Setiawan', 'hendra.setiawan@example.com', 'hashed_password_9', 'user');

INSERT INTO pengumuman (judul, isi, file, kategori, user_id) VALUES
('Libur Semester Genap', 'Berdasarkan kalender akademik, libur semester genap akan dimulai tanggal 15 Juli 2024', 'kalender_akademik.pdf', 'Academic', 1),
('Pendaftaran Wisuda', 'Pendaftaran wisuda periode September 2024 dibuka mulai 1 Agustus 2024', 'form_wisuda.pdf', 'Academic', 2),
('Pemadaman Listrik', 'Akan dilakukan pemadaman listrik untuk pemeliharaan pada tanggal 20 Juli 2024 pukul 09.00-15.00 WIB', NULL, 'Facility', 4),
('Seminar Kewirausahaan', 'Seminar kewirausahaan dengan tema "Digital Business Strategy" akan diselenggarakan pada 25 Juli 2024', 'poster_seminar.jpg', 'Event', 5),
('Perpanjangan Masa Studi', 'Batas perpanjangan masa studi untuk mahasiswa angkatan 2020 adalah 31 Agustus 2024', 'sk_rektor.pdf', 'Academic', 1);

INSERT INTO lowongan (judul, deskripsi, file, link_pendaftaran, user_id) VALUES
('Software Developer', 'Dibutuhkan software developer dengan pengalaman 2 tahun di bidang web development', 'jd_developer.pdf', 'https://career.example.com/dev', 4),
('Data Analyst', 'Lowongan untuk data analyst dengan kemampuan SQL, Python, dan data visualization', 'jd_analyst.pdf', 'https://career.example.com/analyst', 5),
('Research Assistant', 'Asisten peneliti untuk proyek artificial intelligence dan machine learning', 'jd_research.pdf', 'https://research.example.com/apply', 2),
('Marketing Specialist', 'Specialist marketing digital dengan pengalaman di social media marketing', 'jd_marketing.pdf', 'https://career.example.com/marketing', 4),
('Administrative Staff', 'Staf administrasi untuk mengelola dokumen dan surat-menyurat', 'jd_admin.pdf', 'https://career.example.com/admin', 5);

INSERT INTO dosen (nama_lengkap, keahlian_rekognisi, email, external_link, photo) VALUES
('Prof. Dr. Indra Gunawan, M.Sc.', 'Artificial Intelligence, Machine Learning, Natural Language Processing', 'indra.gunawan@university.ac.id', 'https://scholar.google.com/indragunawan', 'indra_gunawan.jpg'),
('Dr. Maria Ulfa, S.T., M.T.', 'Computer Vision, Image Processing, Pattern Recognition', 'maria.ulfa@university.ac.id', 'https://researchgate.net/mariaulfa', 'maria_ulfa.jpg'),
('Dr. Rudi Hermawan, S.Kom., M.Kom.', 'Database Systems, Big Data, Cloud Computing', 'rudi.hermawan@university.ac.id', 'https://dblp.org/rudihermawan', 'rudi_hermawan.jpg'),
('Dian Purnama Sari, S.Si., M.Cs.', 'Human-Computer Interaction, User Experience Design, Interaction Design', 'dian.purnama@university.ac.id', 'https://linkedin.com/in/dianpurnama', 'dian_purnama.jpg'),
('Dr. Eko Prasetyo, M.Inf.Tech.', 'Network Security, Cybersecurity, Cryptography', 'eko.prasetyo@university.ac.id', 'https://github.com/ekoprasetyo', 'eko_prasetyo.jpg');