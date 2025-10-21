<?php
session_start();

//try {
    $db = new PDO("sqlite:listfilm.db");
    //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['film_id'], $_POST['rating'], $_POST['komentar'])) { //beri rating(sebelum login)
        if (!isset($_SESSION['username'])) {
            header("Location: login.php?error=Silakan login untuk memberi rating.");
            exit;
        }

        $film_id  = (int) $_POST['film_id'];
        $rating   = (int) $_POST['rating'];
        $komentar = $_POST['komentar'];
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

    $filter_genre = $_GET['genre'] ?? ''; //genre

    $query = "SELECT * FROM films"; //data film
    $params = [];//wadah buat data yang akan dimasukin ke query,jadi intinya params itu isinya value dari data yg diambil dari tabel
    if ($filter_genre) {//filter genre
        $query .= " WHERE genre = :genre";
        $params[':genre'] = $filter_genre;//isi
    }
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ratings = [];//rating
    $stmt = $db->query("SELECT * FROM ratings");//ambil rating
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //Setiap baris ($row) dicek, lalu dimasukkan ke $ratings sesuai film_id
        $ratings[$row['film_id']][] = $row;
    }

    $avgRatings = [];//rata rata rating dari  setiap film
    foreach ($films as $f) {
        if (!empty($ratings[$f['id']])) { // Cek kalau film ini punya rating
            $sum = array_sum(array_column($ratings[$f['id']], 'rating'));// Ambil semua nilai rating dari film, lalu jumlahkan
            $avgRatings[$f['id']] = $sum / count($ratings[$f['id']]);// Hitung rata-rata: jumlah dibagi banyaknya rating
        } else {
            $avgRatings[$f['id']] = 0; // Kalau belum ada rating, rata-rata = 0
        }
    }
    usort($films, function($a, $b) use ($avgRatings) {//usort mengurutkan array menggunakan fungsi perbandingan yang ditentukan pengguna,$a dan $b adalah dua elemen film yg dibandingkan
        return $avgRatings[$b['id']] <=> $avgRatings[$a['id']];// mengembalikan hasil perbandingan antara rating B dan rating A karena B dibandingkan dengan A, ini menghasilkan urutan MENURUN (Descending).
                                                               // Film dengan rating tertinggi akan diletakkan di awal.
    });

    
    $genreList = $db->query("SELECT DISTINCT genre FROM films ORDER BY genre ASC")->fetchAll(PDO::FETCH_COLUMN);//mengambil semua nilai dari kolom genre,jika ada film yg memiliki gnre yg sama maka genre akan muncul hanya sekali

//} catch (Exception $e) {
    //die("DB Error: " . $e->getMessage());
