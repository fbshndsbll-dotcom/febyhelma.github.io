<?php
session_start();// Nyalain sesi dulu biar bisa ngapus data login yang aktif
session_unset(); // Hapus semua isi sesi (kayak username atau data login yang disimpen)
session_destroy(); // Tutup sesi sepenuhnya, biar user bener-bener keluar
header("Location: index.php?pesan=Logout");  //logout, langsung pindah ke halaman index dan kirim pesan "Logout"
exit;
?>
