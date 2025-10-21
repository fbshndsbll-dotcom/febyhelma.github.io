<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
        .login-box {
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
        input::placeholder { color:#aaa; }
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
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
         <?php if (isset($_GET['error'])): ?>
        <div class="msg error"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="action_login.php">
            <input type="text" name="username" placeholder="Masukan username" required>
            <input type="password" name="password" placeholder="Masukan password" required>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="registrasi.php">Daftar di sini</a></p>
    </div>
</body>
</html>
