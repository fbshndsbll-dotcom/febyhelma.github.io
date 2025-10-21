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

// Ambil data film berdasarkan ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM films WHERE id=?");
    $stmt->execute([$id]);
    $film = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$film) die("Film tidak ditemukan.");
} else {
    header("Location: dashboard_admin.php");
    exit;
}

$sinopsis = $film['sinopsis'];

// Tentukan nama file lama untuk hapus
$safeNameLama = strtolower(str_replace(" ", "_", $film['judul']));

// Cek poster saat ini
$posterPath = '';
$exts = ['jpg','jpeg','png','gif'];
foreach ($exts as $ext) {
    $path = "uploads/{$safeNameLama}.{$ext}";
    if (file_exists($path)) {
        $posterPath = $path;
        break;
    }
}

// Cek trailer saat ini
$trailerPath = '';
$trailerFile = "trailers/{$safeNameLama}.mp4";
if (file_exists($trailerFile)) {
    $trailerPath = $trailerFile;
}

// Proses update film
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $genre = trim($_POST['genre']);
    $durasi = (int)$_POST['durasi'];
    $jadwal = trim($_POST['jadwal']);
    $sinopsis = trim($_POST['sinopsis']);
    $safeNameBaru = strtolower(str_replace(" ", "_", $judul));

    // Update data text di database
    $stmt = $db->prepare("UPDATE films SET judul=?, genre=?, durasi=?, jadwal=?, sinopsis=? WHERE id=?");
    $stmt->execute([$judul, $genre, $durasi, $jadwal, $sinopsis, $id]);

    // --- Upload Poster ---
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
        $posterBaru = "uploads/{$safeNameBaru}.{$ext}";
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);

        // Hapus poster lama
        foreach($exts as $e){
            $old = "uploads/{$safeNameLama}.{$e}";
            if(file_exists($old)) unlink($old);
        }

        move_uploaded_file($_FILES['poster']['tmp_name'], $posterBaru);

        // Update path poster di database
        $stmt = $db->prepare("UPDATE films SET poster=? WHERE id=?");
        $stmt->execute([$posterBaru, $id]);

        $posterPath = $posterBaru;
    }

    // --- Upload Trailer ---
    if (isset($_FILES['trailer']) && $_FILES['trailer']['error'] === UPLOAD_ERR_OK) {
        $trailerBaru = "trailers/{$safeNameBaru}.mp4";
        if (!is_dir('trailers')) mkdir('trailers', 0777, true);

        $oldTrailer = "trailers/{$safeNameLama}.mp4";
        if (file_exists($oldTrailer)) unlink($oldTrailer);

        move_uploaded_file($_FILES['trailer']['tmp_name'], $trailerBaru);

        // Update path trailer di database
        $stmt = $db->prepare("UPDATE films SET trailer=? WHERE id=?");
        $stmt->execute([$trailerBaru, $id]);

        $trailerPath = $trailerBaru;
    }

    header("Location: dashboard_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Film</title>
<style>
body { font-family: Arial; background:#121212; color:#e0e0e0; margin:20px; }
h2 { text-align:center; color:#f39c12; }
form { background:#1e1e1e; padding:20px; border-radius:12px; max-width:700px; margin:auto; }
label { font-weight:bold; display:block; margin-top:10px; color:#b0b0b0; }
input, textarea { width:100%; padding:10px; margin:6px 0 15px 0; border-radius:6px; border:1px solid #333; background:#2a2a2a; color:#fff; }
textarea { height:150px; resize:vertical; }
button { background:#6200ee; color:#fff; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#7c4dff; }
a { display:inline-block; margin-top:20px; color:#03dac6; text-decoration:none; font-weight:bold; }
a:hover { text-decoration:underline; }
.media-container { display:flex; gap:20px; flex-wrap:wrap; }
.media-container img, .media-container video { border-radius:8px; border:2px solid #333; }
</style>
</head>
<body>
<h2>Edit Film</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Judul:</label>
    <input type="text" name="judul" value="<?php echo htmlspecialchars($film['judul']); ?>" required>
    <label>Genre:</label>
    <input type="text" name="genre" value="<?php echo htmlspecialchars($film['genre']); ?>" required>
    <label>Durasi (menit):</label>
    <input type="number" name="durasi" value="<?php echo htmlspecialchars($film['durasi']); ?>" required>
    <label>Tayang Perdana:</label>
    <input type="text" name="jadwal" value="<?php echo htmlspecialchars($film['jadwal']); ?>" required>
    <label>Deskripsi:</label>
    <textarea name="sinopsis"><?php echo htmlspecialchars($sinopsis); ?></textarea>

    <label>Poster & Trailer Saat Ini:</label>
    <div class="media-container">
        <div>
            <?php if($posterPath): ?>
                <img src="<?php echo $posterPath . '?v=' . time(); ?>" alt="Poster" width="200"> 
            <?php else: ?>
                <span style="color:red">[Belum ada poster]</span>
            <?php endif; ?>
        </div>
        <div>
            <?php if($trailerPath): ?>
                <video width="320" controls>
                    <source src="<?php echo $trailerPath . '?v=' . time(); ?>" type="video/mp4">
                </video>
            <?php else: ?>
                <span style="color:red">[Belum ada trailer]</span>
            <?php endif; ?>
        </div>
    </div>

    <label>Upload Poster Baru:</label>
    <input type="file" name="poster" accept="image/*">
    <label>Upload Trailer Baru:</label>
    <input type="file" name="trailer" accept="video/mp4">
    <button type="submit">Update Film</button>
</form>

<a href="dashboard_admin.php">Kembali ke Dashboard</a>
</body>
</html>
