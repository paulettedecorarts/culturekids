# Paulette Culture Kids - Windows Setup Guide

This project is a multi-tenant cultural heritage platform for kids, consisting of a Laravel API (`culturekids-api`) and an Expo mobile application (`paulette-culture-kids`).

## 1. Prerequisites (Windows)

Ensure you have the following installed on your machine:
*   [XAMPP](https://www.apachefriends.org/download.html) (PHP 8.2+ and MySQL)
*   [Node.js & npm](https://nodejs.org/) (for the Expo app)
*   [Memurai Developer](https://www.memurai.com/get-memurai) (Redis compatible cache for Windows)

---

## 2. Backend Setup (Laravel API)

1.  **Move to the API folder**:
    ```powershell
    cd culturekids-api
    ```

2.  **XAMPP Configuration**:
    *   Open **XAMPP Control Panel**.
    *   Start **MySQL** (Default port is usually `3306`, this project currently uses `3307`).
    *   Open `http://localhost/phpmyadmin` and create a database named `paulette_culture_kids`.

3.  **Environment Setup**:
    *   The `.env` file is already pre-configured for **XAMPP (Port 3307)** and **Memurai**.
    *   If you need to use Composer commands, use the bundled `composer.phar`:
    ```powershell
    php composer.phar install
    ```

4.  **Database Migration**:
    ```powershell
    php artisan migrate
    ```
4.1 **Seed data**
    ```powershell
    php artisan db:seed
    ```
    or
    ```
    php artisan migrate:fresh --seed
    ```

5.  **Run API Server**:
    ```powershell
    php artisan serve
    ```
    *   Check system health at: [http://localhost:8000/health](http://localhost:8000/health)

---

## 3. Redis Setup (Memurai)

To ensure the caching layer is active:
*   Make sure the **Memurai** service is running in Windows Services.
*   To check status in PowerShell: `Get-Service Memurai`
*   To start if stopped: `Start-Service Memurai`

---

## 4. Frontend Setup (Mobile App)

1.  **Move to the Mobile app folder**:
    ```powershell
    cd paulette-culture-kids
    ```

2.  **Install dependencies**:
    ```powershell
    npm install
    ```

3.  **Run the application**:
    ```powershell
    npx expo start
    ```

---

## Project Structure
*   `culturekids-api/`: Laravel backend (PHP).
*   `paulette-culture-kids/`: Expo mobile app (React Native/Typescript).
*   `docs/`: Additional project documentation.
