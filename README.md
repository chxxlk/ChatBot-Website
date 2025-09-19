# ChatBot-Website 🚀

[![forthebadge](https://forthebadge.com/images/badges/made-with-react.svg)](https://react.dev/)
[![forthebadge](https://forthebadge.com/images/badges/made-with-php.svg)](https://www.php.net/)

Chatbot Powered by Model AI, Custom Chatbots for Specific Websites.
# 📌 Feature
* Custom Chat
* Multi-Platform
* Smart Search
# Tech Stack
* *Backend  : Laravel 12, PHP 8.3.16*
* *Frontend : React + Vite + Typescript*
* *Database : MySQL (Local)*
* *AI        : Gemini AI*
# 📂 Project Structure
>├── backend/        # Laravel project </br>
├── frontend/       # React project </br>
└── README.md       # Dokumentasi</br>
# 💡 Installation
* # Clone repo
```
git clone https://github.com/chxxlk/Chatbot-Website.git
```
## Masuk folder backend
```
cd gemini-chatbot-backend
```
### Install requirements
```
composer install
```
### Buat file .env dan isi apa yang diperlukan
```
cp .env.example .env
```
### Generate Key
```
php artisan key:generate
```
### Buat migrasi database _(Pastikan sudah mengatur database di file .env)_
```
php artisan migration
```

## Masuk folder frontend
```
cd gemini-chatbot
```
### Instal requirements
```
npm install
```
### Buat file .env dan isi route ke API backend Laravel _(Default: http://localhost:8000/api)_
```
cp .env.example .env
```
# ▶️ Usage
* ## Jalankan backend
```
php artisan serve
```
* ## Jalankan frontend
```
npm run dev
```
# 📷 Screenshot
Tampilan
<p align="center"> <img src="img/screenshot_4.png" alt="screenshot" width="80%"/> </p>

# 🌟 Support
Kalau Repo ini bermanfaat, jangan lupa kasih ⭐ di GitHub ya!
