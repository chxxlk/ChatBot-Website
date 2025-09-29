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