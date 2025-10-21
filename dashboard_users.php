<?php
session_start();

try {
    $db = new PDO("sqlite:listfilm.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Simpan rating kalau login
    if (isset($_POST['film_id'], $_POST['rating'], $_POST['komentar'])) {
        if (!isset($_SESSION['username'])) {
            header("Location: login.php?error=Silakan login untuk memberi rating.");
            exit;
        }

        $film_id  = (int) $_POST['film_id'];
        $rating   = (int) $_POST['rating'];
        $komentar = trim($_POST['komentar']);
        $username = $_SESSION['username'];

        $stmt = $db->prepare("INSERT INTO ratings (film_id, username, rating, komentar) 
                              VALUES (:film_id, :username, :rating, :komentar)");
        $stmt->execute([
            ':film_id'  => $film_id,
            ':username' => $username,
            ':rating'   => $rating,
            ':komentar' => $komentar
        ]);
    }


// Filter genre (Harus didefinisikan di awal!)
$filter_genre = $_GET['genre'] ?? ''; // Mengambil nilai genre dari URL

// Ambil data film
$query = "SELECT * FROM films";
$params = [];
if ($filter_genre) {
    $query .= " WHERE genre = :genre"; // Tambahkan filter WHERE
    $params[':genre'] = $filter_genre;
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$films = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ---


    // kumpulkan semua rating
    $ratings = [];
    $stmt = $db->query("SELECT * FROM ratings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ratings[$row['film_id']][] = $row;
    }

   //rata rata rating
    $avgRatings = [];
    foreach ($films as $f) {
        if (!empty($ratings[$f['id']])) {
            $sum = array_sum(array_column($ratings[$f['id']], 'rating'));
            $avgRatings[$f['id']] = $sum / count($ratings[$f['id']]);
        } else {
            $avgRatings[$f['id']] = 0;
        }
    }
    //sort rating tertinggi
    usort($films, function($a, $b) use ($avgRatings) {
        return $avgRatings[$b['id']] <=> $avgRatings[$a['id']];
    });
    //genre
    $genreList = $db->query("SELECT DISTINCT genre FROM films ORDER BY genre ASC")->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Film</title>
</head>
<body>

<div class="topbar">
    <div>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <a href="dashboard_admin.php">Dashboard Admin</a>
        <?php endif; ?>
    </div>
    <div style="display:flex; align-items:center; gap:10px;">
        <!-- Filter Genre -->
         <form method="get" style="margin:0; display:flex; align-items:center; gap:5px;">
            <label style="font-weight:bold; color:#f0f0f0;">Genre:</label>
            <select name="genre" onchange="this.form.submit()">
            <option value="" <?php echo (!$filter_genre) ? 'selected' : ''; ?>>Semua Genre</option>
    
                <?php foreach ($genreList as $genre): ?>
                    <option 
                        value="<?php echo htmlspecialchars($genre); ?>" 
                        <?php echo ($filter_genre === $genre) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($genre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <!-- User Login/Logout -->
        <?php if (isset($_SESSION['username'])): ?>
            <span>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" style="color:red;margin-left:10px;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</div>

<h2>Daftar Film</h2>

<?php if ($films): ?>
<div class="film-grid">
    <?php foreach ($films as $film): ?>
    <div class="film-card">
        <?php 
            $safeName = strtolower(str_replace(" ", "_", $film['judul']));
            $posterPath = "uploads/" . $safeName . ".jpg";
            if (file_exists($posterPath)) {
                echo "<img src='".$posterPath."' alt='".htmlspecialchars($film['judul'])."'>";
            } else {
                echo "<div style='height:320px; display:flex; align-items:center; justify-content:center; background:#555; color:#f0f0f0;'>[Poster Tidak Ada]</div>";
            }
        ?>
        <div class="film-content">
            <h3><a href="detail_film.php?id=<?php echo $film['id']; ?>"><?php echo htmlspecialchars($film['judul']); ?></a></h3>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($film['genre']); ?></p>
            <p><strong>Durasi:</strong> <?php echo htmlspecialchars($film['durasi']); ?> menit</p>
            <p><strong>Tayang Perdana:</strong> <?php echo htmlspecialchars($film['jadwal']); ?></p>

            <!-- Rating Playstore Style -->
            <?php if (isset($_SESSION['username'])): ?>
            <form method="post" class="rating-form">
                <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                <input type="hidden" name="rating" value="0" class="rating-input">
                <div class="star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star" data-value="<?php echo $i; ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <textarea name="komentar" required placeholder="Komentar..."></textarea>
                <button type="submit">Kirim</button>
            </form>
            <?php endif; ?>

            <!-- Rata-rata rating -->
            <?php 
            if ($avgRatings[$film['id']] > 0) {
                echo "<div class='rating-box'>⭐ " . round($avgRatings[$film['id']],1) . "/5</div>";
            } else {
                echo "<i>Belum ada rating</i>";
            }
            ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p style="text-align:center; font-size:18px;">Belum ada film.</p>
<?php endif; ?>

<!-- Script Bintang Klik -->
<script>
document.querySelectorAll('.rating-form').forEach(form => {
    const stars = form.querySelectorAll('.star');
    const input = form.querySelector('.rating-input');
    stars.forEach(star => {
        star.addEventListener('click', () => {
            let value = star.getAttribute('data-value');
            input.value = value;
            stars.forEach(s => s.classList.remove('active'));
            for (let i = 0; i < value; i++) stars[i].classList.add('active');
        });
        star.addEventListener('mouseover', () => {
            stars.forEach((s, idx) => {
                s.classList.toggle('hover', idx < star.getAttribute('data-value'));
            });
        });
        star.addEventListener('mouseout', () => {
            stars.forEach(s => s.classList.remove('hover'));
        });
    });
});
</script>
<style>
body { font-family: Arial, sans-serif; margin:0; background:#2c2c2c; color:#f0f0f0; }
.topbar { background:#1e1e1e; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #444; }
.topbar a, .topbar span { color:#f0f0f0; margin-right:15px; text-decoration:none; font-weight:bold; }
.topbar a:hover { color:#ff9800; }
h2 { text-align:center; margin:20px 0; font-size:28px; color:#ff9800; }
.film-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:20px; width:95%; margin:20px auto; }
.film-card { background:#3a3a3a; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.6); overflow:hidden; transition:transform 0.3s; display:flex; flex-direction:column; }
.film-card:hover { transform:translateY(-5px); }
.film-card img { width:100%; height:320px; object-fit:cover; }
.film-content { padding:12px; flex:1; display:flex; flex-direction:column; justify-content:space-between; }
.film-content h3 { font-size:18px; margin:0 0 8px 0; color:#ff9800; }
.film-content h3 a { text-decoration:none; color:#ff9800; }
.film-content p { margin:3px 0; font-size:14px; color:#ddd; }
.rating-box { margin-top:8px; font-weight:bold; color:gold; text-align:center; }

.star-rating { display:flex; justify-content:center; margin:10px 0; }
.star { font-size:28px; color:#555; cursor:pointer; transition:color 0.2s; text-shadow:0 1px 2px rgba(0,0,0,0.5); }
.star.active { color: gold; }
.star.hover { color: #FFC107; }

.rating-form textarea { width:100%; padding:6px; border-radius:5px; border:none; background:#2c2c2c; color:#f0f0f0; margin-bottom:5px; }
.rating-form button { width:100%; padding:8px; border:none; border-radius:5px; background:#ff9800; color:#111; cursor:pointer; font-weight:bold; }
.rating-form button:hover { background:#e68900; }
i { color:#bbb; font-size:14px; text-align:center; display:block; }
</style>
</body>
</html>
