<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DELETE FILM</title>
</head>
<body>
</body>
</html>

<?php
session_start();

$db = new PDO('sqlite:listfilm.db'); // Sambungkan ke database SQLite
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Aktifkan laporan error database

$id = $_GET['id'] ?? null; // Ambil ID film dari URL 
$message = ''; // variabel untuk pesan status

if ($id) { // Cek apakah ID ditemukan di URL
    $stmt = $db->prepare("DELETE FROM films WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT); // Ikat ID ke placeholder
    $stmt->execute();

    if ($stmt->rowCount() > 0) { // Cek apakah ada baris yang berhasil dihapus
        $message = "Film dengan ID $id berhasil dihapus."; // Pesan sukses
        // Catatan: Sebaiknya hapus juga file poster dan trailer terkait di sini!
    } else {
        $message = "Film dengan ID $id tidak ditemukan."; // Pesan gagal (ID tidak ada)
    }
} else {
    $message = "Parameter ID tidak ditemukan."; // Pesan jika URL tidak menyertakan ID
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hapus Film</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            padding: 40px;
            text-align: center;
        }

        p {
            font-size: 18px;
            margin: 20px 0;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6200ee;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        a:hover {
            background-color: #7c4dff;
        }
    </style>
</head>
<body>
    <p><?php echo htmlspecialchars($message); ?></p> <a href="dashboard_admin.php">Kembali ke daftar film</a> </body>
</html>