<?php
session_start();
include 'koneksi.php';

$id = (int)($_GET['id'] ?? 0); //mengambil data film
$stmt = $koneksi->prepare("SELECT * FROM films WHERE id=?");
$stmt->execute([$id]);
$film = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$film) { //jika tdk ditemukan
    die("Film tidak ditemukan!");
}

$sinopsis = $film['sinopsis']; // Ambil sinopsis dari database

//Submit ulasan user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && isset($_SESSION['username']) && !isset($_POST['update_sinopsis'])) { //Mengecek apakah form ulasan dikirim (POST) dan pengguna sudah login. Mengambil data rating, komentar, dan username.
    $rating   = (int) $_POST['rating'];
    $komentar = trim($_POST['komentar']);
    $username = $_SESSION['username'];

    $stmt = $koneksi->prepare("INSERT INTO ratings (film_id, username, rating, komentar) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $username, $rating, $komentar]);
    header("Location: detail_film.php?id=$id");
    exit;
}

//Ambil rata-rata rating dan menampilkan rating dan ulasan
$stmt = $koneksi->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE film_id=?");
$stmt->execute([$id]);
$avg = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'] ?? 0;

//Ambil semua ulasan atau rata rata rating
$stmt = $koneksi->prepare("SELECT * FROM ratings WHERE film_id=? ORDER BY id DESC");
$stmt->execute([$id]);
$ulasan = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Poster
$safeName = strtolower(str_replace(" ", "_", $film['judul']));
$posterPath = '';
$exts = ['jpg','jpeg','png','gif'];
foreach($exts as $ext){
    $path = "uploads/{$safeName}.{$ext}";
    if(file_exists($path)){
        $posterPath = $path;
        break;
    }
}

//Trailer
$trailerPath = '';
$trailerFile = "trailers/{$safeName}.mp4";
if(file_exists($trailerFile)) {
    $trailerPath = $trailerFile;
}

//Tentukan tautan kembali
$backLink = "index.php";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $backLink = "dashboard_admin.php";
    } else {
        $backLink = "dashboard_users.php";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Film</title>
</head>
<body>
<div class="topbar">
    <div><a href="<?php echo $backLink; ?>">⬅ Kembali</a></div>
    <div>
        <?php if (isset($_SESSION['username'])): ?>
            <span>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" style="color:#e74c3c;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2><?php echo htmlspecialchars($film['judul']); ?></h2>

    <div class="media-container">
        <!-- Poster -->
        <div>
            <?php if($posterPath): ?>
                <img src="<?php echo $posterPath; ?>" alt="<?php echo htmlspecialchars($film['judul']); ?>" class="poster">
            <?php else: ?>
                <div class="no-poster">Poster tidak tersedia</div>
            <?php endif; ?>
        </div>

        <!-- Trailer -->
        <div>
            <?php if(!empty($trailerPath)): ?>
                <video controls autoplay muted loop poster="<?php echo $posterPath; ?>">
                    <source src="<?php echo $trailerPath; ?>" type="video/mp4">
                    Browser Anda tidak mendukung pemutaran video.
                </video>
            <?php else: ?>
                <div class="no-trailer">Trailer tidak tersedia</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="info">
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($film['genre']); ?></p>
        <p><strong>Durasi:</strong> <?php echo htmlspecialchars($film['durasi']); ?> menit</p>
        <p><strong>Tayang Perdana:</strong> <?php echo htmlspecialchars($film['jadwal']); ?></p>
        <p><strong>Rating:</strong> <?php echo round($avg,1); ?>/5 ⭐</p>
    </div>

    <h3>Sinopsis</h3>
    <?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
        <form method="post">
            <textarea name="sinopsis"><?php echo htmlspecialchars($sinopsis); ?></textarea><br>
            <!-- <button type="submit" name="update_sinopsis"> Simpan Deskripsi</button> -->
        </form>
    <?php else: ?>
        <p><?php echo !empty($sinopsis) ? nl2br(htmlspecialchars($sinopsis)) : '<i>Belum ada deskripsi</i>'; ?></p>
    <?php endif; ?>

    <h3>Ulasan</h3>
    <?php foreach($ulasan as $u): ?>
        <div class="box">
            <strong><?php echo htmlspecialchars($u['username']); ?></strong>
            (<?php echo str_repeat("⭐",$u['rating']); ?>)<br>  <!-- tampilkan bintang sebanyak nilai rating user -->
            <?php echo nl2br(htmlspecialchars($u['komentar'])); ?>
            <?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
                <br>
                <a href="hapus_komen.php?id=<?php echo $u['id']; ?>&film=<?php echo $film['id']; ?>" 
                   onclick="return confirm('Yakin hapus komentar ini?')">🗑️ Hapus</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if(isset($_SESSION['username'])): ?>
        <h3>Tulis Ulasan</h3>
        <form method="post">
            <label>Rating:</label>
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>
            <textarea name="komentar" placeholder="Tulis komentar..." required></textarea><br>
            <button type="submit">Kirim</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Login</a> untuk memberi komentar.</p>
    <?php endif; ?>

    <a href="<?php echo $backLink; ?>" class="back-btn">⬅ Kembali ke Dashboard</a>
</div>
</body>

<style>
body { font-family: Arial, sans-serif; margin:0; background:#111; color:#eee; }
.container { width:90%; max-width:1200px; margin:auto; padding:20px; }

.topbar { background:#222; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; }
.topbar a { color:#fff; margin-right:15px; text-decoration:none; font-weight:bold; }
.topbar a:hover { color:#f39c12; }

h2 { color:#f39c12; margin-top:20px; }

.poster { max-width:65%; border-radius:8px; }
video {border-radius:20px; display:block; width:145%; height:450px;margin-left: -200px;}

.no-poster,
.no-trailer { width:100%; height:300px; background:#333; color:#777; display:flex; align-items:center; justify-content:center; border-radius:8px; }
.no-trailer { background:#222; }

.info { margin:15px 0; font-size:15px; }
.info strong { color:#f39c12; }

textarea { width:100%; height:120px; border-radius:6px; border:none; padding:8px; margin:6px 0; resize:vertical; }
select, button { padding:8px; border:none; border-radius:6px; margin:6px 0; }
button { background:#f39c12; color:#111; font-weight:bold; cursor:pointer; }
button:hover { background:#e67e22; }

.box { background:#1c1c1c; padding:12px; border-radius:8px; margin:10px 0; }
.box strong { color:#f39c12; }
.box a { color:#e74c3c; text-decoration:none; font-size:14px; }
.box a:hover { text-decoration:underline; }

.back-btn { display:inline-block; margin-top:15px; background:#3498db; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none; }
.back-btn:hover { background:#2980b9; }

.media-container { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px; }
.media-container > div { flex:1 1 300px; }

@media(max-width:700px){ .media-container { flex-direction:column; } }

/* Star rating Play Store style */
.star-rating { direction: rtl; display: inline-flex; font-size: 24px; margin:6px 0; }
.star-rating input { display:none; }
.star-rating label { color:#555; cursor:pointer; transition:color 0.2s; }
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label { color:#f39c12; }
</style>
</html>
