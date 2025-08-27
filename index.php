<?php
session_start();
require_once "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id);

    if ($stmt->fetch()) {
        $_SESSION['user_id'] = $id;
    } else {
        $stmt = $mysqli->prepare("INSERT INTO users (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Mini Drive</title>
    <style>
        body {
            margin:0; padding:0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .card {
            background:white;
            padding:40px;
            border-radius:12px;
            box-shadow:0 4px 10px rgba(0,0,0,0.2);
            width:350px;
            text-align:center;
        }
        .card h1 {
            margin-bottom:20px;
            color:#007bff;
        }
        .card p {
            color:#555;
            font-size:14px;
            margin-bottom:20px;
        }
        .input-box {
            width:100%;
            padding:10px;
            margin:10px 0;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:14px;
        }
        .btn {
            background:#007bff;
            color:white;
            border:none;
            padding:10px 20px;
            border-radius:6px;
            font-size:16px;
            cursor:pointer;
            width:100%;
            margin-top:10px;
        }
        .btn:hover {
            background:#0056b3;
        }
        .footer {
            margin-top:20px;
            font-size:12px;
            color:#888;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>📂 Mini Drive</h1>
        <p>Masuk dengan email Anda untuk mengakses file</p>
        <form method="post">
            <input class="input-box" type="email" name="email" placeholder="Masukkan Email" required>
            <button class="btn" type="submit">Masuk</button>
        </form>
        <div class="footer">
            &copy; <?php echo date("Y"); ?> Mini Drive
        </div>
    </div>
</body>
</html>