//}
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
        <form method="get" style="margin:0; display:flex; align-items:center; gap:5px;">
            <label style="font-weight:bold; color:#f0f0f0;">Genre:</label>
            <select name="genre" onchange="this.form.submit()"> <!-- filter genre , membuat dropdown tanpa menggunakan opsi otomatis submit ketika user memilih genre tertentu-->
            <option value="" <?php echo (!$filter_genre) ? 'selected' : ''; ?>>Semua Genre</option> <!-- membuat opsi dropdown dari daftar genre yang diambil dari database -->
                <?php foreach ($genreList as $genre): ?>
                    <option 
                        value="<?php echo htmlspecialchars($genre); ?>"
                        <?php echo ($filter_genre === $genre) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($genre); ?>
                        <!-- Menetapkan genre yang dikirim ketika opsi genre seperti horor dipilih.
                        mengecek apakah genre sama dengan yang difilter -->
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (isset($_SESSION['username'])): ?> <!-- User Login/Logout -->
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
            $safeName = strtolower(str_replace(" ", "_", $film['judul'])); //str_replace Ubah spasi menjadi underscore(_) & semua huruf menjadi lowercase(huruf kecil) untuk nama file
            $posterPath = "uploads/" . $safeName . ".jpg"; //menyimpan poster ke dalam folder uploads/, menambahkan ekstensi .jpg ke nama file agar menjadi nama file gambar
            if (file_exists($posterPath)) { //mengecek apakah file poster benar-benar ada di server.
                echo "<img src='".$posterPath."' alt='".htmlspecialchars($film['judul'])."'>"; //menampilkan gambar
            } else {
                echo "<div style='height:320px; display:flex; align-items:center; justify-content:center; background:#555; color:#f0f0f0;'>[Poster Tidak Ada]</div>";
            }
        ?>
        <div class="film-content">
            <h3><a href="detail_film.php?id=<?php echo $film['id']; ?>"><?php echo htmlspecialchars($film['judul']); ?></a></h3>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($film['genre']); ?></p>
            <p><strong>Durasi:</strong> <?php echo htmlspecialchars($film['durasi']); ?> menit</p>
            <p><strong>Tayang Perdana:</strong> <?php echo htmlspecialchars($film['jadwal']); ?></p>

            <?php if (isset($_SESSION['username'])): ?>
                <form method="post">
                    <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                    <select name="rating" required>
                        <option value="">-- Pilih --</option>
                        <option value="1">1⭐</option>
                        <option value="2">2⭐</option>
                        <option value="3">3⭐</option>
                        <option value="4">4⭐</option>
                        <option value="5">5⭐</option>
                    </select>
                    <textarea name="komentar" required placeholder="Komentar..."></textarea>
                    <button type="submit">Kirim</button> <!-- mengirim rating -->
                </form>
            <?php endif; ?>

            <?php 
            if ($avgRatings[$film['id']] > 0) { // Mengecek apakah film ini sudah punya rating 
                echo "<div class='rating-box'>⭐ " . round($avgRatings[$film['id']],1) . "/5</div>";//tampilkan bintang dan nilai rata-rata rating
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



<style>
    body { 
        font-family: Arial, sans-serif; 
        margin:0; 
        background:#2c2c2c;
        color:#f0f0f0;
    }
    .topbar { 
        background:#1e1e1e; 
        padding:12px 20px; 
        display:flex; 
        justify-content:space-between; 
        align-items:center;
        border-bottom:1px solid #444;
    }
    .topbar a, .topbar span { 
        color:#f0f0f0; 
        margin-right:15px; 
        text-decoration:none; 
        font-weight:bold;
    }
    .topbar a:hover { color:#ff9800; }

    h2 { 
        text-align:center; 
        margin:20px 0; 
        font-size:28px; 
        color:#ff9800; 
    }

    .film-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap:20px;
        width:95%;
        margin:20px auto;
    }
    .film-card {
        background:#3a3a3a;
        border-radius:10px;
        box-shadow:0 2px 8px rgba(0,0,0,0.6);
        overflow:hidden;
        transition:transform 0.3s;
        display:flex;
        flex-direction:column;
    }
    .film-card:hover { transform:translateY(-5px); }
    .film-card img {
        width:100%;
        height:320px;
        object-fit:cover;
    }
    .film-content {
        padding:12px;
        flex:1;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
    }
    .film-content h3 {
        font-size:18px;
        margin:0 0 8px 0;
        color:#ff9800;
    }
    .film-content h3 a {
        text-decoration:none;
        color:#ff9800;
    }
    .film-content p {
        margin:3px 0;
        font-size:14px;
        color:#ddd;
    }
    .rating-box {
        margin-top:8px; 
        font-weight:bold; 
        color:#ff9800;
    }
    form {
        margin-top:10px;
    }
    select, textarea {
        width:100%;
        padding:6px;
        border:1px solid #555;
        border-radius:5px;
        background:#2c2c2c;
        color:#f0f0f0;
        margin-top:5px;
        font-size:14px;
    }
    textarea { height:50px; resize:vertical; }
    button[type="submit"] {
        margin-top:5px;
        background:#4CAF50;
        border:none;
        color:white;
        padding:8px 12px;
        border-radius:5px;
        cursor:pointer;
        font-weight:bold;
        width:100%;
    }
    button[type="submit"]:hover { background:#45a049; }
    i { color:#bbb; font-size:14px; }
</style>

</body>
</html>

