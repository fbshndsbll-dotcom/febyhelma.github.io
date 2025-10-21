<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

try {
    $db = new PDO("sqlite:listfilm.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $genreList = $db->query("SELECT DISTINCT genre FROM films ORDER BY genre ASC")->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Film</title>
    <style>
        body { font-family: Arial; background:#111; color:#eee; margin:0; }
        .container { width:90%; max-width:500px; margin:50px auto; padding:20px; background:#222; border-radius:10px; }
        h2 { color:#f39c12; text-align:center; margin-bottom:20px; }
        form input, form select, form button { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:none; }
        form input[type="file"] { background:#333; color:#eee; }
        form button { background:#f39c12; color:#111; font-weight:bold; cursor:pointer; }
        form button:hover { background:#e67e22; }
        a { color:#f39c12; text-decoration:none; display:inline-block; margin-top:10px; }
        a:hover { text-decoration:underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>➕ Tambah Film Baru</h2>
        <form method="POST" action="action_film.php" enctype="multipart/form-data">
            <input type="text" name="judul" placeholder="Judul Film" required>

            <select name="genre" required>
                <option value="">-- Pilih Genre --</option>
                <option value="Horor">Horor</option>
                <option value="Komedi">Komedi</option>
                <option value="Romance">Romance</option>
                <option value="Drama">Drama</option>
            </select>

            <input type="number" name="durasi" placeholder="Durasi (menit)" required>
            <input type="text" name="jadwal" placeholder="Tayang Perdana" required>
            <input type="text" name="sinopsis" placeholder="Deskripsi" required>
            <input type="file" name="poster" accept="image/*" required>
            <label>Upload Trailer Baru:</label>
            <input type="file" name="trailer" accept="video/mp4">
            <button type="submit" name="tambah">Tambah Film</button>
        </form>
        <a href="dashboard_admin.php">← Kembali ke Dashboard</a>
    </div>
</body>
</html>