<?php
session_start();
include 'koneksi.php'; // Hubungkan ke file koneksi database

if (!isset($_SESSION['username'])) { // Cek: Sudah login BELUM?
    die("Akses ditolak! Harus login dulu."); // Kalau belum, hentikan dan tampilkan pesan
}

$komenId = (int)($_GET['id'] ?? 0); // Ambil ID komentar dari URL (jadikan angka, default 0)
$filmId  = (int)($_GET['film'] ?? 0); // Ambil ID film dari URL (untuk redirect, jadikan angka, default 0)

// Cek apakah komentar ada
$stmt = $koneksi->prepare("SELECT * FROM ratings WHERE id=?");
$stmt->execute([$komenId]); // Jalankan perintah dengan ID komentar
$komen = $stmt->fetch(PDO::FETCH_ASSOC); // Ambil data komentar

if (!$komen) { // Jika data komentar tidak ditemukan
    die("Komentar tidak ditemukan!"); // Hentikan dan tampilkan pesan
}

// Hanya boleh hapus jika admin ATAU pemilik komentar
if ($_SESSION['role'] == 'admin' || $komen['username'] == $_SESSION['username']) { 
    $stmt = $koneksi->prepare("DELETE FROM ratings WHERE id=?"); // Siapkan perintah DELETE
    $stmt->execute([$komenId]); // Hapus komentar
    header("Location: detail_film.php?id=$filmId"); // Setelah berhasil, kembalikan ke halaman detail film tersebut
    exit; // Stop proses di sini
} else {
    die("Anda tidak punya izin untuk menghapus komentar ini!"); // Jika bukan admin dan bukan pemilik komentar, tolak akses
}