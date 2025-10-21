<?php
session_start(); // Mulai sesi (untuk menyimpan status login)

$db = new PDO('sqlite:listfilm.db'); // Sambungkan ke database SQLite
$username = $_POST['username'] ?? ''; // Ambil username dari form, jika tidak ada, default-kan kosong
$password = $_POST['password'] ?? ''; // Ambil password dari form, jika tidak ada, default-kan kosong

if ($username === '' || $password === '') { // Cek apakah username atau password kosong
    header("Location: login.php?error=Username atau password wajib diisi"); // Kalau kosong, balikin ke login.php dengan pesan error
    exit; 
}

$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    if ($password === $user['password']) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = ($user['username'] === "PEBHEL") ? "admin" : "user";

        if ($_SESSION['role'] === "admin") {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_users.php");
        }
        exit;
    } else {
        header("Location: login.php?error=Password salah");
        exit;
    }
} else {
    header("Location: registrasi.php");
    exit;
}




