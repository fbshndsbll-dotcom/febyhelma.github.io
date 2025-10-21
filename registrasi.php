<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"> 
    <title>Registrasi</title> 
</head>
<body>
    <div class="register-box"> <!-- wadah utama untuk form registrasi -->
        <h2>Buat Akun Baru</h2>

        <form method="POST" action="registrasi_action.php"> 
            <input type="text" name="username" placeholder="Masukan username" required> <!-- input username -->
            <input type="password" name="password" placeholder="Masukan password" required> <!-- input password -->
            <button type="submit">Register</button>
        </form>

        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p> <!-- link menuju halaman login -->
    </div>

    <style>
        body { 
            font-family: "Segoe UI", Arial, sans-serif; 
            background:#1e1e2f; 
            margin:0; 
            display:flex; 
            justify-content:center; 
            align-items:center; 
            height:100vh;
            color:#f1f1f1;
        }
        .register-box {
            background:#2c2c3e;
            padding:30px 25px;
            border-radius:12px;
            box-shadow:0 6px 16px rgba(0,0,0,0.5);
            width:360px;
            text-align:center;
        }
        h2 {
            margin-bottom:20px;
            color:#00ff88;
        }
        .msg { 
            margin:10px 0; 
            padding:10px; 
            border-radius:6px; 
            font-size:14px;
        }
        .error { 
            color:#ff6b6b; 
            background:#3d1f1f; 
            border:1px solid #ff6b6b; 
        }
        .success { 
            color:#4cd137; 
            background:#1e3d1f; 
            border:1px solid #4cd137; 
        }
        input[type="text"], input[type="password"] {
            width:100%;
            padding:10px;
            margin:8px 0;
            border:1px solid #555;
            border-radius:6px;
            font-size:14px;
            background:#1b1b2b;
            color:#fff;
        }
        input::placeholder {
            color:#aaa;
        }
        button {
            margin-top:10px;
            width:100%;
            background:#00ff88;
            border:none;
            color:#111;
            padding:10px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            font-size:15px;
            transition:0.3s;
        }
        button:hover { background:#00cc6a; }
        p {
            margin-top:15px;
            font-size:14px;
            color:#ccc;
        }
        a {
            color:#00ff88;
            text-decoration:none;
            font-weight:600;
        }
        a:hover { text-decoration:underline; }
    </style>
</body>
</html>
