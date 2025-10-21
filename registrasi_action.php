<?php
//try { menggunakan try-catch lebih aman karena jika ada kesalahan/file rusak akan ada pesan yang jelas
    $db = new PDO('sqlite:listfilm.db'); // konek ke database SQLite dengan nama listfilm.db
    //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // biar error ditampilkan dalam bentuk exception

    $username = $_POST['username'] ?? ''; // ambil data username dari form, kalau kosong isi string kosong ??- untuk mencegah eror(trim=untuk mencegah spasi)
    $password = $_POST['password'] ?? ''; // ambil data password dari form

    if ($username === '' || $password === '') { // kalau salah satu kosong
        $msg = "Username dan password wajib diisi"; // tampilkan pesan ini
        $type = "error"; // tipe pesan error
    } else {
        // cek apakah username sudah ada di database
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username"); // siapkan query
        $stmt->bindValue(':username', $username); // isi nilai username ke query
        $stmt->execute(); // jalankan query
        $cek = $stmt->fetch(PDO::FETCH_ASSOC); // ambil hasilnya

        if ($cek) { // kalau username sudah di  pakai
            $msg = "Username sudah terpakai"; // kasih pesan error
            $type = "error"; // tipe error
        } else {
            // kalau belum ada, simpan user baru ke tabel users
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
            $stmt->execute([':username' => $username, ':password' => $password]); // masukkan data ke database

            $msg = "Registrasi berhasil 🎉 Silakan login."; // pesan sukses
            $type = "success"; // tipe pesan sukses
        }
    }
//} catch (Exception $e) { // kalau ada error saat koneksi atau query
   // $msg = "Error: " . $e->getMessage(); // tampilkan pesan errornya
    //$type = "error"; // tipe error
//}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background:#1e1e2f;
            color:#f1f1f1;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .box {
            background:#2c2c3e;
            padding:25px 30px;
            border-radius:12px;
            box-shadow:0 6px 16px rgba(0,0,0,0.5);
            width:380px;
            text-align:center;
        }
        h2 {
            color:#00ff88;
            margin-bottom:20px;
        }
        .msg {
            padding:12px;
            border-radius:8px;
            margin-bottom:15px;
            font-size:15px;
        }
        .error {
            background:#3d1f1f;
            color:#ff6b6b;
            border:1px solid #ff6b6b;
        }
        .success {
            background:#1f3d2a;
            color:#00ff88;
            border:1px solid #00ff88;
        }
        a {
            display:inline-block;
            margin-top:15px;
            padding:10px 16px;
            background:#00ff88;
            color:#111;
            border-radius:6px;
            text-decoration:none;
            font-weight:bold;
            transition:0.3s;
        }
        a:hover {
            background:#00cc6a;
        }
    </style>
</head>
<body>
    <div class="box"> <!-- wadah utama hasil registrasi -->
        <h2>Status Registrasi</h2> 
        <div class="msg <?= $type ?>"><?= htmlspecialchars($msg) ?></div> <!-- tampilkan pesan hasil -->
        <a href="registrasi.php">⬅️ Kembali</a> <!-- tombol balik ke form registrasi -->
        <a href="login.php">🔑 Login</a> <!-- tombol menuju halaman login -->
    </div>
</body>
</html>
