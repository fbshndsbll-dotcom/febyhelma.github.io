<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

try {
    $db = new PDO("sqlite:listfilm.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    try {
        $judul = trim($_POST['judul']);
        $genre = trim($_POST['genre']);
        $durasi = (int) $_POST['durasi'];
        $jadwal = trim($_POST['jadwal']);
        $sinopsis = trim($_POST['sinopsis']);
        $posterPath = '';
        $trailerPath = '';

        $safeName = strtolower(str_replace(" ", "_", $judul));

        // --- Upload poster ---
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
            $posterPath = $uploadDir . $safeName . '.' . $ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], $posterPath);
        }

        // --- Upload trailer ---
        if (isset($_FILES['trailer']) && $_FILES['trailer']['error'] === UPLOAD_ERR_OK) {
            $trailerDir = 'trailers/';
            if (!is_dir($trailerDir)) mkdir($trailerDir, 0777, true);
            $trailerPath = $trailerDir . $safeName . ".mp4";
            move_uploaded_file($_FILES['trailer']['tmp_name'], $trailerPath);
        }

        // --- Simpan ke database ---
        $stmt = $db->prepare("INSERT INTO films (judul, genre, durasi, jadwal, sinopsis, poster, trailer) 
                              VALUES (:judul, :genre, :durasi, :jadwal, :sinopsis, :poster, :trailer)");
        $stmt->execute([
            ':judul' => $judul,
            ':genre' => $genre,
            ':durasi' => $durasi,
            ':jadwal' => $jadwal,
            ':sinopsis' => $sinopsis,
            ':poster' => $posterPath,
            ':trailer' => $trailerPath
        ]);

        $msg = "✅ Film berhasil ditambahkan!";
    } catch (Exception $e) {
        $msg = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Film</title>
<style>
body { font-family: Arial; background:#121212; color:#f5f5f5; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
.card { background:#1e1e1e; padding:30px; border-radius:12px; box-shadow:0 0 15px rgba(0,0,0,0.6); text-align:center; max-width:400px; }
h2 { margin-bottom:20px; }
.success { color:#4caf50; font-weight:bold; }
.error { color:#f44336; font-weight:bold; }
a { display:inline-block; margin-top:20px; padding:10px 16px; background:#1976d2; color:#fff; border-radius:6px; text-decoration:none; }
a:hover { background:#1565c0; }
input, textarea { width:100%; padding:10px; margin:6px 0 15px 0; border-radius:6px; border:1px solid #333; background:#2a2a2a; color:#fff; }
button { padding:10px 16px; background:#4caf50; border:none; border-radius:6px; color:#fff; cursor:pointer; font-weight:bold; }
button:hover { background:#43a047; }
</style>
</head>
<body>
<div class="card">
    <h2>Tambah Film</h2>

    <?php if(isset($msg)): ?>
        <p class="<?php echo (strpos($msg,'✅')!==false) ? 'success':'error'; ?>">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <?php if (!isset($msg) || strpos($msg, '✅') === false): ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="judul" placeholder="Judul Film" required>
        <input type="text" name="genre" placeholder="Genre" required>
        <input type="number" name="durasi" placeholder="Durasi (menit)" required>
        <input type="text" name="jadwal" placeholder="Tayang Perdana" required>
        <textarea name="sinopsis" placeholder="Sinopsis"></textarea>
        <input type="file" name="poster" accept="image/*">
        <input type="file" name="trailer" accept="video/mp4">
        <button type="submit" name="tambah">Tambah Film</button>
    </form>
    <?php endif; ?>

    <a href="dashboard_admin.php">⬅ Kembali ke Dashboard</a>
</div>
</body>
</html>
