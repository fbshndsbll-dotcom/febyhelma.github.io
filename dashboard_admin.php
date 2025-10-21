<?php
session_start(); // Mulai sesi (untuk cek status login)

// Cek: Sudah login BELUM? Role-nya 'admin' BUKAN?
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php"); // Kalau bukan admin/belum login, usir ke halaman utama
    exit; // Stop proses di sini
}

try {
    $db = new PDO("sqlite:listfilm.db"); // Sambungkan ke database SQLite
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Aktifkan laporan error database
} catch (Exception $e) {
    die("DB Error: " . $e->getMessage()); // Kalau gagal koneksi, tampilkan pesan error
}

$no = 1; // Variabel untuk nomor urut tabel
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <style>
/* Style CSS tidak perlu dikomentari sesuai permintaan */
        body { font-family: Arial, sans-serif; margin:0; background:#111; color:#eee; }
        .container { width:90%; margin:auto; padding:20px; }

        /* Topbar */
        .topbar { background:#222; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; }
        .topbar a { color:#fff; margin-right:15px; text-decoration:none; font-weight:bold; }
        .topbar a:hover { color:#f39c12; }
        .topbar span { color:#f39c12; }

        h2 { margin-top:20px; color:#f39c12; }

        /* Table */
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; text-align:center; border-bottom:1px solid #444; }
        th { background:#222; color:#f39c12; }
        tr:hover { background:#1c1c1c; }

        img { max-width:80px; border-radius:6px; margin-bottom:5px; }
        .judul { margin-top:5px; font-weight:bold; color:#fff; }
        a { text-decoration:none; color:#3498db; }
        a:hover { color:#f39c12; }

        .aksi a { margin:0 5px; }
        .aksi a:first-child { color:#2ecc71; } /* Edit hijau */
        .aksi a:last-child { color:#e74c3c; }  /* Hapus merah */
        .tambah-film { margin-top:15px; display:inline-block; background:#f39c12; color:#111; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold; }
        .tambah-film:hover { background:#e67e22; }
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <a href="dashboard_admin.php">🏠 Dashboard Admin</a> </div>
        <div>
            <span>Halo, <?php echo $_SESSION['username']." (".$_SESSION['role'].")"; ?></span>
            <a href="logout.php">Logout</a> </div>
    </div>

    <div class="container">
        <h2>📋 Daftar Film & Ulasan</h2>

        <a href="tambah_film.php" class="tambah-film">➕ Tambah Film</a>

        <table>
            <tr>
                <th>No</th>
                <th>Poster + Judul</th>
                <th>Genre</th>
                <th>Durasi</th>
                <th>Tayang Perdana</th>
                <th>Rating</th>
                <th>Komentar</th>
                <th>Aksi</th>
            </tr>
            <?php
            // Ambil semua data film dari database
            $films = $db->query("SELECT * FROM films ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($films as $film) { // Loop untuk menampilkan setiap film dalam baris tabel
                // Hitung rating rata-rata
                $stmt = $db->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE film_id = ?"); // Siapkan query hitung rata-rata rating
                $stmt->execute([$film['id']]); // Jalankan query untuk film ini
                $avg = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating']; // Ambil hasil rata-rata
                $avg_show = $avg ? round($avg,1)."/5 ⭐" : "<i>Belum ada</i>"; // Format rata-rata (misal: 4.5/5 ⭐)

                // Hitung jumlah komentar
                $stmt2 = $db->prepare("SELECT COUNT(*) as jumlah FROM ratings WHERE film_id = ?"); // Siapkan query hitung jumlah komentar
                $stmt2->execute([$film['id']]); // Jalankan query
                $jml_komen = $stmt2->fetch(PDO::FETCH_ASSOC)['jumlah']; // Ambil jumlah komentar

                // Tentukan path file poster berdasarkan judul film (sama seperti saat upload)
                $safeName = strtolower(str_replace(" ", "_", $film['judul'])); // Format judul jadi nama file aman
                $posterPath = "uploads/" . $safeName . ".jpg"; // Path poster

                echo "<tr>
                    <td>".$no++."</td>
                    <td>
                        <a href='detail_film.php?id=".$film['id']."'>"; // Tautan ke halaman detail film
                
                if (file_exists($posterPath)) { // Cek apakah file poster ada di folder 'uploads/'
                    echo "<img src='".$posterPath."' alt='".htmlspecialchars($film['judul'])."'>"; // Tampilkan poster
                } else {
                    echo "<span style='color:red'>[Tidak ada poster]</span>"; // Jika poster tidak ditemukan
                }

                echo "<div class='judul'>".htmlspecialchars($film['judul'])."</div>
                        </a>
                    </td>
                    <td>".htmlspecialchars($film['genre'])."</td> 
                    <td>".htmlspecialchars($film['durasi'])." menit</td> 
                    <td>".htmlspecialchars($film['jadwal'])."</td> 
                    <td>".$avg_show."</td> 
                    <td>";
                if ($jml_komen > 0) { // Jika ada komentar
                    echo "<a href='detail_film.php?id=".$film['id']."'>".$jml_komen." komentar</a>"; // Tampilkan jumlah komentar (link ke detail)
                } else {
                    echo "<i>Belum ada</i>"; // Jika belum ada komentar
                }
                echo "</td>
                    <td class='aksi'>
                        <a href='update_admin.php?id=".$film['id']."'>✏️ Edit</a> | 
                        <a href='delete_admin.php?id=".$film['id']."' onclick=\"return confirm('Yakin hapus film ini?')\">⛔ Hapus</a> 
                    </td>
                </tr>";
            } // Tutup loop foreach
            ?>
        </table>
    </div>
</body>
</html>