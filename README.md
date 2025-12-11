# ChatBot-Website 🚀
Chatbot Powered by AI Model, Custom Chatbots for Specific Websites (FTI UKSW Website).

[![forthebadge](https://forthebadge.com/images/badges/made-with-react.svg)](https://react.dev/)
[![forthebadge](https://forthebadge.com/images/badges/made-with-php.svg)](https://www.php.net/)
# 📌Feature
* Custom Chat
* Multi-Platform
* Smart Search
# 🖥️Tech Stack
* *Backend  : Laravel 12, PHP 8.3.16*
* *Frontend : React + Vite + Typescript*
* *Database : PostgreSQL + pgvector*
* *AI        : Model Deepseek 3.1 with OpenRouter*
# 📂Project Structure
>├── backend/        # Laravel project </br>
>├── frontend/       # React project </br>
>└── README.md       # Dokumentasi</br>
# 💡Installation
```
git clone https://github.com/chxxlk/Chatbot-Website.git

# Masuk Foleder backend
cd backend
composer install
```
### Buat file .env dan isi apa yang diperlukan
```
cp .env.example .env
# Tambahkan Databse, Openrouter API, Hugginface API untuk embedding, Cors, dll di .env
# Jalankan Migrate dan Seeder
php artisan migrate:fresh --seed
# Tambahkan Databse, Openrouter API, Huggingface API, dll di .env

# Masuk folder frontend
cd frontend
cp .env.example .env
# isi base url ke backend
npm install
```

# ▶️ Usage
* ## Jalankan backend
```
# Jalankan backend
php atisan serve --host=0.0.0.0 --route=8000 //untuk running local

# Jalankan frontend
npm run dev
```
# 📷Screenshot
<p align="center"> <img src="img/screenshot_4.png" alt="screenshot" width="80%"/> </p>

# 🌟Support
Kalau Repo ini bermanfaat, jangan lupa kasih ⭐ di GitHub ya!
